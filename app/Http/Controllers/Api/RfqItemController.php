<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfqItem;
use Illuminate\Http\Request;

/**
 * Line items requested under an RFQ/OTM (Annex II "Price Schedule for
 * Goods, Machine and Related Services" / RFQ item table). One row per
 * SL number; `category` groups items under a sub-heading like
 * "Bicycle and Van" or "Cloth" when the tender covers multiple
 * categories in one document.
 */
class RfqItemController extends Controller
{
    public function index(Request $request)
    {
        $query = RfqItem::query()->with(['unit']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        $items = $query->orderBy('rfq_id')->orderBy('serial_no')
            ->paginate($request->integer('per_page', 50));

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

    public function show(RfqItem $rfqItem)
    {
        $rfqItem->load(['unit', 'rfq']);

        return response()->json([
            'success' => true,
            'data' => $rfqItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'category' => 'nullable|string|max:255',
            'serial_no' => 'required|integer|min:1',
            'description' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'unit_id' => 'nullable|exists:units,id',
            'delivery_address' => 'nullable|string',
        ]);

        $rfqItem = RfqItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'RfqItem created successfully',
            'data' => $rfqItem,
        ], 201);
    }

    public function update(Request $request, RfqItem $rfqItem)
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:255',
            'serial_no' => 'sometimes|required|integer|min:1',
            'description' => 'sometimes|required|string',
            'quantity' => 'sometimes|required|numeric|min:0',
            'unit_id' => 'nullable|exists:units,id',
            'delivery_address' => 'nullable|string',
        ]);

        $rfqItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'RfqItem updated successfully',
            'data' => $rfqItem,
        ]);
    }

    public function destroy(RfqItem $rfqItem)
    {
        $rfqItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'RfqItem deleted successfully',
        ]);
    }
}
