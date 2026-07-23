<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FrameworkAgreement;
use Illuminate\Http\Request;

class FrameworkAgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = FrameworkAgreement::query();
        $query->with(['vendor', 'category']);

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

    public function show(FrameworkAgreement $frameworkAgreement)
    {
        $frameworkAgreement->load(['vendor', 'category']);

        return response()->json([
            'success' => true,
            'data' => $frameworkAgreement,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'category_id' => 'required|exists:procurement_categories,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'terms' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $frameworkAgreement = FrameworkAgreement::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'FrameworkAgreement created successfully',
            'data' => $frameworkAgreement,
        ], 201);
    }

    public function update(Request $request, FrameworkAgreement $frameworkAgreement)
    {
        $validated = $request->validate([
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'category_id' => 'sometimes|required|exists:procurement_categories,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'terms' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $frameworkAgreement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'FrameworkAgreement updated successfully',
            'data' => $frameworkAgreement,
        ]);
    }

    public function destroy(FrameworkAgreement $frameworkAgreement)
    {
        $frameworkAgreement->delete();

        return response()->json([
            'success' => true,
            'message' => 'FrameworkAgreement deleted successfully',
        ]);
    }
}
