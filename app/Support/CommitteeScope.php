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

        $current = self::currentCommitteeForPlanId($procurementPlanId);

        // Center/Main Procurement: only while the plan has NOT been sent to a sub-committee.
        if (self::isCenterProcurement($user)) {
            return self::isHeldByMain($current);
        }

        return self::userIsMemberOf($user, $current);
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

        $current = self::currentCommitteeForCase($case);

        if (self::isCenterProcurement($user)) {
            return self::isHeldByMain($current);
        }

        return self::userIsMemberOf($user, $current);
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

        if (self::isCenterProcurement($user)) {
            return self::isHeldByMain(self::currentCommitteeForPurchaseRequisition($pr));
        }

        if (empty(self::committeeIdsForUser($user))) {
            return true;
        }

        return self::userIsMemberOf($user, self::currentCommitteeForPurchaseRequisition($pr));
    }


    /**
     * Only Admin sees/acts on everything regardless of where a PR currently sits.
     * (Procurement Officers are NOT unrestricted any more: Center/Main Procurement
     * loses a PR once it is transferred to a sub-committee.)
     * Focal Person / Executive Director are untouched: they have no committee rows,
     * so they keep the "see everything" behaviour in prVisibleToUser()/visiblePlanIdsFor().
     */
    public static function hasUnrestrictedAccess(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Center / Main Procurement = a Procurement Officer who is not a member of any
     * Sub-Committee. Works across all projects, but only on PRs still held by Main.
     */
    public static function isCenterProcurement(User $user): bool
    {
        if (! $user->isProcurementOfficer()) {
            return false;
        }

        $subIds = PurchaseCommittee::where('type', 'sub')->select('id');

        return ! CommitteeMember::where('user_id', $user->id)
            ->whereIn('committee_id', $subIds)
            ->exists();
    }

    /** A plan/PR/case is with Main unless its latest transfer points to a sub-committee. */
    public static function isHeldByMain(?PurchaseCommittee $committee): bool
    {
        return ! $committee || $committee->type === 'main';
    }

    /**
     * true = this user's visibility depends on who currently holds the PR/plan
     * (used by the dashboard). Admin, Focal Person, ED, requesters etc. are not.
     */
    public static function isHoldingScoped(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->isProcurementOfficer() || ! empty(self::committeeIdsForUser($user));
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

        if (self::hasUnrestrictedAccess($user)) {
            return null;
        }

        // Center/Main Procurement: every plan EXCEPT those whose latest transfer is to a sub-committee.
        if (self::isCenterProcurement($user)) {
            $subHeld = \App\Models\SubCommitteeTransfer::with('toCommittee')
                ->orderByDesc('transfer_date')->orderByDesc('id')->get()
                ->unique('procurement_plan_id')
                ->filter(fn ($t) => $t->toCommittee && $t->toCommittee->type === 'sub')
                ->pluck('procurement_plan_id')->all();

            return \App\Models\ProcurementPlan::whereNotIn('id', $subHeld)->pluck('id')->all();
        }

        if (empty($committeeIds)) {
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
