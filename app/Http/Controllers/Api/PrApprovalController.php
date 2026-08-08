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
 * Chain: Reviewer -> Accounts/Budget Checker -> Approver
 * (for BOQ/TOR/Design & Drawing windows the chain is: Reviewer -> Approver)
 */
class PrApprovalController extends Controller
{
    protected const CHAIN = [
        'PR' => ['reviewed', 'checked', 'approved'],
        'BOQ' => ['reviewed', 'approved'],
        'TOR' => ['reviewed', 'approved'],
        'Design_Drawing' => ['reviewed', 'approved'],
    ];

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

        $chain = self::CHAIN[$purchaseRequisition->window_type] ?? self::CHAIN['PR'];
        $currentIndex = array_search($purchaseRequisition->status, $chain, true);

        // Who is actually allowed to act on this PR right now? For the
        // PR window, the 'reviewed' stage belongs to the Budget Checker
        // and must go through PrBudgetCheckController — not here, or the
        // budget check step could be silently skipped.
        $userRole = $request->user()->roleName();
        $isAdmin = $userRole === \App\Models\User::ADMIN;

        $requiredRole = $purchaseRequisition->window_type === 'PR'
            ? match ($purchaseRequisition->status) {
                'draft' => \App\Models\User::REVIEWER,
                'checked' => \App\Models\User::APPROVER,
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

        $result = DB::transaction(function () use ($request, $validated, $purchaseRequisition, $chain, $currentIndex) {
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
            } else {
                // approved: move to the next stage in the chain, or stay
                // at 'approved' if this was already the last stage.
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
