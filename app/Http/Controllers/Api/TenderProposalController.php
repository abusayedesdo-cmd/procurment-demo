<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderProposal;
use Illuminate\Http\Request;

class TenderProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderProposal::query();
        $query->with(['rfq']);

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

    public function show(TenderProposal $tenderProposal)
    {
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

        $tenderProposal = TenderProposal::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderProposal created successfully',
            'data' => $tenderProposal,
        ], 201);
    }

    public function update(Request $request, TenderProposal $tenderProposal)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'proposal_details' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $tenderProposal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderProposal updated successfully',
            'data' => $tenderProposal,
        ]);
    }

    public function destroy(TenderProposal $tenderProposal)
    {
        $tenderProposal->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderProposal deleted successfully',
        ]);
    }
}
