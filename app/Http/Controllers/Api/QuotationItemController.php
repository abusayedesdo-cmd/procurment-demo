<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;

/**
 * A vendor's unit price / amount for one RfqItem, recorded when a
 * Quotation is opened/entered. amount is stored explicitly (not
 * derived) so it can be checked against the paper submission — the
 * tender document itself disqualifies bids with a calculation
 * mismatch, so the recorded amount must reflect what was actually
 * written, not a recomputed figure.
 */
class QuotationItemController extends Controller
{
    public function index(Request $request)
    {
        $query = QuotationItem::query()->with(['rfqItem.unit']);

        if ($request->filled('quotation_id')) {
            $query->where('quotation_id', $request->integer('quotation_id'));
        }

        $items = $query->paginate($request->integer('per_page', 50));

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

    public function show(QuotationItem $quotationItem)
    {
        $quotationItem->load(['rfqItem.unit', 'quotation']);

        return response()->json([
            'success' => true,
            'data' => $quotationItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'rfq_item_id' => 'required|exists:rfq_items,id',
            'unit_price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $quotationItem = QuotationItem::create($validated);

        $this->refreshQuotationTotal($quotationItem->quotation_id);

        return response()->json([
            'success' => true,
            'message' => 'QuotationItem created successfully',
            'data' => $quotationItem,
        ], 201);
    }

    public function update(Request $request, QuotationItem $quotationItem)
    {
        $validated = $request->validate([
            'unit_price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $quotationItem->update($validated);
        $this->refreshQuotationTotal($quotationItem->quotation_id);

        return response()->json([
            'success' => true,
            'message' => 'QuotationItem updated successfully',
            'data' => $quotationItem,
        ]);
    }

    public function destroy(QuotationItem $quotationItem)
    {
        $quotationId = $quotationItem->quotation_id;
        $quotationItem->delete();
        $this->refreshQuotationTotal($quotationId);

        return response()->json([
            'success' => true,
            'message' => 'QuotationItem deleted successfully',
        ]);
    }

    /**
     * Keep Quotation.quoted_amount in sync as the grand total of its
     * line items, so existing evaluation/comparative-statement code
     * that reads quoted_amount keeps working unchanged.
     */
    protected function refreshQuotationTotal(int $quotationId): void
    {
        $total = QuotationItem::where('quotation_id', $quotationId)->sum('amount');
        Quotation::whereKey($quotationId)->update(['quoted_amount' => $total]);
    }
}
