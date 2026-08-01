<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementCategory;
use Illuminate\Http\Request;

class ProcurementCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcurementCategory::query();
        $query->with(['chartOfAccounts']);

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

    public function show(ProcurementCategory $procurementCategory)
    {
        $procurementCategory->load(['chartOfAccounts']);

        return response()->json([
            'success' => true,
            'data' => $procurementCategory,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $procurementCategory = ProcurementCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'ProcurementCategory created successfully',
            'data' => $procurementCategory,
        ], 201);
    }

    public function update(Request $request, ProcurementCategory $procurementCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255'
        ]);

        $procurementCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ProcurementCategory updated successfully',
            'data' => $procurementCategory,
        ]);
    }

    public function destroy(ProcurementCategory $procurementCategory)
    {
        $procurementCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'ProcurementCategory deleted successfully',
        ]);
    }
}
