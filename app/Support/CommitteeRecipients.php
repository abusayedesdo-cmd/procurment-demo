<?php

namespace App\Support;

use App\Models\CommitteeMember;
use App\Models\ProcurementCase;
use App\Models\ProcurementPlan;
use App\Models\PurchaseCommittee;
use App\Models\SubCommitteeTransfer;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Single source for "which committee, and who is in it" for meeting notices,
 * attendance and their emails.
 *
 * Everything comes from purchase_committees (type main/sub, sub has project_id)
 * + committee_members. The old Committee Roster table is only used to look up a
 * member's name / e-mail / role through committee_members.procurement_committee_member_id.
 *
 * Queries here deliberately skip the per-project global scope: a notice must go
 * to the whole committee no matter which project the sending user belongs to.
 */
class CommitteeRecipients
{
    public static function mainCommittee(): ?PurchaseCommittee
    {
        return PurchaseCommittee::withoutGlobalScopes()->where('type', 'main')->orderBy('id')->first();
    }

    /** Committee that currently holds the case (Main when never transferred / no plan yet). */
    public static function committeeForCase(?ProcurementCase $case): ?PurchaseCommittee
    {
        return CommitteeScope::currentCommitteeForCase($case) ?? self::mainCommittee();
    }

    /**
     * Same answer as committeeForCase() for many cases at once (no per-case queries).
     *
     * @param  Collection<int, ProcurementCase>  $cases  needs id + purchase_requisition_id
     * @return array<int, int|null>  [case id => committee id]
     */
    public static function committeeIdsForCases(Collection $cases): array
    {
        $result = [];
        if ($cases->isEmpty()) {
            return $result;
        }

        $mainId = self::mainCommittee()?->id;

        $prIds = $cases->pluck('purchase_requisition_id')->filter()->unique()->values()->all();
        $planIdByPr = $prIds
            ? ProcurementPlan::withoutGlobalScopes()->whereIn('pr_id', $prIds)->orderByDesc('id')->pluck('id', 'pr_id')
            : collect();

        $toCommitteeByPlan = $planIdByPr->isEmpty()
            ? collect()
            : SubCommitteeTransfer::whereIn('procurement_plan_id', $planIdByPr->values()->all())
                ->orderByDesc('transfer_date')->orderByDesc('id')->get()
                ->unique('procurement_plan_id')
                ->pluck('to_committee_id', 'procurement_plan_id');

        foreach ($cases as $case) {
            $planId = $planIdByPr->get($case->purchase_requisition_id);
            $result[$case->id] = ($planId ? $toCommitteeByPlan->get($planId) : null) ?: $mainId;
        }

        return $result;
    }

    /**
     * Committees this user may send a stand-alone notice for.
     * Admin / Center users: all. A user who sits on sub-committees: only their own.
     */
    public static function selectableCommittees(User $user): Collection
    {
        $query = PurchaseCommittee::withoutGlobalScopes()
            ->with('project')
            ->orderByRaw("type = 'main' desc")
            ->orderBy('name');

        if (! $user->isAdmin()) {
            $own = CommitteeScope::committeeIdsForUser($user);
            if ($own) {
                $query->whereIn('id', $own);
            }
        }

        return $query->get();
    }

    /** Plain array describing a committee + its members, for the notice form preview. */
    public static function describe(PurchaseCommittee $committee): array
    {
        $isSub = $committee->type === 'sub';

        return [
            'id' => $committee->id,
            'name' => $committee->name,
            'type' => $committee->type,
            'label' => self::label($committee),
            'members' => self::membersOf($committee)->values()->all(),
            'cc' => $isSub ? self::mainConveners()->values()->all() : [],
        ];
    }

    /** "Main / Central Committee" or "Sub-Committee — <project>". */
    public static function label(?PurchaseCommittee $committee): string
    {
        if (! $committee) {
            return '';
        }
        if ($committee->type !== 'sub') {
            return 'Main / Central Committee';
        }
        $project = $committee->project?->name;

        return 'Sub-Committee' . ($project ? ' — ' . $project : '');
    }

    /**
     * Members of a committee: name, committee role/designation, e-mail (roster
     * e-mail first, then the login account's), roster id (for the attendance sheet).
     *
     * @return Collection<int, array{name:string,designation:string,email:?string,roster_id:?int,is_convener:bool}>
     */
    public static function membersOf(?PurchaseCommittee $committee): Collection
    {
        if (! $committee) {
            return collect();
        }

        return CommitteeMember::where('committee_id', $committee->id)
            ->with(['user', 'procurementCommitteeMember'])
            ->get()
            ->map(function (CommitteeMember $cm) {
                $roster = $cm->procurementCommitteeMember;
                $designation = trim((string) ($cm->designation_in_committee
                    ?: ($roster ? ($roster->roleLabel() ?: $roster->designation) : '')));
                $email = trim((string) ($roster?->email ?: $cm->user?->email));

                return [
                    'name' => (string) ($cm->member_name ?? ($email !== '' ? $email : 'Member')),
                    'designation' => $designation,
                    'email' => $email !== '' ? $email : null,
                    'roster_id' => $roster?->id,
                    'is_convener' => ($roster?->role === 'convener') || stripos($designation, 'convener') !== false,
                    '_sort' => sprintf('%03d-%08d', $roster?->sort_order ?? 99, $cm->id),
                ];
            })
            ->sortBy('_sort')
            ->map(function (array $m) {
                unset($m['_sort']);

                return $m;
            })
            ->values();
    }

    /** Convener(s) of the Main committee — optional CC on sub-committee notices. */
    public static function mainConveners(): Collection
    {
        return self::membersOf(self::mainCommittee())->filter(fn ($m) => $m['is_convener'])->values();
    }

    /**
     * Who gets the notice e-mail.
     *
     * @return array{0: array<string, array{name:string,copy:bool}>, 1: array<int,string>, 2: int}
     *         [recipients keyed by lower-case e-mail, names with no e-mail, member count]
     */
    public static function noticeRecipients(?PurchaseCommittee $committee, bool $ccMainConvener = false): array
    {
        $members = self::membersOf($committee);

        $namesByEmail = [];
        $missing = [];
        foreach ($members as $m) {
            if ($m['email']) {
                $namesByEmail[strtolower($m['email'])][] = $m['name'];
            } else {
                $missing[] = $m['name'];
            }
        }

        // Several members sharing one address get ONE mail addressed to all their names.
        $recipients = [];
        foreach ($namesByEmail as $email => $names) {
            $recipients[$email] = ['name' => implode(' / ', array_unique($names)), 'copy' => false];
        }

        if ($committee && $committee->type === 'sub' && $ccMainConvener) {
            foreach (self::mainConveners() as $m) {
                if ($m['email']) {
                    $recipients[strtolower($m['email'])] ??= ['name' => $m['name'], 'copy' => true];
                } else {
                    $missing[] = $m['name'] . ' (Main Convener, CC)';
                }
            }
        }

        return [$recipients, array_values(array_unique($missing)), $members->count()];
    }
}
