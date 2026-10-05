<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EligibilityReport;
use App\Models\EligibilityReportItem;
use App\Models\Quotation;
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

        // Vendor Result rows used to be a completely separate manual step, so a
        // freshly-created report printed "[No eligibility items recorded yet]".
        // Seed one row per quotation of this RFQ up front instead.
        $seeded = $this->seedItemsFromQuotations($eligibilityReport);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "EligibilityReport created with {$seeded} vendor(s) loaded from quotations"
                : 'EligibilityReport created — no quotations found for this RFQ yet, add vendors from the Vendor Result step',
            'data' => $eligibilityReport,
        ], 201);
    }

    /**
     * POST eligibility-reports/{id}/sync-vendors — for reports that already
     * exist (or were created before any quotation arrived): adds a Vendor
     * Result row for every quotation not yet on the report. Never touches or
     * duplicates rows that are already there.
     */
    public function syncVendors(EligibilityReport $eligibilityReport)
    {
        $seeded = $this->seedItemsFromQuotations($eligibilityReport);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "{$seeded} vendor(s) loaded from quotations"
                : 'No new vendors to add — no further quotations found for this RFQ (rejected/disqualified ones are skipped)',
            'data' => $eligibilityReport->load('items'),
        ]);
    }

    /**
     * One Vendor Result row per quotation of the report's RFQ, with the 4
     * verification boxes pre-ticked from what the vendor actually submitted
     * (staff can still correct them afterwards). If any quotation was already
     * "Forwarded for Evaluation" at the Opening step only those are used;
     * otherwise every quotation that is not rejected/disqualified.
     */
    private function seedItemsFromQuotations(EligibilityReport $report): int
    {
        $quotations = Quotation::where('rfq_id', $report->rfq_id)
            ->whereNotIn('status', ['rejected', 'disqualified'])
            ->get();

        $forwarded = $quotations->where('status', 'forwarded');
        if ($forwarded->isNotEmpty()) {
            $quotations = $forwarded;
        }

        $already = $report->items()->pluck('vendor_id')->all();
        $count = 0;

        foreach ($quotations as $q) {
            if (in_array($q->vendor_id, $already, true)) {
                continue;
            }

            $tl = (bool) $q->trade_license_submitted;
            $tin = (bool) $q->tin_submitted;
            $bin = (bool) $q->bin_submitted;
            $psr = (bool) $q->psr_submitted;

            EligibilityReportItem::create([
                'eligibility_report_id' => $report->id,
                'vendor_id' => $q->vendor_id,
                'quotation_id' => $q->id,
                'trade_license_verified' => $tl,
                'tin_verified' => $tin,
                'bin_verified' => $bin,
                'psr_verified' => $psr,
                'eligible' => $tl && $tin && $bin && $psr,
            ]);

            $already[] = $q->vendor_id;
            $count++;
        }

        return $count;
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
