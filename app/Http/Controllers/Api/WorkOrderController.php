<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;

/**
 * Document section C, steps 30-32 —
 * "Create Work Order for Work/Goods/Service"
 * wo_number is auto-generated.
 */
class WorkOrderController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = WorkOrder::query()->with('contractAgreement.contractAward.vendor');

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

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['contractAgreement.contractAward.vendor', 'deliveryReceipts']);

        return response()->json([
            'success' => true,
            'data' => $workOrder,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_agreement_id' => 'required|exists:contract_agreements,id',
            'category' => 'required|in:Work,Goods,Service',
            'wo_date' => 'required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $workOrder = WorkOrder::create($validated + [
            'wo_number' => $this->numberGenerator->nextMemo(),
        ]);

        $workOrder->load('contractAgreement');

        return response()->json([
            'success' => true,
            'message' => 'Work order created successfully',
            'data' => $workOrder,
        ], 201);
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'wo_date' => 'sometimes|required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $workOrder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Work order updated successfully',
            'data' => $workOrder,
        ]);
    }

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order deleted successfully',
        ]);
    }
}
