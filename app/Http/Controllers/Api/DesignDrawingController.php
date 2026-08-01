<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignDrawing;
use Illuminate\Http\Request;

class DesignDrawingController extends Controller
{
    public function index(Request $request)
    {
        $query = DesignDrawing::query();
        $query->with(['purchaseRequisition']);

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

    public function show(DesignDrawing $designDrawing)
    {
        $designDrawing->load(['purchaseRequisition']);

        return response()->json([
            'success' => true,
            'data' => $designDrawing,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requisitions,id',
            'file_path' => 'nullable|string|max:255',
            'drawing_no' => 'nullable|string|max:255'
        ]);

        $designDrawing = DesignDrawing::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'DesignDrawing created successfully',
            'data' => $designDrawing,
        ], 201);
    }

    public function update(Request $request, DesignDrawing $designDrawing)
    {
        $validated = $request->validate([
            'pr_id' => 'sometimes|required|exists:purchase_requisitions,id',
            'file_path' => 'nullable|string|max:255',
            'drawing_no' => 'nullable|string|max:255'
        ]);

        $designDrawing->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'DesignDrawing updated successfully',
            'data' => $designDrawing,
        ]);
    }

    public function destroy(DesignDrawing $designDrawing)
    {
        $designDrawing->delete();

        return response()->json([
            'success' => true,
            'message' => 'DesignDrawing deleted successfully',
        ]);
    }
}
