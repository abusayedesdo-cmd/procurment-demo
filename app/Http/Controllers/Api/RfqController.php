<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;

/**
 * Document section C, step 7 — "Create RFQ (As per RFQ)"
 * rfq_number is auto-generated (fiscal-year office memo format).
 */
class RfqController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = Rfq::query()->with(['procurementPlan.purchaseRequisition']);

        if ($request->filled('procurement_plan_id')) {
            $query->where('procurement_plan_id', $request->integer('procurement_plan_id'));
        }

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

    public function show(Rfq $rfq)
    {
        $rfq->load([
            'procurementPlan', 'tenderSchedules', 'tenderProposals', 'tenderAdvertisements',
            'items.unit', 'quotations.vendor', 'quotations.items', 'tenderOpenings.committeeMembers',
            'eligibilityReports', 'technicalEvaluationReports',
            'financialEvaluationReports', 'comparativeStatements',
        ]);

        return response()->json([
            'success' => true,
            'data' => $rfq,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'required|exists:procurement_plans,id',
            'type' => 'required|in:RFQ,OTM',
            'issue_date' => 'required|date',
            'closing_date' => 'required|date|after:issue_date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $rfq = Rfq::create($validated + [
            'rfq_number' => $this->numberGenerator->nextMemo(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RFQ created successfully',
            'data' => $rfq,
        ], 201);
    }

    public function update(Request $request, Rfq $rfq)
    {
        $validated = $request->validate([
            'type' => 'sometimes|required|in:RFQ,OTM',
            'issue_date' => 'sometimes|required|date',
            'closing_date' => 'sometimes|required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $rfq->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'RFQ updated successfully',
            'data' => $rfq,
        ]);
    }

    public function destroy(Rfq $rfq)
    {
        $rfq->delete();

        return response()->json([
            'success' => true,
            'message' => 'RFQ deleted successfully',
        ]);
    }
}
