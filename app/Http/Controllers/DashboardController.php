<?php

namespace App\Http\Controllers;

use App\Models\ContractAward;
use App\Models\ProcurementPlan;
use App\Models\PurchaseRequisition;
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

        // Chain: draft (Reviewer) -> reviewed (Budget Checker) -> checked
        // (Focal Person OR Executive Director, per the Budget Checker's own
        // 'route_to' choice, stored as routed_to) -> approved. For PRs
        // checked before routed_to existed (routed_to is null), fall back
        // to the old amount-based rule (see
        // PrApprovalController::HIGH_VALUE_THRESHOLD). 'focal_reviewed' is
        // a legacy status kept for PRs already routed through the Focal
        // Person before this branching existed — those still need the ED.
        // Each role gets a direct list of the PRs actually waiting on them,
        // not just a generic unfiltered list.
        $awaitingReview = $canReview
            ? PurchaseRequisition::where('status', 'draft')->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        $awaitingBudgetCheck = $canCheckBudget
            ? PurchaseRequisition::where('status', 'reviewed')->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        // 'checked' status is exclusive to the PR window and now belongs to
        // Focal Person (see below). The Approver role only still acts on
        // the BOQ/TOR/Design & Drawing windows, at their 'reviewed' stage.
        $awaitingApproval = $canApprove
            ? PurchaseRequisition::where('status', 'reviewed')->where('window_type', '!=', 'PR')->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        $threshold = \App\Http\Controllers\Api\PrApprovalController::HIGH_VALUE_THRESHOLD;

        $awaitingFocalReview = $canFocalReview
            ? PurchaseRequisition::where('status', 'checked')
                ->where(function ($q) use ($threshold) {
                    $q->where('routed_to', 'focal_person')
                        ->orWhere(function ($q2) use ($threshold) {
                            $q2->whereNull('routed_to')->where('total_estimated_amount', '<', $threshold);
                        });
                })
                ->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        $awaitingEdApproval = $canEdApprove
            ? PurchaseRequisition::where(function ($q) use ($threshold) {
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
                ->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        return view('dashboard', [
            'user' => $user,
            'draftPrs' => PurchaseRequisition::where('status', 'draft')->count(),
            'pendingPrs' => PurchaseRequisition::whereIn('status', ['reviewed', 'checked', 'focal_reviewed'])->count(),
            'approvedPrs' => PurchaseRequisition::where('status', 'approved')->count(),
            'activePlans' => ProcurementPlan::whereIn('status', ['planned', 'ongoing'])->count(),
            'contractsAwarded' => ContractAward::count(),
            'awaitingReview' => $awaitingReview,
            'awaitingBudgetCheck' => $awaitingBudgetCheck,
            'awaitingApproval' => $awaitingApproval,
            'awaitingFocalReview' => $awaitingFocalReview,
            'awaitingEdApproval' => $awaitingEdApproval,
        ]);
    }
}