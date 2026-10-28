<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\CommitteeMemberController;
use App\Models\CommitteeMember;
use App\Models\ProcurementCommitteeMember;
use App\Models\PurchaseCommittee;
use App\Models\User;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Committee Roster" page: pick a committee, see ONLY that committee's members.
 *
 * Committees live in purchase_committees (type main / sub); their members in
 * committee_members. procurement_committee_members is just the people list
 * (name / e-mail / role) that committee_members point to.
 */
class CommitteeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $committees = $this->visibleCommittees($user);
        $selected = $committees->firstWhere('id', $request->integer('committee')) ?? $committees->first();

        $members = collect();
        if ($selected) {
            $members = CommitteeMember::where('committee_id', $selected->id)
                ->with(['user', 'procurementCommitteeMember'])
                ->get()
                ->map(function (CommitteeMember $cm) {
                    $roster = $cm->procurementCommitteeMember;

                    return [
                        'roster' => $roster, // null = a login-only member (no roster entry)
                        'name' => $cm->member_name ?? 'Member',
                        'designation' => $roster?->designation ?: $cm->user?->designation,
                        'role' => $cm->designation_in_committee ?: $roster?->roleLabel(),
                        'email' => $roster?->email ?: $cm->user?->email,
                        'active' => $roster ? (bool) $roster->active : true,
                        'sort' => sprintf('%03d-%08d', $roster?->sort_order ?? 99, $cm->id),
                    ];
                })
                ->sortBy('sort')
                ->values();
        }

        // Roster people who sit in no committee at all (so they get no notices).
        // Only shown to users who are not tied to specific committees (Admin / Center Procurement).
        $orphans = $this->isUnrestricted($user)
            ? ProcurementCommitteeMember::whereNotIn(
                'id',
                CommitteeMember::whereNotNull('procurement_committee_member_id')->select('procurement_committee_member_id')
            )->orderBy('sort_order')->get()
            : collect();

        $isMain = $selected && $selected->type === 'main';
        $range = $isMain
            ? [CommitteeMemberController::CENTRAL_MIN, CommitteeMemberController::CENTRAL_MAX]
            : [CommitteeMemberController::SUB_MIN, CommitteeMemberController::SUB_MAX];

        return view('settings.committee', [
            'committees' => $committees,
            'selected' => $selected,
            'members' => $members,
            'orphans' => $orphans,
            'isMain' => $isMain,
            'minMembers' => $range[0],
            'maxMembers' => $range[1],
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    /** Add a person to the MAIN committee (roster entry + committee membership in one go). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'committee_id' => 'required|exists:purchase_committees,id',
            'name' => 'required|string|max:120',
            'designation' => 'required|string|max:120',
            'email' => 'nullable|email|max:150',
            'role' => 'required|in:convener,member,member_secretary',
        ]);

        $committee = PurchaseCommittee::withoutGlobalScopes()->findOrFail($data['committee_id']);
        abort_unless($this->visibleCommittees(Auth::user())->contains('id', $committee->id), 403);

        $back = redirect()->route('settings.committee.index', ['committee' => $committee->id]);

        if ($committee->type !== 'main') {
            return $back->withErrors(['name' => 'Members of a Sub-Committee are added under Admin → Committees.'])->withInput();
        }

        $count = CommitteeMember::where('committee_id', $committee->id)->count();
        if ($count >= CommitteeMemberController::CENTRAL_MAX) {
            return $back->withErrors(['name' => 'Per ESDO Procurement Policy §9, the central procurement committee can have at most '
                . CommitteeMemberController::CENTRAL_MAX . " members. \"{$committee->name}\" already has {$count}."])->withInput();
        }

        DB::transaction(function () use ($data, $committee) {
            $member = ProcurementCommitteeMember::create([
                'name' => $data['name'],
                'designation' => $data['designation'],
                'email' => $data['email'] ?? null,
                'role' => $data['role'],
                'sort_order' => (int) ProcurementCommitteeMember::max('sort_order') + 1,
                'active' => true,
            ]);

            // Same rule as Admin → Committees: a person who already has a login (same e-mail)
            // is linked to it, so committee-scoped access keeps working for that login.
            $userId = ! empty($data['email'])
                ? User::whereRaw('LOWER(email) = ?', [strtolower(trim($data['email']))])->value('id')
                : null;

            CommitteeMember::create([
                'committee_id' => $committee->id,
                'user_id' => $userId,
                'procurement_committee_member_id' => $member->id,
                'designation_in_committee' => ProcurementCommitteeMember::ROLE_LABELS[$data['role']] ?? $data['role'],
            ]);
        });

        return $back->with('ok', 'Committee member added to ' . $committee->name . '.');
    }

    public function toggle(ProcurementCommitteeMember $member)
    {
        $this->authorizeMember($member);

        $member->update(['active' => ! $member->active]);

        return back()->with('ok', $member->active ? 'Member reactivated.' : 'Member deactivated.');
    }

    public function updateEmail(Request $request, ProcurementCommitteeMember $member)
    {
        $this->authorizeMember($member);

        $data = $request->validate(['email' => 'nullable|email|max:150']);
        $member->update($data);

        return back()->with('ok', 'Email updated for ' . $member->name . '.');
    }

    /** Admin / Center Procurement (not tied to specific committees) see everything. */
    private function isUnrestricted(User $user): bool
    {
        return $user->isAdmin() || empty(CommitteeScope::committeeIdsForUser($user));
    }

    /** Committees this user may look at: Main first, then Sub-Committees; a committee member sees only their own. */
    private function visibleCommittees(User $user): Collection
    {
        $query = PurchaseCommittee::withoutGlobalScopes()
            ->with('project')
            ->orderByRaw("CASE WHEN type = 'main' THEN 0 ELSE 1 END")
            ->orderBy('name');

        if (! $this->isUnrestricted($user)) {
            $query->whereIn('id', CommitteeScope::committeeIdsForUser($user));
        }

        return $query->get();
    }

    /** A committee member may only change people who are in one of their own committees. */
    private function authorizeMember(ProcurementCommitteeMember $member): void
    {
        $user = Auth::user();
        if ($this->isUnrestricted($user)) {
            return;
        }

        abort_unless(
            CommitteeMember::where('procurement_committee_member_id', $member->id)
                ->whereIn('committee_id', CommitteeScope::committeeIdsForUser($user))
                ->exists(),
            403,
            'This person is not in a committee you can manage.'
        );
    }
}