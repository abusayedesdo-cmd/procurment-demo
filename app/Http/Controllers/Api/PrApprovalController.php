<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrApproval;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Document section A footnote:
 * "Only after filling all the fields of the concerned form and approving
 * it will proceed to the next window."
 * Chain: Reviewer -> Accounts/Budget Checker -> [Focal Person OR Executive
 * Director]. The Budget Checker's "Recommend for Approval" (see
 * PrBudgetCheckController) is what forwards a PR to that final sign-off.
 *
 * Which role gives the final sign-off is chosen by the Budget Checker at
 * check time (see PrBudgetCheckController's 'route_to' field, stored on
 * the PR as routed_to) — Focal Person or Executive Director directly.
 * HIGH_VALUE_THRESHOLD is kept only as a fallback for PRs that reached
 * 'checked' before routed_to existed.
 *
 * (for BOQ/TOR/Design & Drawing windows the chain is: Reviewer -> Approver)
 */
class PrApprovalController extends Controller
{
    protected const CHAIN = [
        'BOQ' => ['reviewed', 'approved'],
        'TOR' => ['reviewed', 'approved'],
        'Design_Drawing' => ['reviewed', 'approved'],
    ];

    // 5 Lakh BDT. Keep in sync with the threshold check in
    // resources/views/purchase-requisitions/show.blade.php (isHighValue()).
    public const HIGH_VALUE_THRESHOLD = 500000;

    public function index(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $approvals = $purchaseRequisition->approvals()->with('user')->latest('acted_at')->get();

        return response()->json([
            'success' => true,
            'data' => $approvals,
        ]);
    }

    /**
     * Record a review/check/approval/rejection/return action on a PR and
     * advance (or reset) its status accordingly.
     */
    public function store(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $validated = $request->validate([
            'action' => 'required|in:approved,rejected,returned',
            'role_at_action' => 'required|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $isPr = $purchaseRequisition->window_type === 'PR';
        $isHighValue = (float) $purchaseRequisition->total_estimated_amount >= self::HIGH_VALUE_THRESHOLD;

        $chain = self::CHAIN[$purchaseRequisition->window_type] ?? self::CHAIN['BOQ'];
        $currentIndex = array_search($purchaseRequisition->status, $chain, true);

        // Who is actually allowed to act on this PR right now? For the
        // PR window, the 'reviewed' stage belongs to the Budget Checker
        // and must go through PrBudgetCheckController — not here, or the
        // budget check step could be silently skipped. Once the Budget
        // Checker forwards it ('checked'), routed_to (the Budget Checker's
        // own choice) decides who gives final sign-off. For PRs checked
        // before routed_to existed (routed_to is null), fall back to the
        // old amount-based rule. 'focal_reviewed' is a legacy status kept
        // for PRs that were already routed through the Focal Person before
        // this branching existed — those still need the ED.
        $userRole = $request->user()->roleName();
        $isAdmin = $userRole === \App\Models\User::ADMIN;

        $nextRole = match ($purchaseRequisition->routed_to) {
            'executive_director' => \App\Models\User::EXECUTIVE_DIRECTOR,
            'focal_person' => \App\Models\User::FOCAL_PERSON,
            default => $isHighValue ? \App\Models\User::EXECUTIVE_DIRECTOR : \App\Models\User::FOCAL_PERSON,
        };

        $requiredRole = $isPr
            ? match ($purchaseRequisition->status) {
                'draft' => \App\Models\User::REVIEWER,
                'checked' => $nextRole,
                'focal_reviewed' => \App\Models\User::EXECUTIVE_DIRECTOR,
                default => null,
            }
            : match ($purchaseRequisition->status) {
                'draft' => \App\Models\User::REVIEWER,
                'reviewed' => \App\Models\User::APPROVER,
                default => null,
            };

        if ($requiredRole === null) {
            return response()->json([
                'success' => false,
                'message' => "This PR (status: {$purchaseRequisition->status}) isn't actionable here right now.",
            ], 422);
        }

        if (! $isAdmin && $userRole !== $requiredRole) {
            return response()->json([
                'success' => false,
                'message' => "This step needs a {$requiredRole}, not a {$userRole}.",
            ], 403);
        }

        $result = DB::transaction(function () use ($request, $validated, $purchaseRequisition, $chain, $currentIndex, $isPr) {
            PrApproval::create([
                'pr_id' => $purchaseRequisition->id,
                'user_id' => $request->user()->id,
                'role_at_action' => $validated['role_at_action'],
                'action' => $validated['action'],
                'acted_at' => now()->toDateString(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($validated['action'] === 'rejected') {
                $purchaseRequisition->status = 'rejected';
            } elseif ($validated['action'] === 'returned') {
                $purchaseRequisition->status = 'draft';
            } elseif ($isPr) {
                // approved: for the PR window there's only ever one more
                // sign-off after the Budget Checker (Focal Person OR ED,
                // decided above by amount) — whichever of them acts, the
                // PR is final. 'focal_reviewed' (legacy) also finishes here.
                $purchaseRequisition->status = 'approved';
            } else {
                // BOQ/TOR/Design & Drawing: move to the next stage in the
                // fixed chain, or stay at 'approved' if already the last.
                $nextIndex = $currentIndex === false ? 0 : $currentIndex + 1;
                $purchaseRequisition->status = $chain[$nextIndex] ?? end($chain);
            }

            $purchaseRequisition->save();

            return $purchaseRequisition;
        });

        $result->load(['approvals.user']);

        return response()->json([
            'success' => true,
            'message' => 'Action recorded, PR status updated to "' . $result->status . '"',
            'data' => $result,
        ], 201);
    }
}