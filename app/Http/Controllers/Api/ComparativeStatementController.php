<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComparativeStatement;
use Illuminate\Http\Request;

class ComparativeStatementController extends Controller
{
    public function index(Request $request)
    {
        $query = ComparativeStatement::query();
        $query->with(['rfq', 'preparedBy', 'lowestEvaluatedVendor', 'items']);

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

    public function show(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->load(['rfq', 'preparedBy', 'lowestEvaluatedVendor', 'items']);

        return response()->json([
            'success' => true,
            'data' => $comparativeStatement,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'prepared_by' => 'required|exists:users,id',
            'lowest_evaluated_vendor_id' => 'nullable|exists:vendors,id',
            'file_path' => 'nullable|string|max:255'
        ]);

        $comparativeStatement = ComparativeStatement::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatement created successfully',
            'data' => $comparativeStatement,
        ], 201);
    }

    public function update(Request $request, ComparativeStatement $comparativeStatement)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'prepared_by' => 'sometimes|required|exists:users,id',
            'lowest_evaluated_vendor_id' => 'nullable|exists:vendors,id',
            'file_path' => 'nullable|string|max:255'
        ]);

        $comparativeStatement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatement updated successfully',
            'data' => $comparativeStatement,
        ]);
    }

    public function destroy(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->delete();

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatement deleted successfully',
        ]);
    }
}
