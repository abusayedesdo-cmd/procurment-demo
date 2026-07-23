<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoleSourcingRequest;
use Illuminate\Http\Request;

class SoleSourcingRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SoleSourcingRequest::query();
        $query->with(['purchaseRequisition', 'vendor', 'approvedBy']);

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

    public function show(SoleSourcingRequest $soleSourcingRequest)
    {
        $soleSourcingRequest->load(['purchaseRequisition', 'vendor', 'approvedBy']);

        return response()->json([
            'success' => true,
            'data' => $soleSourcingRequest,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requisitions,id',
            'vendor_id' => 'required|exists:vendors,id',
            'justification' => 'nullable|string',
            'approved_by' => 'nullable|exists:users,id',
            'approval_date' => 'nullable|date',
            'file_path' => 'nullable|string|max:255'
        ]);

        $soleSourcingRequest = SoleSourcingRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'SoleSourcingRequest created successfully',
            'data' => $soleSourcingRequest,
        ], 201);
    }

    public function update(Request $request, SoleSourcingRequest $soleSourcingRequest)
    {
        $validated = $request->validate([
            'pr_id' => 'sometimes|required|exists:purchase_requisitions,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'justification' => 'nullable|string',
            'approved_by' => 'nullable|exists:users,id',
            'approval_date' => 'nullable|date',
            'file_path' => 'nullable|string|max:255'
        ]);

        $soleSourcingRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'SoleSourcingRequest updated successfully',
            'data' => $soleSourcingRequest,
        ]);
    }

    public function destroy(SoleSourcingRequest $soleSourcingRequest)
    {
        $soleSourcingRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'SoleSourcingRequest deleted successfully',
        ]);
    }
}
