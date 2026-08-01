<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoqDetail;
use Illuminate\Http\Request;

class BoqDetailController extends Controller
{
    public function index(Request $request)
    {
        $query = BoqDetail::query();
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

    public function show(BoqDetail $boqDetail)
    {
        $boqDetail->load(['purchaseRequisition']);

        return response()->json([
            'success' => true,
            'data' => $boqDetail,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requisitions,id',
            'file_path' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        $boqDetail = BoqDetail::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'BoqDetail created successfully',
            'data' => $boqDetail,
        ], 201);
    }

    public function update(Request $request, BoqDetail $boqDetail)
    {
        $validated = $request->validate([
            'pr_id' => 'sometimes|required|exists:purchase_requisitions,id',
            'file_path' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        $boqDetail->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'BoqDetail updated successfully',
            'data' => $boqDetail,
        ]);
    }

    public function destroy(BoqDetail $boqDetail)
    {
        $boqDetail->delete();

        return response()->json([
            'success' => true,
            'message' => 'BoqDetail deleted successfully',
        ]);
    }
}
