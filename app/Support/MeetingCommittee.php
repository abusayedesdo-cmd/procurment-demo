<?php

namespace App\Support;

use App\Models\CommitteeMember;
use App\Models\Meeting;
use App\Models\ProcurementCase;
use App\Models\ProcurementCommitteeMember;
use App\Models\ProcurementPlan;
use App\Models\PurchaseCommittee;
use App\Models\SubCommitteeTransfer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One place that answers: "Which committee is this meeting for, and who is in it?"
 *
 *  - Main / Central committee  -> purchase_committees.type = 'main'
 *  - Sub-Committees            -> purchase_committees.type = 'sub' (one per project)
 *  - Members of every committee come from committee_members.
 *
 * Safety net: if the Main committee has no committee_members rows yet, the old
 * Central Procurement roster (procurement_committee_members) is used, so notices
 * never silently go to nobody. The notice form shows when this happens.
 */
class MeetingCommittee
{
    public static function main(): ?PurchaseCommittee
    {
        return PurchaseCommittee::withoutGlobalScopes()->where('type', 'main')->first();
    }

    /** The committee that currently holds a case (Main unless transferred to a Sub-Committee). */
    public static function forCase(?ProcurementCase $case): ?PurchaseCommittee
    {
        $committee = $case ? CommitteeScope::currentCommitteeForCase($case) : null;

        return $committee ?? self::main();
    }

    /**
     * Committee that is running the procurement an RFQ belongs to
     * (Main unless the case was transferred to a Sub-Committee).
     */
    public static function forRfq(\App\Models\Rfq $rfq): ?PurchaseCommittee
    {
        return self::forCase($rfq->procurementCase);
    }

    /**
     * Name + address printed on a document for the committee that is doing the procurement.
     * A Sub-Committee with no address of its own uses the Main committee's address; if that is
     * empty too, $defaultAddress (the old hard-coded text) is used so a letter is never blank.
     * $office (Main committee only) = 'Dhaka' / 'Thakurgaon' chosen on the notice or RFQ.
     *
     * @return array{name:string,address:string}
     */
    public static function letterhead(?PurchaseCommittee $committee, string $defaultAddress = 'Dhaka', ?string $office = null): array
    {
        // The case-holder lookup is project-scoped; re-read without scopes so a user who is not
        // in that project still gets the right committee on the paper.
        if ($committee) {
            $committee = PurchaseCommittee::withoutGlobalScopes()->find($committee->id) ?? $committee;
        }
        $main = self::main();

        // Main committee: papers always say "Central Procurement Committee" (the saved name may just be a
        // place, e.g. "Thakurgao"). A Sub-Committee prints its own name.
        $isMain = ! $committee || $committee->type === 'main';
        $name = $isMain
            ? 'Central Procurement Committee'
            : (trim((string) $committee->name) ?: 'Central Procurement Committee');
        $address = trim((string) ($committee?->address ?? ''))
            ?: trim((string) ($main?->address ?? ''))
            ?: $defaultAddress;

        $location = $address;

        // Main committee only: the office picked on the notice / RFQ (Dhaka or Thakurgaon) wins.
        if ($isMain && $office) {
            $offices = (array) config('esdo.offices', []);
            foreach ($offices as $label => $officeAddress) {
                if (strcasecmp((string) $label, trim($office)) === 0 && trim((string) $officeAddress) !== '') {
                    $address = trim((string) $officeAddress);
                    $location = (string) $label;
                    break;
                }
            }
        }

        return ['name' => $name, 'address' => $address, 'location' => $location];
    }

    /** The office (Dhaka / Thakurgaon) picked on the case's 1st Meeting Notice; $fallback if there is none. */
    public static function caseOffice(?\App\Models\ProcurementCase $case, ?string $fallback = null): ?string
    {
        if ($case) {
            $office = Meeting::withoutGlobalScopes()
                ->where('procurement_case_id', $case->id)
                ->where('meeting_type', 'first')
                ->value('office');

            if ($office) {
                return $office;
            }
        }

        return $fallback;
    }

    /** Saved committee of the meeting; older meetings fall back to the case holder. */
    public static function forMeeting(Meeting $meeting): ?PurchaseCommittee
    {
        if ($meeting->committee_id) {
            $saved = PurchaseCommittee::withoutGlobalScopes()->find($meeting->committee_id);
            if ($saved) {
                return $saved;
            }
        }

        return self::forCase($meeting->procurementCase);
    }

