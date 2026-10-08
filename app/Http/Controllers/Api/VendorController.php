<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query();
        $query->with(['documents']);

        // Vendors page opened for one RFQ (listFilterField = rfq_id): only the vendors
        // who submitted a quotation (portal link or manual entry) for THAT RFQ.
        if ($request->filled('rfq_id')) {
            $rfqId = $request->integer('rfq_id');
            $query->whereHas('quotations', fn ($q) => $q->where('rfq_id', $rfqId));
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

    public function show(Vendor $vendor)
    {
        $vendor->load(['documents']);

        return response()->json([
            'success' => true,
            'data' => $vendor,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'trade_license_no' => 'nullable|string|max:255',
            'vat_reg_no' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255'
        ]);

        $vendor = Vendor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully',
            'data' => $vendor,
        ], 201);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'trade_license_no' => 'nullable|string|max:255',
            'vat_reg_no' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255'
        ]);

        $vendor->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully',
            'data' => $vendor,
        ]);
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor deleted successfully',
        ]);
    }
}
