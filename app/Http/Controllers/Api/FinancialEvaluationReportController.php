<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvaluationReport;
use Illuminate\Http\Request;

class FinancialEvaluationReportController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialEvaluationReport::query();
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

    public function show(FinancialEvaluationReport $financialEvaluationReport)
    {
        $financialEvaluationReport->load(['rfq', 'preparedBy', 'items']);

        return response()->json([
            'success' => true,
            'data' => $financialEvaluationReport,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'prepared_by' => 'required|exists:users,id',
            'report_file' => 'nullable|string|max:255'
        ]);

        $financialEvaluationReport = FinancialEvaluationReport::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationReport created successfully',
            'data' => $financialEvaluationReport,
        ], 201);
    }

    public function update(Request $request, FinancialEvaluationReport $financialEvaluationReport)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'prepared_by' => 'sometimes|required|exists:users,id',
            'report_file' => 'nullable|string|max:255'
        ]);

        $financialEvaluationReport->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationReport updated successfully',
            'data' => $financialEvaluationReport,
        ]);
    }

    public function destroy(FinancialEvaluationReport $financialEvaluationReport)
    {
        $financialEvaluationReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationReport deleted successfully',
        ]);
    }
}
