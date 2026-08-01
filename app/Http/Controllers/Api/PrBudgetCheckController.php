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
    // What the Budget Checker sees before deciding: the PR + its matched
    // budget line + the live available balance.
    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->load('budgetLine.category');
        $line = $purchaseRequisition->budgetLine;

        return response()->json([
            'success' => true,
            'data' => [
                'pr' => $purchaseRequisition,
                'budget_line' => $line ? [
                    'id' => $line->id,
                    'code' => $line->item_code,
                    'name' => $line->item_name,
                    'approved_budget' => (float) $line->approved_budget,
                    'spent' => $line->totalExpense(),
                    'available_budget_amount' => $line->balance(),
                    'is_sufficient' => $line->hasSufficientBalance((float) $purchaseRequisition->total_estimated_amount),
                ] : null,
            ],
        ]);
    }

    // POST /api/purchase-requisitions/{purchaseRequisition}/budget-check
    public function store(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $validated = $request->validate([
            'budget_line_id' => 'nullable|exists:budget_lines,id',
            'budget_code' => 'nullable|string|max:255',
            'is_budget_code_verified' => 'required|boolean',
            'is_budget_available' => 'required|boolean',
            'decision' => 'required|in:recommended,returned',
            'remarks' => 'nullable|string',
        ]);

        $line = ($validated['budget_line_id'] ?? null)
            ? BudgetLine::find($validated['budget_line_id'])
            : $purchaseRequisition->budgetLine;

        $check = DB::transaction(function () use ($request, $validated, $purchaseRequisition, $line) {
            $action = $validated['decision'] === 'recommended' ? 'approved' : 'returned';

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