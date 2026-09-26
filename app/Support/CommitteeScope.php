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
        // No Plan yet at all — still with Main Committee by definition.
        if (! $plan) {
            return PurchaseCommittee::where('type', 'main')->first();
        }

        return self::currentCommitteeForPlanId($plan->id);
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


        return $latestTransfer?->toCommittee ?? PurchaseCommittee::where('type', 'main')->first();
    }


    public static function userCanActOnPlan(User $user, ?int $procurementPlanId): bool
    {
        if (self::hasUnrestrictedAccess($user)) {
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


    public static function userCanActOnCase(User $user, ?ProcurementCase $case): bool
    {
        if (self::hasUnrestrictedAccess($user)) {
            return true;
        }

        if (! $case) {
            return false;
        }

        return self::userIsMemberOf($user, self::currentCommitteeForCase($case));
    }

   
    public static function currentCommitteeForPurchaseRequisition($pr): ?PurchaseCommittee
    {
        if (! $pr) {
            return null;
        }

        $plan = \App\Models\ProcurementPlan::where('pr_id', $pr->id)->first();
        // No Plan yet at all — still with Main Committee by definition.
        if (! $plan) {
            return PurchaseCommittee::where('type', 'main')->first();
        }

        return self::currentCommitteeForPlanId($plan->id);
    }


    public static function prVisibleToUser(User $user, $pr): bool
    {
        if (self::hasUnrestrictedAccess($user)) {
            return true;
        }

        if (empty(self::committeeIdsForUser($user))) {
            return true;
        }

        return self::userIsMemberOf($user, self::currentCommitteeForPurchaseRequisition($pr));
    }


    public static function hasUnrestrictedAccess(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isProcurementOfficer()) {
            return empty(self::committeeIdsForUser($user));
        }

        return false;
    }


    public static function committeeIdsForUser(User $user): array
    {
        return CommitteeMember::where('user_id', $user->id)
            ->pluck('committee_id')
            ->all();
    }

    /**
     * null = সব প্ল্যান দেখতে পারবে (unrestricted বা কোনো কমিটির সদস্য নয়)।
     * নইলে: যেসব প্ল্যান এখন ইউজারের কমিটির হাতে আছে তাদের ID।
     */
    public static function visiblePlanIdsFor(User $user): ?array
    {
        $committeeIds = self::committeeIdsForUser($user);

        if (self::hasUnrestrictedAccess($user) || empty($committeeIds)) {
            return null;
        }

        $latest = \App\Models\SubCommitteeTransfer::orderByDesc('transfer_date')
            ->orderByDesc('id')->get()->unique('procurement_plan_id');

        $ids = $latest->filter(fn ($t) => in_array($t->to_committee_id, $committeeIds))
            ->pluck('procurement_plan_id')->all();

        // যে প্ল্যান কখনো transfer হয়নি সেটা Main Committee-র হাতে
        $mainId = PurchaseCommittee::where('type', 'main')->value('id');
        if ($mainId && in_array($mainId, $committeeIds)) {
            $ids = array_merge($ids, \App\Models\ProcurementPlan::whereNotIn(
                'id', $latest->pluck('procurement_plan_id')
            )->pluck('id')->all());
        }

        return $ids;
    }
}
