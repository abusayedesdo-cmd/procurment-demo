<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query();
        $query->with(['rfq.procurementCase', 'vendor']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
        }

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));

        $user = $request->user();
        $items->setCollection(
            $items->getCollection()
                ->filter(fn ($q) => CommitteeScope::userCanActOnCase($user, $q->rfq?->procurementCase))
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

    public function show(Quotation $quotation)
    {
        $quotation->load(['rfq.procurementCase', 'vendor', 'items.rfqItem.unit']);

        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $quotation->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        return response()->json([
            'success' => true,
            'data' => $quotation,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'vendor_id' => 'required|exists:vendors,id',
            'submitted_at' => 'required|date',
            'quoted_amount' => 'required|numeric|min:0',
            'file_path' => 'nullable|string|max:255',
            'status' => 'nullable|in:received,opened,evaluated,disqualified,forwarded,rejected',
            'representative_name' => 'nullable|string|max:255',
            'representative_contact' => 'nullable|string|max:50',
        ]);

        $rfq = Rfq::with('procurementCase')->findOrFail($validated['rfq_id']);
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $quotation = Quotation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quotation created successfully',
            'data' => $quotation,
        ], 201);
    }

    public function submissionPreview(Quotation $quotation)
    {
        $quotation->load(['rfq', 'vendor.documents', 'items.rfqItem']);

        return view('quotations.submission-preview', ['quotation' => $quotation]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $quotation->loadMissing('rfq.procurementCase');
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $quotation->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'submitted_at' => 'sometimes|required|date',
            'quoted_amount' => 'sometimes|required|numeric|min:0',
            'file_path' => 'nullable|string|max:255',
            'status' => 'nullable|in:received,opened,evaluated,disqualified,forwarded,rejected',
            // Recorded at Tender/RFQ Opening — attendance & eligibility checklist.
            'representative_name' => 'nullable|string|max:255',
            'representative_contact' => 'nullable|string|max:50',
            'attended' => 'sometimes|boolean',
            'trade_license_submitted' => 'sometimes|boolean',
            'tin_submitted' => 'sometimes|boolean',
            'bin_submitted' => 'sometimes|boolean',
            'opening_remarks' => 'nullable|string',
        ]);

        $quotation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quotation updated successfully',
            'data' => $quotation,
        ]);
    }

    // Statuses a quotation can still be in when the committee reviews the
    // Opening Report. Once it is forwarded or rejected, it is settled.
    private const OPENING_REVIEWABLE = ['received', 'opened'];

    /**
     * Step 11 — "Forward for Evaluation". Marks the RFQ's pending quotations
     * (all of them, or only `quotation_ids`) as forwarded so they move on to
     * Eligibility / Technical / Financial evaluation.
     */
    public function forwardForEvaluation(Request $request, Rfq $rfq)
    {
        $targets = $this->openingReviewTargets($request, $rfq);
        $now = now();

        foreach ($targets as $quotation) {
            $quotation->update([
                'status' => 'forwarded',
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => $now,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $targets->count() . ' quotation(s) forwarded for evaluation.',
            'data' => $targets->pluck('id'),
        ]);
    }

    /**
     * Step 11 — "Reject Quotations". A reason is mandatory so the rejection
     * is on record (it also prints/shows next to the quotation).
     */
    public function rejectAtOpening(Request $request, Rfq $rfq)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $targets = $this->openingReviewTargets($request, $rfq);
        $now = now();

        foreach ($targets as $quotation) {
            $quotation->update([
                'status' => 'rejected',
                'rejection_reason' => $request->input('reason'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => $now,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $targets->count() . ' quotation(s) rejected.',
            'data' => $targets->pluck('id'),
        ]);
    }

    /** Shared by forward/reject: committee-scope check + which quotations to act on. */
    private function openingReviewTargets(Request $request, Rfq $rfq)
    {
        $rfq->loadMissing('procurementCase');
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $data = $request->validate([
            'quotation_ids' => 'nullable|array',
            'quotation_ids.*' => 'integer',
        ]);

        $query = Quotation::where('rfq_id', $rfq->id)->whereIn('status', self::OPENING_REVIEWABLE);
        if (! empty($data['quotation_ids'])) {
            $query->whereIn('id', $data['quotation_ids']);
        }

        $targets = $query->get();
        abort_if($targets->isEmpty(), 422, 'No quotations are waiting for review on this RFQ (already forwarded or rejected).');

        return $targets;
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->loadMissing('rfq.procurementCase');
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $quotation->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $quotation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quotation deleted successfully',
        ]);
    }
}
