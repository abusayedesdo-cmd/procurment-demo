<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayOrder;
use Illuminate\Http\Request;

class PayOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PayOrder::query();
        $query->with(['contractAward']);

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

    public function show(PayOrder $payOrder)
    {
        $payOrder->load(['contractAward']);

        return response()->json([
            'success' => true,
            'data' => $payOrder,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_award_id' => 'required|exists:contract_awards,id',
            'awarded_amount' => 'required|numeric|min:0',
            'pay_order_amount' => 'required|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'received_date' => 'nullable|date',
            'calculation_details' => 'nullable|string'
        ]);

        $payOrder = PayOrder::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'PayOrder created successfully',
            'data' => $payOrder,
        ], 201);
    }

    public function update(Request $request, PayOrder $payOrder)
    {
        $validated = $request->validate([
            'contract_award_id' => 'sometimes|required|exists:contract_awards,id',
            'awarded_amount' => 'sometimes|required|numeric|min:0',
            'pay_order_amount' => 'sometimes|required|numeric|min:0',
            'received_amount' => 'nullable|numeric|min:0',
            'received_date' => 'nullable|date',
            'calculation_details' => 'nullable|string'
        ]);

        $payOrder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'PayOrder updated successfully',
            'data' => $payOrder,
        ]);
    }

    public function destroy(PayOrder $payOrder)
    {
        $payOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'PayOrder deleted successfully',
        ]);
    }
}
