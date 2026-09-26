<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderProposal;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;

class TenderProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderProposal::query();
        $query->with(['rfq']);
        $query = TenderProposal::query()->with('rfq.procurementCase');

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
        }

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));
        $user = $request->user();
        $items->setCollection(
            $items->getCollection()
                ->filter(fn ($row) => CommitteeScope::userCanActOnCase($user, $row->rfq?->procurementCase))
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

    public function show(TenderProposal $tenderProposal)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $tenderProposal->rfq?->procurementCase),
            403
        );
        $tenderProposal->load(['rfq']);

        return response()->json([
            'success' => true,
            'data' => $tenderProposal,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'proposal_details' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $rfq = \App\Models\Rfq::with('procurementCase')->findOrFail($validated['rfq_id']);
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $tenderProposal = TenderProposal::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderProposal created successfully',
            'data' => $tenderProposal,
        ], 201);
    }

    public function update(Request $request, TenderProposal $tenderProposal)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $tenderProposal->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'proposal_details' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        if (! empty($validated['rfq_id']) && $validated['rfq_id'] != $tenderProposal->rfq_id) {
            $newRfq = \App\Models\Rfq::with('procurementCase')->findOrFail($validated['rfq_id']);
            abort_unless(
                CommitteeScope::userCanActOnCase($request->user(), $newRfq->procurementCase),
                403,
                'This case is currently with a different committee.'
            );
        }

        $tenderProposal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderProposal updated successfully',
            'data' => $tenderProposal,
        ]);
    }

    public function destroy(TenderProposal $tenderProposal)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $tenderProposal->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $tenderProposal->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderProposal deleted successfully',
        ]);
    }
}
