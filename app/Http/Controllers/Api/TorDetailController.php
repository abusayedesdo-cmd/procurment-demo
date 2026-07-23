<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TorDetail;
use Illuminate\Http\Request;

class TorDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = TorDetail::query();
        $query->with(['purchaseRequisition']);

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

    public function show(TorDetail $torDetail)
    {
        $torDetail->load(['purchaseRequisition']);

        return response()->json([
            'success' => true,
            'data' => $torDetail,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requisitions,id',
            'file_path' => 'nullable|string|max:255',
            'scope_of_work' => 'nullable|string'
        ]);

        $torDetail = TorDetail::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TorDetail created successfully',
            'data' => $torDetail,
        ], 201);
    }

    public function update(Request $request, TorDetail $torDetail)
    {
        $validated = $request->validate([
            'pr_id' => 'sometimes|required|exists:purchase_requisitions,id',
            'file_path' => 'nullable|string|max:255',
            'scope_of_work' => 'nullable|string'
        ]);

        $torDetail->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TorDetail updated successfully',
            'data' => $torDetail,
        ]);
    }

    public function destroy(TorDetail $torDetail)
    {
        $torDetail->delete();

        return response()->json([
            'success' => true,
            'message' => 'TorDetail deleted successfully',
        ]);
    }
}