    /**
     * Same rule as CommitteeScope::currentCommitteeForCase(), but for many cases
     * with 3 queries in total (used by the notice form's case dropdown).
     *
     * @return array<int, int|null>  case id => committee id
     */
    public static function holderIdsForCases(Collection $cases): array
    {
        $mainId = self::main()?->id;

        $planByPr = ProcurementPlan::whereIn('pr_id', $cases->pluck('purchase_requisition_id')->filter()->unique())
            ->pluck('id', 'pr_id');

        $holderByPlan = SubCommitteeTransfer::whereIn('procurement_plan_id', $planByPr->values())
            ->orderByDesc('transfer_date')->orderByDesc('id')->get()
            ->unique('procurement_plan_id')
            ->pluck('to_committee_id', 'procurement_plan_id');

        $out = [];
        foreach ($cases as $case) {
            $planId = $planByPr[$case->purchase_requisition_id] ?? null;
            $out[$case->id] = ($planId ? ($holderByPlan[$planId] ?? null) : null) ?? $mainId;
        }

        return $out;
    }

    /**
     * Members of a committee, ready for e-mailing / attendance.
     *
     * @return array{rows: Collection<int, array{name:string,title:?string,email:?string,roster_id:?int}>, fallback: bool}
     */
    public static function members(?PurchaseCommittee $committee): array
    {
        $rows = collect();

        if ($committee) {
            $rows = CommitteeMember::where('committee_id', $committee->id)
                ->with(['user', 'procurementCommitteeMember'])
                ->orderBy('id')
                ->get()
                // A roster member who was deactivated (left the committee) gets no mail.
                ->reject(fn ($cm) => $cm->procurementCommitteeMember && ! $cm->procurementCommitteeMember->active)
                ->map(function ($cm) use ($committee) {
                    $roster = $cm->procurementCommitteeMember;

                    $title = $cm->designation_in_committee
                        ?: ($roster ? $roster->roleLabel() . ', ' . $committee->name : null)
                        ?: $cm->user?->designation
                        ?: 'Member, ' . $committee->name;

                    return self::row(
                        $cm->member_name ?: ($cm->user?->email ?: 'Member'),
                        $title,
                        $roster?->email ?: $cm->user?->email,
                        $roster?->id,
                    );
                });
        }

        $fallback = false;
        if ($rows->isEmpty() && (! $committee || $committee->type === 'main')) {
            $fallback = true;
            $label = $committee?->name ?? 'Central Procurement Committee';
            $rows = ProcurementCommitteeMember::activeRoster()
                ->map(fn ($m) => self::row($m->name, $m->roleLabel() . ', ' . $label, $m->email, $m->id));
        }

        return ['rows' => $rows->values(), 'fallback' => $fallback];
    }

    /** Committees this user may send a notice for (Admin: all; Center Procurement: Main; Sub member: own). */
    public static function availableFor(User $user): Collection
    {
        $all = PurchaseCommittee::withoutGlobalScopes()->with('project')
            ->orderByRaw("CASE WHEN type = 'main' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        if ($user->isAdmin()) {
            return $all;
        }

        $mine = CommitteeMember::where('user_id', $user->id)->pluck('committee_id');
        $center = CommitteeScope::isCenterProcurement($user);

        return $all->filter(fn ($c) => $mine->contains($c->id) || ($center && $c->type === 'main'))->values();
    }

    /** Human label for dropdowns: "<name> · Main / Central" or "<name> · Sub-Committee · <project>". */
    public static function label(PurchaseCommittee $committee): string
    {
        if ($committee->type === 'main') {
            return $committee->name . ' · Main / Central';
        }

        $project = $committee->project?->name;

        return $committee->name . ' · Sub-Committee'
            . ($project && ! Str::contains($committee->name, $project) ? ' · ' . $project : '');
    }

    /** Data for the notice form's live "who will receive this notice" box, keyed by committee id. */
    public static function previewFor(Collection $committees): array
    {
        return $committees->mapWithKeys(function ($c) {
            $info = self::members($c);

            return [$c->id => [
                'label' => self::label($c),
                'name' => $c->name,
                'type' => $c->type,
                'fallback' => $info['fallback'],
                'members' => $info['rows']->all(),
            ]];
        })->all();
    }

    private static function row(?string $name, ?string $title, ?string $email, ?int $rosterId): array
    {
        $email = trim((string) $email);

        return [
            'name' => (string) $name,
            'title' => $title ?: null,
            'email' => $email !== '' ? $email : null,
            'roster_id' => $rosterId,
        ];
    }
}