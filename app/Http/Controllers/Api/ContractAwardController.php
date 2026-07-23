<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContractAward;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;

/**
 * Document section C, steps 23-25 —
 * "Create Notification of Contract Award for Work/Goods/Service"
 * noa_number is auto-generated.
 */
class ContractAwardController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = ContractAward::query()->with(['procurementPlan', 'vendor']);

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

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

    public function show(ContractAward $contractAward)
    {
        $contractAward->load(['procurementPlan', 'vendor', 'payOrders', 'contractAgreements']);

        return response()->json([
            'success' => true,
            'data' => $contractAward,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'required|exists:procurement_plans,id',
            'category' => 'required|in:Work,Goods,Service',
            'vendor_id' => 'required|exists:vendors,id',
            'noa_date' => 'required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $award = ContractAward::create($validated + [
            'noa_number' => $this->numberGenerator->nextMemo(),
        ]);

        $award->load(['procurementPlan', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Notification of Contract Award created successfully',
            'data' => $award,
        ], 201);
    }

    public function update(Request $request, ContractAward $contractAward)
    {
        $validated = $request->validate([
            'noa_date' => 'sometimes|required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $contractAward->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contract award updated successfully',
            'data' => $contractAward,
        ]);
    }

    public function destroy(ContractAward $contractAward)
    {
        $contractAward->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract award deleted successfully',
        ]);
    }
}
