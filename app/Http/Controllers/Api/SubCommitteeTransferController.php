<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubCommitteeTransfer;
use Illuminate\Http\Request;

class SubCommitteeTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = SubCommitteeTransfer::query();
        $query->with(['procurementPlan', 'fromCommittee', 'toCommittee']);

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

    public function show(SubCommitteeTransfer $subCommitteeTransfer)
    {
        $subCommitteeTransfer->load(['procurementPlan', 'fromCommittee', 'toCommittee']);

        return response()->json([
            'success' => true,
            'data' => $subCommitteeTransfer,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'required|exists:procurement_plans,id',
            'from_committee_id' => 'required|exists:purchase_committees,id',
            'to_committee_id' => 'required|exists:purchase_committees,id',
            'transfer_note' => 'nullable|string',
            'transfer_date' => 'required|date'
        ]);

        $subCommitteeTransfer = SubCommitteeTransfer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'SubCommitteeTransfer created successfully',
            'data' => $subCommitteeTransfer,
        ], 201);
    }

    public function update(Request $request, SubCommitteeTransfer $subCommitteeTransfer)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'sometimes|required|exists:procurement_plans,id',
            'from_committee_id' => 'sometimes|required|exists:purchase_committees,id',
            'to_committee_id' => 'sometimes|required|exists:purchase_committees,id',
            'transfer_note' => 'nullable|string',
            'transfer_date' => 'sometimes|required|date'
        ]);

        $subCommitteeTransfer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'SubCommitteeTransfer updated successfully',
            'data' => $subCommitteeTransfer,
        ]);
    }

    public function destroy(SubCommitteeTransfer $subCommitteeTransfer)
    {
        $subCommitteeTransfer->delete();

        return response()->json([
            'success' => true,
            'message' => 'SubCommitteeTransfer deleted successfully',
        ]);
    }
}
