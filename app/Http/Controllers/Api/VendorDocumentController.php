<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use Illuminate\Http\Request;

class VendorDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = VendorDocument::query();
        $query->with(['vendor']);

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

    public function show(VendorDocument $vendorDocument)
    {
        $vendorDocument->load(['vendor']);

        return response()->json([
            'success' => true,
            'data' => $vendorDocument,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'document_type' => 'required|in:trade_license,vat_certificate,tax_certificate,psr,professional_certificate,experience,bank_solvency,manpower_list,machinery_capacity',
            'file_path' => 'required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'verified' => 'boolean'
        ]);

        $vendorDocument = VendorDocument::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'VendorDocument created successfully',
            'data' => $vendorDocument,
        ], 201);
    }

    public function update(Request $request, VendorDocument $vendorDocument)
    {
        $validated = $request->validate([
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'document_type' => 'sometimes|required|in:trade_license,vat_certificate,tax_certificate,psr,professional_certificate,experience,bank_solvency,manpower_list,machinery_capacity',
            'file_path' => 'sometimes|required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'verified' => 'boolean'
        ]);

        $vendorDocument->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'VendorDocument updated successfully',
            'data' => $vendorDocument,
        ]);
    }

    public function destroy(VendorDocument $vendorDocument)
    {
        $vendorDocument->delete();

        return response()->json([
            'success' => true,
            'message' => 'VendorDocument deleted successfully',
        ]);
    }
}
