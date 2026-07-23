<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::query();
        $query->with(['category', 'items']);

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

    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['category', 'items']);

        return response()->json([
            'success' => true,
            'data' => $chartOfAccount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:procurement_categories,id',
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255'
        ]);

        $chartOfAccount = ChartOfAccount::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'ChartOfAccount created successfully',
            'data' => $chartOfAccount,
        ], 201);
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:procurement_categories,id',
            'code' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255'
        ]);

        $chartOfAccount->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ChartOfAccount updated successfully',
            'data' => $chartOfAccount,
        ]);
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'ChartOfAccount deleted successfully',
        ]);
    }
}
