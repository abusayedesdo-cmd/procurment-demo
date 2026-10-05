<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicalEvaluationReport;
use App\Services\EvaluationVendorSeeder;
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

        // Vendor rows used to be typed in one by one, so a new report printed "[No ... items recorded yet]".
        $seeded = EvaluationVendorSeeder::seedTechnical($technicalEvaluationReport);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "Technical Evaluation Report created with {$seeded} vendor(s) loaded automatically"
                : 'Technical Evaluation Report created — no vendors to load yet (earlier steps are empty); use "Load Vendors" once they are filled',
            'data' => $technicalEvaluationReport->load('items'),
        ], 201);
    }

    /**
     * POST {id}/sync-vendors — adds the vendors that are not on the report yet
     * (rows that already exist are never touched or duplicated).
     */
    public function syncVendors(TechnicalEvaluationReport $technicalEvaluationReport)
    {
        $seeded = EvaluationVendorSeeder::seedTechnical($technicalEvaluationReport);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "{$seeded} vendor(s) loaded"
                : 'No new vendors to add',
            'data' => $technicalEvaluationReport->load('items'),
        ]);
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
