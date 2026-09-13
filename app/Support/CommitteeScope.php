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

        // Never transferred yet — still with Main Committee by definition,
        // not "held by no one".
        return $latestTransfer?->toCommittee ?? PurchaseCommittee::where('type', 'main')->first();
    }

    /** Same idea as userCanActOnCase(), but keyed off a Procurement Plan id (Contract Award). */
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

    /**
     * True if the user may act on this case: procurement_officer/admin
     * always can (unchanged, system-wide access); anyone else only if
     * they're on the committee the case is currently transferred to.
     */
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

    /** Same idea as userCanActOnCase(), but keyed off a Purchase Requisition — used for the PR list/show, since a sub-committee member should only ever see the PR their committee currently holds, not every PR in the system. */
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

    /**
     * Whether a Purchase Requisition should be visible to this user.
     * Admin/Procurement Officer always can. A user who belongs to NO
     * committee is unaffected (unrestricted, as before — normal
     * requester/reviewer/budget_checker/approver workflow is untouched).
     * A user who IS on at least one committee is a committee member for
     * this purpose, and only sees PRs currently transferred to one of
     * their own committees.
     */
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

    /**
     * Whether this user bypasses committee scoping entirely.
     * Admin always bypasses. Procurement Officer bypasses ONLY if they are
     * not a member of any committee — a Procurement Officer who also sits
     * on a (Sub-)Committee is scoped to that committee's transferred
     * work/PRs just like any other committee member, everywhere this
     * class is consulted (dashboard, PR visibility, case/plan actions).
     */
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

   /**
     * Ids of the SUB-Committees the user is currently on. Only sitting on
     * a Sub-Committee scopes/restricts a user's view — the Main/Central
     * committee is the apex procurement authority and its members must
     * keep the full, unrestricted view, so main-committee membership is
     * deliberately excluded here. A user who is on a main committee only
     * (or on no committee at all) gets an empty array back, which every
     * caller in this class treats as "unrestricted".
     */
    public static function committeeIdsForUser(User $user): array
    {
        return CommitteeMember::where('user_id', $user->id)
            ->pluck('committee_id')
            ->all();
    }
}
