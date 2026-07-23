<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReceipt;
use Illuminate\Http\Request;

class DeliveryReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryReceipt::query();
        $query->with(['workOrder', 'receivedBy']);

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

    public function show(DeliveryReceipt $deliveryReceipt)
    {
        $deliveryReceipt->load(['workOrder', 'receivedBy']);

        return response()->json([
            'success' => true,
            'data' => $deliveryReceipt,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'category' => 'required|in:Work,Goods,Service',
            'delivery_date' => 'required|date',
            'received_by' => 'required|exists:users,id',
            'remarks' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $deliveryReceipt = DeliveryReceipt::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'DeliveryReceipt created successfully',
            'data' => $deliveryReceipt,
        ], 201);
    }

    public function update(Request $request, DeliveryReceipt $deliveryReceipt)
    {
        $validated = $request->validate([
            'work_order_id' => 'sometimes|required|exists:work_orders,id',
            'category' => 'sometimes|required|in:Work,Goods,Service',
            'delivery_date' => 'sometimes|required|date',
            'received_by' => 'sometimes|required|exists:users,id',
            'remarks' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $deliveryReceipt->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'DeliveryReceipt updated successfully',
            'data' => $deliveryReceipt,
        ]);
    }

    public function destroy(DeliveryReceipt $deliveryReceipt)
    {
        $deliveryReceipt->delete();

        return response()->json([
            'success' => true,
            'message' => 'DeliveryReceipt deleted successfully',
        ]);
    }
}
