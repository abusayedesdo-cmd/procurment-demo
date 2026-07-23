<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::query();
        $query->with(['rfq', 'vendor']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
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

    public function show(Quotation $quotation)
    {
        $quotation->load(['rfq', 'vendor', 'items.rfqItem.unit']);

        return response()->json([
            'success' => true,
            'data' => $quotation,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'vendor_id' => 'required|exists:vendors,id',
            'submitted_at' => 'required|date',
            'quoted_amount' => 'required|numeric|min:0',
            'file_path' => 'nullable|string|max:255',
            'status' => 'nullable|in:received,opened,evaluated,disqualified',
            'representative_name' => 'nullable|string|max:255',
            'representative_contact' => 'nullable|string|max:50',
        ]);

        $quotation = Quotation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quotation created successfully',
            'data' => $quotation,
        ], 201);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'submitted_at' => 'sometimes|required|date',
            'quoted_amount' => 'sometimes|required|numeric|min:0',
            'file_path' => 'nullable|string|max:255',
            'status' => 'nullable|in:received,opened,evaluated,disqualified',
            // Recorded at Tender/RFQ Opening — attendance & eligibility checklist.
            'representative_name' => 'nullable|string|max:255',
            'representative_contact' => 'nullable|string|max:50',
            'attended' => 'sometimes|boolean',
            'trade_license_submitted' => 'sometimes|boolean',
            'tin_submitted' => 'sometimes|boolean',
            'bin_submitted' => 'sometimes|boolean',
            'opening_remarks' => 'nullable|string',
        ]);

        $quotation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quotation updated successfully',
            'data' => $quotation,
        ]);
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quotation deleted successfully',
        ]);
    }
}
