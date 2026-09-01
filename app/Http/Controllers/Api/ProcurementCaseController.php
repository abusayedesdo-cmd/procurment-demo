<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementCase;
use Illuminate\Http\Request;

/**
 * Read-only API for Procurement Cases — backs the "Procurement Case"
 * select dropdown on the generic module pages (RFQ, etc). Case
 * creation/step progression stays on the web ProcurementCaseController
 * (Cases -> Create), this controller only lists/shows.
 */
class ProcurementCaseController extends Controller
{
    public function index(Request $request)
    {
        $items = ProcurementCase::query()
            ->with(['meetings' => fn ($q) => $q->where('meeting_type', 'first')])
            ->latest('id')
            ->paginate($request->integer('per_page', 20));

        $items->getCollection()->transform(fn ($case) => $this->withRfqHints($case));

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

    public function show(ProcurementCase $procurementCase)
    {
        $procurementCase->load(['purchaseRequisition', 'steps', 'meetings']);

        return response()->json([
            'success' => true,
            'data' => $procurementCase,
        ]);
    }

    /**
     * Adds a few flat, ready-to-use fields the RFQ form's autofill reads
     * from: the case's 1st meeting sets the tender's issue/closing dates,
     * and is_otm decides whether the RFQ defaults to 'RFQ' or 'OTM'.
     */
    private function withRfqHints(ProcurementCase $case): ProcurementCase
    {
        $firstMeeting = $case->meetings->first();

        // Keep in sync with RfqController::assertTypeMatchesPolicy() —
        // ESDO Procurement Policy §11.1/§11.3 thresholds.
        $threshold = $case->category === 'Works' ? 1500000 : 1000000;
        $case->setAttribute('rfq_type_hint', $case->amount > $threshold ? 'OTM' : 'RFQ');
        $case->setAttribute('issue_date_hint', optional($firstMeeting?->publish_date)->format('Y-m-d'));
        $case->setAttribute('closing_date_hint', optional($firstMeeting?->closing_date)->format('Y-m-d'));

        return $case;
    }
}