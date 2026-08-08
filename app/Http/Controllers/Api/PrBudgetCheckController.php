<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetLine;
use App\Models\PrApproval;
use App\Models\PrBudgetCheck;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrBudgetCheckController extends Controller
{
    protected const CHAIN = ['reviewed', 'checked', 'approved']; // budget check only applies to window_type = PR

    // GET /api/purchase-requisitions/{purchaseRequisition}/budget-check
    // What the Budget Checker (Accountant) sees before deciding: the PR +
    // its matched budget line + the figures for the "Budgetary Check" box
    // on the printed PR (Total allocated Budget / Remaining Budget B/F /
    // Amount of PR / Remaining Budget C/F / Name of Accountant).
    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->load('budgetLine.category');
        $line = $purchaseRequisition->budgetLine;
        $prAmount = (float) $purchaseRequisition->total_estimated_amount;

        return response()->json([
            'success' => true,
            'data' => [
                'pr' => $purchaseRequisition,
                'accountant_name' => request()->user()->name,
                'budget_line' => $line ? [
                    'id' => $line->id,
                    'code' => $line->item_code,
                    'name' => $line->item_name,
                    // "Total allocated Budget"
                    'total_allocated_budget' => (float) $line->approved_budget,
                    'spent' => $line->totalExpense(),
                    // "Remaining Budget B/F" — balance before this PR is deducted
                    'remaining_budget_bf' => $line->balance(),
                    // "Amount of PR"
                    'amount_of_pr' => $prAmount,
                    // "Remaining Budget C/F" — balance after this PR is deducted
                    'remaining_budget_cf' => $line->balance() - $prAmount,
                    'is_sufficient' => $line->hasSufficientBalance($prAmount),
                ] : null,
            ],
        ]);
    }

    // POST /api/purchase-requisitions/{purchaseRequisition}/budget-check
    public function store(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $userRole = $request->user()->roleName();
        if ($userRole !== \App\Models\User::BUDGET_CHECKER && $userRole !== \App\Models\User::ADMIN) {
            return response()->json([
                'success' => false,
                'message' => "This step needs a Budget Checker, not a {$userRole}.",
            ], 403);
        }
        if ($purchaseRequisition->window_type !== 'PR' || $purchaseRequisition->status !== 'reviewed') {
            return response()->json([
                'success' => false,
                'message' => "This PR (status: {$purchaseRequisition->status}) isn't awaiting a budget check right now.",
            ], 422);
        }

        $validated = $request->validate([
            'budget_line_id' => 'nullable|exists:budget_lines,id',
            'budget_code' => 'nullable|string|max:255',
            // Accountant-entered figures for the Budgetary Check box. They're
            // pre-filled from the budget line on the show() call but the
            // accountant can edit them, so they're validated and stored as
            // their own inputs rather than re-derived from the budget line.
            'allocated_budget' => 'required|numeric|min:0',
            'remaining_budget_bf' => 'required|numeric|min:0',
            'is_budget_code_verified' => 'required|boolean',
            'is_budget_available' => 'required|boolean',
            'decision' => 'required|in:recommended,approved,returned,rejected',
            'remarks' => 'nullable|string',
        ]);

        $line = ($validated['budget_line_id'] ?? null)
            ? BudgetLine::find($validated['budget_line_id'])
            : $purchaseRequisition->budgetLine;

        // Remaining Budget C/F = Remaining Budget B/F − Amount of PR, computed
        // server-side from what the accountant actually entered (not silently
        // re-derived from the budget line's live balance).
        $remainingCf = $validated['remaining_budget_bf'] - (float) $purchaseRequisition->total_estimated_amount;

        // If the PR would push the balance into the red, "Approved" isn't
        // allowed — the accountant can only Recommend it forward (for
        // someone else to decide) or Reject it outright.
        if ($validated['decision'] === 'approved' && $remainingCf < 0) {
            return response()->json([
                'success' => false,
                'message' => "Remaining Budget C/F is negative — this PR can't be Approved. Choose Recommend for Approval or Reject instead.",
            ], 422);
        }

        $check = DB::transaction(function () use ($request, $validated, $purchaseRequisition, $line, $remainingCf) {
            $action = match ($validated['decision']) {
                'recommended' => 'approved',
                'rejected' => 'rejected',
                // 'approved' (label: "Approved") and legacy 'returned' both
                // mean the same thing behaviorally: send the PR back to
                // draft. Only the decision value stored differs, so it's
                // no longer confusingly labeled "returned" in the DB.
                default => 'returned',
            };

            // 1. Generic approval-chain entry — keeps the existing Approvals tab working.
            $approval = PrApproval::create([
                'pr_id' => $purchaseRequisition->id,
                'user_id' => $request->user()->id,
                'role_at_action' => 'Budget Checker',
                'action' => $action,
                'acted_at' => now()->toDateString(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // 2. Budget-specific verification record (Module 1.4 fields).
            $check = PrBudgetCheck::create([
                'pr_id' => $purchaseRequisition->id,
                'pr_approval_id' => $approval->id,
                'budget_line_id' => $line?->id,
                'budget_code' => $validated['budget_code'] ?? $line?->item_code,
                'available_budget_amount' => $line?->balance(),
                'allocated_budget' => $validated['allocated_budget'],
                'remaining_budget_bf' => $validated['remaining_budget_bf'],
                'remaining_budget_cf' => $remainingCf,
                'is_budget_code_verified' => $validated['is_budget_code_verified'],
                'is_budget_available' => $validated['is_budget_available'],
                'decision' => $validated['decision'],
                'checked_by' => $request->user()->id,
                'checked_at' => now()->toDateString(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            if ($action === 'approved') {
                $currentIndex = array_search($purchaseRequisition->status, self::CHAIN, true);
                $nextIndex = $currentIndex === false ? 0 : $currentIndex + 1;
                $purchaseRequisition->status = self::CHAIN[$nextIndex] ?? end(self::CHAIN);
                $purchaseRequisition->save();

                // Post the spend now, so the balance is live for the next check anywhere in the system.
                if ($line) {
                    $line->expenses()->create([
                        'pr_id' => $purchaseRequisition->id,
                        'amount' => $purchaseRequisition->total_estimated_amount,
                        'expense_date' => now()->toDateString(),
                        'source' => 'pr',
                        'recorded_by' => $request->user()->id,
                    ]);
                }
            } elseif ($action === 'rejected') {
                // Final: budget insufficient / not approvable. Stops the chain here.
                $purchaseRequisition->status = 'rejected';
                $purchaseRequisition->save();
            } else {
                $purchaseRequisition->status = 'draft';
                $purchaseRequisition->save();
            }

            return $check;
        });

        $check->load(['budgetLine', 'checkedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Budget verification recorded, PR status updated to "' . $purchaseRequisition->fresh()->status . '"',
            'data' => $check,
        ], 201);
    }
}