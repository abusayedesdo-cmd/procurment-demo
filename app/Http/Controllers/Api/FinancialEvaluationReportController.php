<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvaluationReport;
use App\Services\EvaluationVendorSeeder;
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

        // Vendor rows used to be typed in one by one, so a new report printed "[No ... items recorded yet]".
        $seeded = EvaluationVendorSeeder::seedFinancial($financialEvaluationReport);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "Financial Evaluation Report created with {$seeded} vendor(s) loaded automatically"
                : 'Financial Evaluation Report created — no vendors to load yet (earlier steps are empty); use "Load Vendors" once they are filled',
            'data' => $financialEvaluationReport->load('items'),
        ], 201);
    }

    /**
     * POST {id}/sync-vendors — adds the vendors that are not on the report yet
     * (rows that already exist are never touched or duplicated).
     */
    public function syncVendors(FinancialEvaluationReport $financialEvaluationReport)
    {
        $seeded = EvaluationVendorSeeder::seedFinancial($financialEvaluationReport);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "{$seeded} vendor(s) loaded"
                : 'No new vendors to add',
            'data' => $financialEvaluationReport->load('items'),
        ]);
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
