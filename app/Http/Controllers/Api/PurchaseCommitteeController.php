<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseCommittee;
use Illuminate\Http\Request;

class PurchaseCommitteeController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseCommittee::query();
        $query->with(['parentCommittee', 'members']);

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

    public function show(PurchaseCommittee $purchaseCommittee)
    {
        $purchaseCommittee->load(['parentCommittee', 'members']);

        return response()->json([
            'success' => true,
            'data' => $purchaseCommittee,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main,sub',
            'parent_committee_id' => 'nullable|exists:purchase_committees,id'
        ]);

        $purchaseCommittee = PurchaseCommittee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'PurchaseCommittee created successfully',
            'data' => $purchaseCommittee,
        ], 201);
    }

    public function update(Request $request, PurchaseCommittee $purchaseCommittee)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:main,sub',
            'parent_committee_id' => 'nullable|exists:purchase_committees,id'
        ]);

        $purchaseCommittee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'PurchaseCommittee updated successfully',
            'data' => $purchaseCommittee,
        ]);
    }

    public function destroy(PurchaseCommittee $purchaseCommittee)
    {
        $purchaseCommittee->delete();

        return response()->json([
            'success' => true,
            'message' => 'PurchaseCommittee deleted successfully',
        ]);
    }
}
