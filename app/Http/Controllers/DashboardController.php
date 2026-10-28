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
        // Dashboard is the root of the step flow: forget old step history so that
        // "Back" on the first step opened from here returns to the dashboard.
        session()->forget('process_step_stack');
        $role = $user->roleName() ?? null;

        $canReview = in_array($role, [\App\Models\User::REVIEWER, \App\Models\User::ADMIN]);
        $canCheckBudget = in_array($role, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN]);
        $canApprove = in_array($role, [\App\Models\User::APPROVER, \App\Models\User::ADMIN]);
        $canFocalReview = in_array($role, [\App\Models\User::FOCAL_PERSON, \App\Models\User::ADMIN]);
        $canEdApprove = in_array($role, [\App\Models\User::EXECUTIVE_DIRECTOR, \App\Models\User::ADMIN]);

       
        $committeeIds = CommitteeScope::committeeIdsForUser($user);
        $isCommitteeOnly = CommitteeScope::isHoldingScoped($user);

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

        // All PRs this user may see (newest first) — feeds the pipeline counts and the
        // "Recent purchase requisitions" table on the dashboard.
        $allPrs = $scopePrs(PurchaseRequisition::orderByDesc('id'));
        $stageCounts = $allPrs->countBy('status');
        $recentPrs = $allPrs->take(8)->map(fn ($pr) => (object) $pr->only([
            'id', 'pr_number', 'project_name', 'requestor_name', 'total_estimated_amount', 'status', 'requisition_date',
        ]))->values();

        $awaitingReview = $canReview
            ? $scopePrs(PurchaseRequisition::where('status', 'draft')->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number', 'project_name', 'total_estimated_amount', 'created_at']))
            : collect();

        $awaitingBudgetCheck = $canCheckBudget
            ? $scopePrs(PurchaseRequisition::where('status', 'reviewed')->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number', 'project_name', 'total_estimated_amount', 'created_at']))
            : collect();

        // 'checked' status is exclusive to the PR window and now belongs to
        // Focal Person (see below). The Approver role only still acts on
        // the BOQ/TOR/Design & Drawing windows, at their 'reviewed' stage.
        $awaitingApproval = $canApprove
            ? $scopePrs(PurchaseRequisition::where('status', 'reviewed')->where('window_type', '!=', 'PR')->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number', 'project_name', 'total_estimated_amount', 'created_at']))
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
                ->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number', 'project_name', 'total_estimated_amount', 'created_at']))
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
                ->orderBy('id'))->map(fn ($pr) => (object) $pr->only(['id', 'pr_number', 'project_name', 'total_estimated_amount', 'created_at']))
            : collect();

        // PRs/cases that were transferred TO a committee this user is on (e.g. Main/Central
        // Committee -> Sub-Committee, or back to the Main Committee). Same "latest transfer
        // per plan decides who holds it" rule as My Committee Work, so an item drops out of
        // the bell automatically once it moves on to another committee.

        // Center/Main Procurement (or a Main Committee member, or Admin): approved PRs
        // that have no procurement case yet. These sit with the Main Committee by default,
        // so there is no SubCommitteeTransfer row and nothing else would put them in the bell.
        $isMainProcurement = $user->isAdmin()
            || CommitteeScope::isCenterProcurement($user)
            || CommitteeMember::where('user_id', $user->id)
                ->whereIn('committee_id', \App\Models\PurchaseCommittee::where('type', 'main')->select('id'))
                ->exists();

        $mainPrModels = $isMainProcurement
            ? $scopePrs(PurchaseRequisition::where('status', 'approved')
                ->whereDoesntHave('procurementCase')
                ->orderBy('id'))
            : collect();

        // When each PR actually reached Main Procurement = time of its final "approved" sign-off.
        // (pr_approvals.acted_at is date-only, so use the row's created_at; fall back to updated_at.)
        $approvedAt = $mainPrModels->isEmpty()
            ? collect()
            : \App\Models\PrApproval::whereIn('pr_id', $mainPrModels->pluck('id'))
                ->where('action', 'approved')
                ->selectRaw('pr_id, MAX(created_at) as arrived_at')
                ->groupBy('pr_id')
                ->pluck('arrived_at', 'pr_id');

        $awaitingMainProcurement = $mainPrModels->map(function ($pr) use ($approvedAt) {
            $arrived = \Illuminate\Support\Carbon::parse($approvedAt[$pr->id] ?? $pr->updated_at, config('app.timezone'))
                ->timezone('Asia/Dhaka');

            return (object) array_merge(
                $pr->only(['id', 'pr_number', 'project_name', 'total_estimated_amount', 'created_at']),
                ['arrived_at' => $arrived]
            );
        })->sortByDesc('arrived_at')->values();

        $transferredToMe = (function () use ($user) {
            $myCommitteeIds = CommitteeMember::where('user_id', $user->id)->pluck('committee_id');
            if ($myCommitteeIds->isEmpty()) {
                return collect();
            }

            return SubCommitteeTransfer::with([
                    'fromCommittee',
                    'toCommittee',
                    'procurementPlan.purchaseRequisition.procurementCase',
                ])
                ->orderByDesc('transfer_date')
                ->orderByDesc('id')
                ->get()
                ->unique('procurement_plan_id')
                ->filter(fn ($t) => $myCommitteeIds->contains($t->to_committee_id))
                ->map(function ($t) {
                    $pr = $t->procurementPlan?->purchaseRequisition;
                    $case = $pr?->procurementCase;

                    return (object) [
                        'pr_number' => $pr?->pr_number ?? ('PR #' . ($t->procurementPlan?->pr_id ?? '?')),
                        'case_id' => $case?->id,
                        'from' => $t->fromCommittee?->name,
                        'to' => $t->toCommittee?->name,
                        'date' => $t->transfer_date,
                    ];
                })
                ->values();
        })();

        return view('dashboard', [
            'user' => $user,
            'transferredToMe' => $transferredToMe,
            'stageCounts' => $stageCounts,
            'recentPrs' => $recentPrs,
            'hasCommittee' => ! empty($committeeIds),
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
            'isMainProcurement' => $isMainProcurement,
            'awaitingMainProcurement' => $awaitingMainProcurement,
        ]);
    }
}
