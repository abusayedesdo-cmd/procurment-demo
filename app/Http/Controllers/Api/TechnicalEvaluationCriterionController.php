<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicalEvaluationCriterion;
use Illuminate\Http\Request;

class TechnicalEvaluationCriterionController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalEvaluationCriterion::query()->with('report');
        if ($request->filled('ter_id')) {
            $query->where('ter_id', $request->query('ter_id'));
        }
        return response()->json(['success' => true, 'data' => $query->orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ter_id' => 'required|exists:technical_evaluation_reports,id',
            'name' => 'required|string|max:255',
            'max_marks' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);
        $criterion = TechnicalEvaluationCriterion::create($validated);
        return response()->json(['success' => true, 'data' => $criterion], 201);
    }

    public function show(TechnicalEvaluationCriterion $technicalEvaluationCriterion)
    {
        return response()->json(['success' => true, 'data' => $technicalEvaluationCriterion]);
    }

    public function update(Request $request, TechnicalEvaluationCriterion $technicalEvaluationCriterion)
    {
        $validated = $request->validate([
            'ter_id' => 'sometimes|required|exists:technical_evaluation_reports,id',
            'name' => 'sometimes|required|string|max:255',
            'max_marks' => 'sometimes|required|numeric|min:0',
            'sort_order' => 'nullable|integer',
        ]);
        $technicalEvaluationCriterion->update($validated);
        return response()->json(['success' => true, 'data' => $technicalEvaluationCriterion]);
    }

    public function destroy(TechnicalEvaluationCriterion $technicalEvaluationCriterion)
    {
        $technicalEvaluationCriterion->delete();
        return response()->json(['success' => true]);
    }
}