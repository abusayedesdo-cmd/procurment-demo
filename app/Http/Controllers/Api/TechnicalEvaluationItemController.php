<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicalEvaluationItem;
use Illuminate\Http\Request;

class TechnicalEvaluationItemController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalEvaluationItem::query();
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

    public function show(TechnicalEvaluationItem $technicalEvaluationItem)
    {
        $technicalEvaluationItem->load(['report', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => $technicalEvaluationItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ter_id' => 'required|exists:technical_evaluation_reports,id',
            'vendor_id' => 'required|exists:vendors,id',
            'score' => 'nullable|numeric|min:0|max:100',
            'remarks' => 'nullable|string'
        ]);

        $technicalEvaluationItem = TechnicalEvaluationItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TechnicalEvaluationItem created successfully',
            'data' => $technicalEvaluationItem,
        ], 201);
    }

    public function update(Request $request, TechnicalEvaluationItem $technicalEvaluationItem)
    {
        $validated = $request->validate([
            'ter_id' => 'sometimes|required|exists:technical_evaluation_reports,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'score' => 'nullable|numeric|min:0|max:100',
            'remarks' => 'nullable|string'
        ]);

        $technicalEvaluationItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TechnicalEvaluationItem updated successfully',
            'data' => $technicalEvaluationItem,
        ]);
    }

    public function destroy(TechnicalEvaluationItem $technicalEvaluationItem)
    {
        $technicalEvaluationItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'TechnicalEvaluationItem deleted successfully',
        ]);
    }
}
