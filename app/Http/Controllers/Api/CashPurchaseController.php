<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashPurchase;
use Illuminate\Http\Request;

class CashPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = CashPurchase::query()->with(['purchaseRequisition', 'committee','vendor']);
        if ($request->filled('pr_id')) {
            $query->where('pr_id', $request->query('pr_id'));
        }
        return response()->json(['success' => true, 'data' => $query->latest('id')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'nullable|exists:purchase_requisitions,id',
            'committee_id' => 'nullable|exists:purchase_committees,id',
            'vendor_id' => 'required|exists:vendors,id',
            'item_description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'receipt_file' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        $validated['purchased_by'] = $request->user()->id;
        $purchase = CashPurchase::create($validated);

        return response()->json(['success' => true, 'data' => $purchase], 201);
    }

    public function show(CashPurchase $cashPurchase)
    {
        return response()->json(['success' => true, 'data' => $cashPurchase->load(['purchaseRequisition', 'committee'])]);
    }

    public function update(Request $request, CashPurchase $cashPurchase)
    {
        $validated = $request->validate([
            'pr_id' => 'nullable|exists:purchase_requisitions,id',
            'committee_id' => 'nullable|exists:purchase_committees,id',
            'vendor_id' => 'required|exists:vendors,id',
            'item_description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'receipt_file' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);
        $cashPurchase->update($validated);
        return response()->json(['success' => true, 'data' => $cashPurchase]);
    }

    public function destroy(CashPurchase $cashPurchase)
    {
        $cashPurchase->delete();
        return response()->json(['success' => true]);
    }
}