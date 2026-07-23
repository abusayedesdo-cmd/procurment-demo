<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicalEvaluationReport;
use Illuminate\Http\Request;

class TechnicalEvaluationReportController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalEvaluationReport::query();
        $query->with(['rfq', 'preparedBy', 'items']);

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

    public function show(TechnicalEvaluationReport $technicalEvaluationReport)
    {
        $technicalEvaluationReport->load(['rfq', 'preparedBy', 'items']);

        return response()->json([
            'success' => true,
            'data' => $technicalEvaluationReport,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'prepared_by' => 'required|exists:users,id',
            'report_file' => 'nullable|string|max:255'
        ]);

        $technicalEvaluationReport = TechnicalEvaluationReport::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TechnicalEvaluationReport created successfully',
            'data' => $technicalEvaluationReport,
        ], 201);
    }

    public function update(Request $request, TechnicalEvaluationReport $technicalEvaluationReport)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'prepared_by' => 'sometimes|required|exists:users,id',
            'report_file' => 'nullable|string|max:255'
        ]);

        $technicalEvaluationReport->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TechnicalEvaluationReport updated successfully',
            'data' => $technicalEvaluationReport,
        ]);
    }

    public function destroy(TechnicalEvaluationReport $technicalEvaluationReport)
    {
        $technicalEvaluationReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'TechnicalEvaluationReport deleted successfully',
        ]);
    }
}
