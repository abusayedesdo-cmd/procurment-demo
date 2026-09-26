<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementCase;
use App\Models\Rfq;
use App\Services\NumberGeneratorService;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;

/**
 * Document section C, step 7 — "Create RFQ (As per RFQ)"
 * rfq_number is auto-generated (fiscal-year office memo format).
 */
class RfqController extends Controller
{
    // ESDO Procurement Policy §11.1 (Goods/Services) and §11.3 (Works):
    // above these amounts, the case must go through Open Tender Method
    // (OTM) — a plain RFQ is no longer allowed. Works gets a higher
    // ceiling (Tk 15,00,000) than Goods/Services (Tk 10,00,000).
    public const OTM_THRESHOLD_GOODS_SERVICES = 1000000;
    public const OTM_THRESHOLD_WORKS = 1500000;

    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = Rfq::query()->with(['procurementCase.purchaseRequisition']);

        if ($request->filled('procurement_case_id')) {
            $query->where('procurement_case_id', $request->integer('procurement_case_id'));
        }

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));

        $user = $request->user();
        $items->setCollection(
            $items->getCollection()
                ->filter(fn ($rfq) => CommitteeScope::userCanActOnCase($user, $rfq->procurementCase))
                ->values()
        );

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
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $rfq->load([
            'procurementCase', 'tenderSchedules', 'tenderProposals', 'tenderAdvertisements',
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
            'procurement_case_id' => 'required|exists:procurement_cases,id',
            'subject' => 'required|string|max:255',
            'type' => 'required|in:RFQ,OTM,RFP',
            'issue_date' => 'required|date',
            'closing_date' => 'required|date|after:issue_date',
            'terms_conditions' => 'nullable|string',
            'terms_condition_ids' => 'nullable|array',
            'terms_condition_ids.*' => 'integer|exists:rfq_terms_conditions,id',
            'distribution_process' => 'nullable|in:Email,Hand Distribution',
            'file_path' => 'nullable|string|max:255',
        ]);

        $case = ProcurementCase::findOrFail($validated['procurement_case_id']);
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $case),
            403,
            'This case is currently with a different committee.'
        );
        $this->assertTypeMatchesPolicy($case, $validated['type']);
        $this->assertClosingWindow($validated['type'], $validated['issue_date'], $validated['closing_date']);

        $rfq = Rfq::create(
            \Illuminate\Support\Arr::except($validated, 'terms_condition_ids') + [
                'rfq_number' => $this->numberGenerator->nextCommitteeMemo('Purchases Committee'),
            ]
        );

        if (! empty($validated['terms_condition_ids'])) {
            $rfq->termsConditions()->sync($validated['terms_condition_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'RFQ created successfully',
            'data' => $rfq,
        ], 201);
    }

    public function update(Request $request, Rfq $rfq)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );
        abort_if($rfq->status === 'finalized', 422, 'This RFQ has been finalized and can no longer be edited.');

        $validated = $request->validate([
            'type' => 'sometimes|required|in:RFQ,OTM,RFP',
            'issue_date' => 'sometimes|required|date',
            'closing_date' => 'sometimes|required|date',
            'terms_conditions' => 'nullable|string',
            'terms_condition_ids' => 'nullable|array',
            'terms_condition_ids.*' => 'integer|exists:rfq_terms_conditions,id',
            'distribution_process' => 'nullable|in:Email,Hand Distribution',
            'file_path' => 'nullable|string|max:255',
        ]);

        if (isset($validated['type'])) {
            $this->assertTypeMatchesPolicy($rfq->procurementCase, $validated['type']);
        }

        $this->assertClosingWindow(
            $validated['type'] ?? $rfq->type,
            $validated['issue_date'] ?? $rfq->issue_date,
            $validated['closing_date'] ?? $rfq->closing_date
        );

        $rfq->update(\Illuminate\Support\Arr::except($validated, 'terms_condition_ids'));

        if (array_key_exists('terms_condition_ids', $validated)) {
            $rfq->termsConditions()->sync($validated['terms_condition_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'RFQ updated successfully',
            'data' => $rfq,
        ]);
    }

    /**
     * Step 7 "Finalized RFQ" — locks the RFQ from further edits. Once
     * finalized, it's the version that was actually sent to vendors.
     */
    public function finalize(Request $request, Rfq $rfq)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );
        abort_if($rfq->status === 'finalized', 422, 'This RFQ is already finalized.');

        $rfq->update([
            'status' => 'finalized',
            'finalized_at' => now(),
            'finalized_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RFQ finalized — it can no longer be edited.',
            'data' => $rfq,
        ]);
    }

    public function prItems(Rfq $rfq)
    {
        $pr = $rfq->procurementCase?->purchaseRequisition;
        $items = $pr ? $pr->items()->with(['item', 'unit'])->orderBy('serial_no')->get() : collect();

        return response()->json([
            'success' => true,
            'data' => $items->map(fn ($i) => [
                'id' => $i->id,
                'description' => trim(($i->item->name ?? '') . ($i->specification ? ' — ' . $i->specification : '')),
                'quantity' => $i->quantity,
                'unit_id' => $i->unit_id,
            ]),
        ]);
    }

    public function destroy(Rfq $rfq)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $rfq->delete();

        return response()->json([
            'success' => true,
            'message' => 'RFQ deleted successfully',
        ]);
    }

    /**
     * Aborts with a 422 if the chosen type doesn't match what the case's
     * amount and category require under the policy thresholds above.
     */
    private function assertTypeMatchesPolicy(ProcurementCase $case, string $type): void
    {
        $threshold = $case->category === 'Works'
            ? self::OTM_THRESHOLD_WORKS
            : self::OTM_THRESHOLD_GOODS_SERVICES;

        // Step 8 (RFP/RFI/Hiring Vendor/Consultant): a Services-category case
        // goes through RFP instead of a plain RFQ, below the same threshold —
        // above it, it still needs the open Tender/OTM route like anything else.
        $required = $case->amount > $threshold
            ? 'OTM'
            : ($case->category === 'Services' ? 'RFP' : 'RFQ');

        abort_if($type !== $required, 422,
            "Per ESDO Procurement Policy §11, a {$case->category} case of Tk. "
            . number_format($case->amount) . " (threshold Tk. " . number_format($threshold)
            . ") must use type '{$required}', not '{$type}'."
        );
    }

    /**
     * Process doc, Step 7: an RFQ's closing date must sit 5–7 days after
     * its issue date. (Step 9's own 14-day OTM rule is separate and not
     * enforced here — only plain RFQs are checked.)
     */
    private function assertClosingWindow(string $type, string $issueDate, string $closingDate): void
    {
        // Step 9's own 14-day OTM rule isn't enforced here yet — future work.
        if ($type !== 'RFQ' && $type !== 'RFP') {
            return;
        }

        $issue = \Carbon\Carbon::parse($issueDate)->startOfDay();
        $closing = \Carbon\Carbon::parse($closingDate)->startOfDay();
        $days = $issue->diffInDays($closing);

        // Doc: RFQ is a fixed 5–7 days; RFP/RFI/Hiring Vendor-Consultant is a
        // wider 3–7 days (the doc lists "3/5/7" without separating RFP vs RFI
        // vs Hiring — a single 'RFP' type covers all three here, so the widest
        // of the three windows is what's enforced).
        [$min, $max] = $type === 'RFQ' ? [5, 7] : [3, 7];

        abort_if($days < $min || $days > $max, 422,
            "A {$type}'s closing date must be {$min}–{$max} days after the issue date (this is {$days} day(s))."
        );
    }
}