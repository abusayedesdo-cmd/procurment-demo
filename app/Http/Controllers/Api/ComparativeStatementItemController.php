<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComparativeStatementItem;
use Illuminate\Http\Request;

class ComparativeStatementItemController extends Controller
{
    public function index(Request $request)
    {
        $query = ComparativeStatementItem::query();
        $query->with(['statement', 'vendor']);

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

    public function show(ComparativeStatementItem $comparativeStatementItem)
    {
        $comparativeStatementItem->load(['statement', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => $comparativeStatementItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comparative_statement_id' => 'required|exists:comparative_statements,id',
            'vendor_id' => 'required|exists:vendors,id',
            'rank' => 'nullable|integer|min:1',
            'amount' => 'required|numeric|min:0'
        ]);

        $comparativeStatementItem = ComparativeStatementItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatementItem created successfully',
            'data' => $comparativeStatementItem,
        ], 201);
    }

    public function update(Request $request, ComparativeStatementItem $comparativeStatementItem)
    {
        $validated = $request->validate([
            'comparative_statement_id' => 'sometimes|required|exists:comparative_statements,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'rank' => 'nullable|integer|min:1',
            'amount' => 'sometimes|required|numeric|min:0'
        ]);

        $comparativeStatementItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatementItem updated successfully',
            'data' => $comparativeStatementItem,
        ]);
    }

    public function destroy(ComparativeStatementItem $comparativeStatementItem)
    {
        $comparativeStatementItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatementItem deleted successfully',
        ]);
    }
}
