<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvaluationItem;
use Illuminate\Http\Request;

class FinancialEvaluationItemController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialEvaluationItem::query();
        $query->with(['report', 'vendor']);

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(FinancialEvaluationItem $financialEvaluationItem)
    {
        $financialEvaluationItem->load(['report', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => $financialEvaluationItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fer_id' => 'required|exists:financial_evaluation_reports,id',
            'vendor_id' => 'required|exists:vendors,id',
            'quoted_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $financialEvaluationItem = FinancialEvaluationItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationItem created successfully',
            'data' => $financialEvaluationItem,
        ], 201);
    }

    public function update(Request $request, FinancialEvaluationItem $financialEvaluationItem)
    {
        $validated = $request->validate([
            'fer_id' => 'sometimes|required|exists:financial_evaluation_reports,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'quoted_amount' => 'sometimes|required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $financialEvaluationItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationItem updated successfully',
            'data' => $financialEvaluationItem,
        ]);
    }

    public function destroy(FinancialEvaluationItem $financialEvaluationItem)
    {
        $financialEvaluationItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationItem deleted successfully',
        ]);
    }
}
