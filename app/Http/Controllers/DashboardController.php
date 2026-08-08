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

        // Chain: draft (Reviewer) -> reviewed (Budget Checker) -> checked
        // (Approver) -> approved. Each role gets a direct list of the PRs
        // actually waiting on them, not just a generic unfiltered list.
        $awaitingReview = $canReview
            ? PurchaseRequisition::where('status', 'draft')->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        $awaitingBudgetCheck = $canCheckBudget
            ? PurchaseRequisition::where('status', 'reviewed')->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        $awaitingApproval = $canApprove
            ? PurchaseRequisition::where('status', 'checked')->orderBy('id')->get(['id', 'pr_number'])
            : collect();

        return view('dashboard', [
            'user' => $user,
            'draftPrs' => PurchaseRequisition::where('status', 'draft')->count(),
            'pendingPrs' => PurchaseRequisition::whereIn('status', ['reviewed', 'checked'])->count(),
            'approvedPrs' => PurchaseRequisition::where('status', 'approved')->count(),
            'activePlans' => ProcurementPlan::whereIn('status', ['planned', 'ongoing'])->count(),
            'contractsAwarded' => ContractAward::count(),
            'awaitingReview' => $awaitingReview,
            'awaitingBudgetCheck' => $awaitingBudgetCheck,
            'awaitingApproval' => $awaitingApproval,
        ]);
    }
}
