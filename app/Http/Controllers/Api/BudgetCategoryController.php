<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;

class BudgetCategoryController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => BudgetCategory::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:budget_categories,code',
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $category = BudgetCategory::create($validated);

        return response()->json(['success' => true, 'data' => $category], 201);
    }

    public function update(Request $request, BudgetCategory $budgetCategory)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:20|unique:budget_categories,code,' . $budgetCategory->id,
            'name' => 'sometimes|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $budgetCategory->update($validated);

        return response()->json(['success' => true, 'data' => $budgetCategory]);
    }

    public function destroy(BudgetCategory $budgetCategory)
    {
        $budgetCategory->delete();

        return response()->json(['success' => true]);
    }
}