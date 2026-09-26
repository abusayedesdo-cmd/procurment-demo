<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMember;
use App\Models\ContractAward;
use App\Models\ProcurementAnnualPlan;
use App\Models\ProcurementPlan;
use App\Models\PurchaseRequisition;
use App\Models\SubCommitteeTransfer;
use App\Support\CommitteeScope;
use Illuminate\Support\Facades\Auth;

/**
 * Minimal session-auth shell. The real UI lives in the separate Next.js
 * frontend (consuming the /api/* routes via Sanctum) — this view is just
 * a lightweight status/landing page for anyone hitting the Laravel app
 * directly, and confirms the logged-in session is valid.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->roleName() ?? null;

        $canReview = in_array($role, [\App\Models\User::REVIEWER, \App\Models\User::ADMIN]);
        $canCheckBudget = in_array($role, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN]);
        $canApprove = in_array($role, [\App\Models\User::APPROVER, \App\Models\User::ADMIN]);
        $canFocalReview = in_array($role, [\App\Models\User::FOCAL_PERSON, \App\Models\User::ADMIN]);
        $canEdApprove = in_array($role, [\App\Models\User::EXECUTIVE_DIRECTOR, \App\Models\User::ADMIN]);

       
        $committeeIds = CommitteeScope::committeeIdsForUser($user);
        $isCommitteeOnly = ! empty($committeeIds) && ! CommitteeScope::hasUnrestrictedAccess($user);

        // Plans currently held by the user's committee — SAME rule as the
        // Procurement Plans list page (CommitteeScope::visiblePlanIdsFor):
        // a plan that was NEVER transferred still counts as being with the
        // Main Committee, not as invisible to everyone. Using the
        // transfer-only rule here (as before) undercounted this card for
        // Main Committee members, since it silently dropped every plan
        // that hadn't been explicitly transferred.
        $visiblePlanIds = CommitteeScope::visiblePlanIdsFor($user);
        $scopedPlanIds = $visiblePlanIds === null ? null : collect($visiblePlanIds);

        // PRs: use the same "current holder" rule as everywhere else
        // (CommitteeScope::prVisibleToUser) instead of a separate,
        // transfer-only scope — that rule already treats a PR that was
        // NEVER transferred as still being with the Main Committee, which
        // a purely SubCommitteeTransfer-based scope would silently drop.
        $scopePrs = function ($query) use ($user, $isCommitteeOnly) {
            $results = $query->get();
            return $isCommitteeOnly
                ? $results->filter(fn ($pr) => CommitteeScope::prVisibleToUser($user, $pr))->values()
                : $results;
        };

        $awaitingReview = $canReview
            ? $scopePrs(PurchaseRequisition::where('status', 'draft')->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number']))
            : collect();

        $awaitingBudgetCheck = $canCheckBudget
            ? $scopePrs(PurchaseRequisition::where('status', 'reviewed')->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number']))
            : collect();

        // 'checked' status is exclusive to the PR window and now belongs to
        // Focal Person (see below). The Approver role only still acts on
        // the BOQ/TOR/Design & Drawing windows, at their 'reviewed' stage.
        $awaitingApproval = $canApprove
            ? $scopePrs(PurchaseRequisition::where('status', 'reviewed')->where('window_type', '!=', 'PR')->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number']))
            : collect();

        $threshold = \App\Http\Controllers\Api\PrApprovalController::HIGH_VALUE_THRESHOLD;

        $awaitingFocalReview = $canFocalReview
            ? $scopePrs(PurchaseRequisition::where('status', 'checked')
                ->where(function ($q) use ($threshold) {
                    $q->where('routed_to', 'focal_person')
                        ->orWhere(function ($q2) use ($threshold) {
                            $q2->whereNull('routed_to')->where('total_estimated_amount', '<', $threshold);
                        });
                })
                ->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number']))
            : collect();

        $awaitingEdApproval = $canEdApprove
            ? $scopePrs(PurchaseRequisition::where(function ($q) use ($threshold) {
                $q->where('status', 'focal_reviewed')
                    ->orWhere(function ($q2) use ($threshold) {
                        $q2->where('status', 'checked')
                            ->where(function ($q3) use ($threshold) {
                                $q3->where('routed_to', 'executive_director')
                                    ->orWhere(function ($q4) use ($threshold) {
                                        $q4->whereNull('routed_to')->where('total_estimated_amount', '>=', $threshold);
                                    });
                            });
                    });
            })
                ->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number']))
            : collect();

        return view('dashboard', [
            'user' => $user,
            'draftPrs' => $scopePrs(PurchaseRequisition::where('status', 'draft'))->count(),
            'pendingPrs' => $scopePrs(PurchaseRequisition::whereIn('status', ['reviewed', 'checked', 'focal_reviewed']))->count(),
            'approvedPrs' => $scopePrs(PurchaseRequisition::where('status', 'approved'))->count(),
            'activePlans' => $scopedPlanIds !== null
                ? ProcurementPlan::whereIn('id', $scopedPlanIds)->whereIn('status', ['planned', 'ongoing'])->count()
                : ProcurementPlan::whereIn('status', ['planned', 'ongoing'])->count(),
            'contractsAwarded' => $scopedPlanIds !== null
                ? ContractAward::whereIn('procurement_plan_id', $scopedPlanIds)->count()
                : ContractAward::count(),
            'annualPlansCount' => ProcurementAnnualPlan::count(),
            'myCommitteeWorkCount' => (function () use ($user) {
                // Same rule as CommitteeWorkController::index(): only the
                // latest transfer per plan decides who currently holds it.
                $myCommitteeIds = CommitteeMember::where('user_id', $user->id)->pluck('committee_id');

                return SubCommitteeTransfer::orderByDesc('transfer_date')
                    ->orderByDesc('id')
                    ->get()
                    ->unique('procurement_plan_id')
                    ->filter(fn ($t) => $myCommitteeIds->contains($t->to_committee_id))
                    ->count();
            })(),
            'awaitingReview' => $awaitingReview,
            'awaitingBudgetCheck' => $awaitingBudgetCheck,
            'awaitingApproval' => $awaitingApproval,
            'awaitingFocalReview' => $awaitingFocalReview,
            'awaitingEdApproval' => $awaitingEdApproval,
        ]);
    }
}
