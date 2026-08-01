<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EligibilityReport;
use Illuminate\Http\Request;

class EligibilityReportController extends Controller
{
    public function index(Request $request)
    {
        $query = EligibilityReport::query();
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

    public function show(EligibilityReport $eligibilityReport)
    {
        $eligibilityReport->load(['rfq', 'preparedBy', 'items']);

        return response()->json([
            'success' => true,
            'data' => $eligibilityReport,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'prepared_by' => 'required|exists:users,id',
            'report_file' => 'nullable|string|max:255'
        ]);

        $eligibilityReport = EligibilityReport::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'EligibilityReport created successfully',
            'data' => $eligibilityReport,
        ], 201);
    }

    public function update(Request $request, EligibilityReport $eligibilityReport)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'prepared_by' => 'sometimes|required|exists:users,id',
            'report_file' => 'nullable|string|max:255'
        ]);

        $eligibilityReport->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'EligibilityReport updated successfully',
            'data' => $eligibilityReport,
        ]);
    }

    public function destroy(EligibilityReport $eligibilityReport)
    {
        $eligibilityReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'EligibilityReport deleted successfully',
        ]);
    }
}
