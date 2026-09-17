<?php

namespace App\Http\Controllers;

use App\Models\ContractAward;
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

        // Plans currently held by one of the user's own committees — still
        // used for the Active Plans / Contracts Awarded cards below, which
        // are genuinely plan-scoped (a plan/contract only exists once a
        // Plan record does, so "never transferred" isn't a concern there).
        $scopedPlanIds = $isCommitteeOnly
            ? SubCommitteeTransfer::query()
                ->orderByDesc('transfer_date')
                ->orderByDesc('id')
                ->get()
                ->unique('procurement_plan_id')
                ->filter(fn ($t) => in_array($t->to_committee_id, $committeeIds))
                ->pluck('procurement_plan_id')
            : collect();

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
            'activePlans' => $isCommitteeOnly
                ? ProcurementPlan::whereIn('id', $scopedPlanIds)->whereIn('status', ['planned', 'ongoing'])->count()
                : ProcurementPlan::whereIn('status', ['planned', 'ongoing'])->count(),
            'contractsAwarded' => $isCommitteeOnly
                ? ContractAward::whereIn('procurement_plan_id', $scopedPlanIds)->count()
                : ContractAward::count(),
            'awaitingReview' => $awaitingReview,
            'awaitingBudgetCheck' => $awaitingBudgetCheck,
            'awaitingApproval' => $awaitingApproval,
            'awaitingFocalReview' => $awaitingFocalReview,
            'awaitingEdApproval' => $awaitingEdApproval,
        ]);
    }
}