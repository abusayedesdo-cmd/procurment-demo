<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rfq;
use App\Models\TenderOpening;
use Illuminate\Http\Request;

class TenderOpeningController extends Controller
{
    // ESDO Procurement Policy §11.1: minimum quotations required by
    // purchase amount. Below Tk 20,001 no formal quotation is required at
    // all (direct purchase) — that tier never reaches this module, since
    // an RFQ wouldn't normally exist for it.
    public const MIN_QUOTATIONS_TIER2 = 2; // Tk 20,001 - 50,000
    public const MIN_QUOTATIONS_TIER3 = 3; // Tk 50,001 and above
    public const TIER2_UPPER_BOUND = 50000;

    private function minQuotationsRequired(float $amount): int
    {
        if ($amount <= 20000) {
            return 0;
        }

        return $amount <= self::TIER2_UPPER_BOUND
            ? self::MIN_QUOTATIONS_TIER2
            : self::MIN_QUOTATIONS_TIER3;
    }

    public function index(Request $request)
    {
        $query = TenderOpening::query();
        $query->with(['rfq', 'openedBy']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
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

    public function show(TenderOpening $tenderOpening)
    {
        $tenderOpening->load([
            'rfq', 'openedBy', 'committeeMembers',
            'rfq.quotations.vendor',
        ]);

        return response()->json([
            'success' => true,
            'data' => $tenderOpening,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'opening_date' => 'required|date',
            'venue' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'opened_by' => 'required|exists:users,id',
            'report_file' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        $this->assertMinimumQuotations($validated['rfq_id']);

        $tenderOpening = TenderOpening::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderOpening created successfully',
            'data' => $tenderOpening,
        ], 201);
    }

    public function update(Request $request, TenderOpening $tenderOpening)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'opening_date' => 'sometimes|required|date',
            'venue' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'opened_by' => 'sometimes|required|exists:users,id',
            'report_file' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        if (isset($validated['rfq_id'])) {
            $this->assertMinimumQuotations($validated['rfq_id']);
        }

        $tenderOpening->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderOpening updated successfully',
            'data' => $tenderOpening,
        ]);
    }

    public function destroy(TenderOpening $tenderOpening)
    {
        $tenderOpening->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderOpening deleted successfully',
        ]);
    }

    /**
     * ESDO Procurement Policy §11.1: aborts with a 422 if the RFQ doesn't
     * yet have enough submitted quotations for its case's amount tier.
     */
    private function assertMinimumQuotations(int $rfqId): void
    {
        $rfq = Rfq::with('procurementCase')->findOrFail($rfqId);
        $amount = (float) ($rfq->procurementCase->amount ?? 0);
        $required = $this->minQuotationsRequired($amount);

        if ($required === 0) {
            return;
        }

        $received = $rfq->quotations()->count();

        abort_if($received < $required, 422,
            "Per ESDO Procurement Policy §11.1, a purchase of Tk. " . number_format($amount)
            . " requires at least {$required} quotations before opening. Only {$received} quotation(s) "
            . "have been recorded for this RFQ so far — record the remaining quotations first."
        );
    }
}