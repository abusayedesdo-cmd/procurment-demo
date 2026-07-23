<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContractAgreement;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;

/**
 * Document section C, steps 27-29 —
 * "Create Contract Agreement for Work/Goods/Service"
 * agreement_number is auto-generated.
 */
class ContractAgreementController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = ContractAgreement::query()->with('contractAward.vendor');

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

    public function show(ContractAgreement $contractAgreement)
    {
        $contractAgreement->load(['contractAward.vendor', 'workOrders']);

        return response()->json([
            'success' => true,
            'data' => $contractAgreement,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_award_id' => 'required|exists:contract_awards,id',
            'category' => 'required|in:Work,Goods,Service',
            'agreement_date' => 'required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $agreement = ContractAgreement::create($validated + [
            'agreement_number' => $this->numberGenerator->nextMemo(),
        ]);

        $agreement->load('contractAward.vendor');

        return response()->json([
            'success' => true,
            'message' => 'Contract agreement created successfully',
            'data' => $agreement,
        ], 201);
    }

    public function update(Request $request, ContractAgreement $contractAgreement)
    {
        $validated = $request->validate([
            'agreement_date' => 'sometimes|required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $contractAgreement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contract agreement updated successfully',
            'data' => $contractAgreement,
        ]);
    }

    public function destroy(ContractAgreement $contractAgreement)
    {
        $contractAgreement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contract agreement deleted successfully',
        ]);
    }
}
