<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrItem;
use Illuminate\Http\Request;

class PrItemController extends Controller
{
    public function index(Request $request)
    {
        $query = PrItem::query();
        $query->with(['purchaseRequisition', 'item', 'unit']);

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

    public function show(PrItem $prItem)
    {
        $prItem->load(['purchaseRequisition', 'item', 'unit']);

        return response()->json([
            'success' => true,
            'data' => $prItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requisitions,id',
            'serial_no' => 'required|integer|min:1',
            'item_id' => 'required|exists:items,id',
            'specification' => 'nullable|string',
            'ac_code' => 'nullable|string|max:255',
            'is_fixed_asset' => 'nullable|boolean',
            'unit_id' => 'required|exists:units,id',
            'quantity' => 'required|numeric|min:0',
            'rate_bdt' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $prItem = PrItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'PrItem created successfully',
            'data' => $prItem,
        ], 201);
    }

    public function update(Request $request, PrItem $prItem)
    {
        $validated = $request->validate([
            'pr_id' => 'sometimes|required|exists:purchase_requisitions,id',
            'serial_no' => 'sometimes|required|integer|min:1',
            'item_id' => 'sometimes|required|exists:items,id',
            'specification' => 'nullable|string',
            'ac_code' => 'nullable|string|max:255',
            'is_fixed_asset' => 'nullable|boolean',
            'unit_id' => 'sometimes|required|exists:units,id',
            'quantity' => 'sometimes|required|numeric|min:0',
            'rate_bdt' => 'sometimes|required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $prItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'PrItem updated successfully',
            'data' => $prItem,
        ]);
    }

    public function destroy(PrItem $prItem)
    {
        $prItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'PrItem deleted successfully',
        ]);
    }
}
