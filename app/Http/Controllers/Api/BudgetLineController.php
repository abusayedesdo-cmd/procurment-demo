<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetCategory;
use App\Models\BudgetLine;

class BudgetLineController extends Controller
{
    // GET /api/budget-dashboard
    public function dashboard()
    {
        $categories = BudgetCategory::with('budgetLines.expenses')->orderBy('sort_order')->get();

        $data = $categories->map(function ($category) {
            $lines = $category->budgetLines->map(function ($line) {
                $spent = $line->totalExpense();

                return [
                    'id' => $line->id,
                    'item_code' => $line->item_code,
                    'item_name' => $line->item_name,
                    'approved_budget' => (float) $line->approved_budget,
                    'spent' => $spent,
                    'balance' => $line->balance(),
                    'percent_used' => $line->approved_budget > 0
                        ? round($spent / $line->approved_budget * 100, 1)
                        : 0,
                ];
            });

            return [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'lines' => $lines,
                'total_budget' => $lines->sum('approved_budget'),
                'total_spent' => $lines->sum('spent'),
                'total_balance' => $lines->sum('balance'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'grand_total' => [
                'budget' => $data->sum('total_budget'),
                'spent' => $data->sum('total_spent'),
                'balance' => $data->sum('total_balance'),
            ],
        ]);
    }

    // GET /api/budget-lines  (flat list for dropdowns e.g. tagging a PR)
    public function index()
    {
        $lines = BudgetLine::with('category')->orderBy('item_code')->get()->map(fn ($l) => [
            'id' => $l->id,
            'code' => $l->item_code,
            'name' => $l->item_name,
            'category' => $l->category->name,
            'balance' => $l->balance(),
        ]);

        return response()->json(['success' => true, 'data' => $lines]);
    }

    // POST /api/budget-lines
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'budget_category_id' => ['required', 'exists:budget_categories,id'],
            'chart_of_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'item_code' => ['required', 'string', 'max:255'],
            'item_name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'no_of_units' => ['nullable', 'numeric'],
            'duration' => ['nullable', 'integer'],
            'unit_cost' => ['nullable', 'numeric'],
            'original_budget' => ['nullable', 'numeric'],
            'approved_budget' => ['nullable', 'numeric'],
            'percent_change' => ['nullable', 'numeric'],
            'realignment_remarks' => ['nullable', 'string'],
            'reported_actual_expense' => ['nullable', 'numeric'],
        ]);

        $line = BudgetLine::create($validated);

        return response()->json(['success' => true, 'data' => $line->load('category')], 201);
    }

    // PUT /api/budget-lines/{budgetLine}
    public function update(\Illuminate\Http\Request $request, BudgetLine $budgetLine)
    {
        $validated = $request->validate([
            'budget_category_id' => ['sometimes', 'required', 'exists:budget_categories,id'],
            'chart_of_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'item_code' => ['sometimes', 'required', 'string', 'max:255'],
            'item_name' => ['sometimes', 'required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'no_of_units' => ['nullable', 'numeric'],
            'duration' => ['nullable', 'integer'],
            'unit_cost' => ['nullable', 'numeric'],
            'original_budget' => ['nullable', 'numeric'],
            'approved_budget' => ['nullable', 'numeric'],
            'percent_change' => ['nullable', 'numeric'],
            'realignment_remarks' => ['nullable', 'string'],
            'reported_actual_expense' => ['nullable', 'numeric'],
        ]);

        $budgetLine->update($validated);

        return response()->json(['success' => true, 'data' => $budgetLine->fresh('category')]);
    }

    // DELETE /api/budget-lines/{budgetLine}
    public function destroy(BudgetLine $budgetLine)
    {
        $budgetLine->delete();

        return response()->json(['success' => true]);
    }
}