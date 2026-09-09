<?php

namespace App\Support;

use App\Models\CommitteeMember;
use App\Models\ProcurementCase;
use App\Models\PurchaseCommittee;
use App\Models\User;

/**
 * Sub-committee scoped access (design item 8).
 *
 * When a case is transferred to a Sub-Committee (SubCommitteeTransferController),
 * that committee's members should be able to work the case (RFQ, Tender
 * Opening, Quotation, Comparative Statement, Contract Award) WITHOUT being
 * given the procurement_officer role generally — access is scoped to that
 * one case only, for as long as it stays with their committee.
 *
 * Model chain used to find "who currently holds this case":
 *   ProcurementCase -> purchase_requisition_id -> PurchaseRequisition
 *   ProcurementPlan (same purchase_requisition_id, via pr_id)
 *   -> latest SubCommitteeTransfer for that plan -> to_committee_id
 */
class CommitteeScope
{
    /**
     * The committee a case is currently with, or null if it has never been
     * transferred to a sub-committee (still with the main/central committee,
     * or has no linked Procurement Plan yet).
     */
    public static function currentCommitteeForCase(?ProcurementCase $case): ?PurchaseCommittee
    {
        if (! $case || ! $case->purchase_requisition_id) {
            return null;
        }

        $plan = \App\Models\ProcurementPlan::where('pr_id', $case->purchase_requisition_id)->first();
        if (! $plan) {
            return null;
        }

        $latestTransfer = $plan->subCommitteeTransfers()
            ->latest('transfer_date')
            ->latest('id')
            ->with('toCommittee')
            ->first();

        return $latestTransfer?->toCommittee;
    }

    /**
     * Same as currentCommitteeForCase(), but starting from a Procurement
     * Plan directly — Contract Award hangs off procurement_plan_id, not a
     * case, so it doesn't need the extra case -> PR -> plan hop.
     */
    public static function currentCommitteeForPlanId(?int $procurementPlanId): ?PurchaseCommittee
    {
        if (! $procurementPlanId) {
            return null;
        }

        $latestTransfer = \App\Models\SubCommitteeTransfer::where('procurement_plan_id', $procurementPlanId)
            ->latest('transfer_date')
            ->latest('id')
            ->with('toCommittee')
            ->first();

        return $latestTransfer?->toCommittee;
    }

    /** Same idea as userCanActOnCase(), but keyed off a Procurement Plan id (Contract Award). */
    public static function userCanActOnPlan(User $user, ?int $procurementPlanId): bool
    {
        if ($user->canManageProcurement()) {
            return true;
        }

        return self::userIsMemberOf($user, self::currentCommitteeForPlanId($procurementPlanId));
    }

    public static function userIsMemberOf(User $user, ?PurchaseCommittee $committee): bool
    {
        if (! $committee) {
            return false;
        }

        return CommitteeMember::where('committee_id', $committee->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * True if the user may act on this case: procurement_officer/admin
     * always can (unchanged, system-wide access); anyone else only if
     * they're on the committee the case is currently transferred to.
     */
    public static function userCanActOnCase(User $user, ?ProcurementCase $case): bool
    {
        if ($user->canManageProcurement()) {
            return true;
        }

        if (! $case) {
            return false;
        }

        return self::userIsMemberOf($user, self::currentCommitteeForCase($case));
    }

    /** Ids of every committee the user is currently on. */
    public static function committeeIdsForUser(User $user): array
    {
        return CommitteeMember::where('user_id', $user->id)->pluck('committee_id')->all();
    }
}
