<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderSchedule;
use Illuminate\Http\Request;

class TenderScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderSchedule::query();
        $query->with(['rfq']);

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

    public function show(TenderSchedule $tenderSchedule)
    {
        $tenderSchedule->load(['rfq']);

        return response()->json([
            'success' => true,
            'data' => $tenderSchedule,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'category' => 'required|in:Goods,Works',
            'schedule_details' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $tenderSchedule = TenderSchedule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderSchedule created successfully',
            'data' => $tenderSchedule,
        ], 201);
    }

    public function update(Request $request, TenderSchedule $tenderSchedule)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'category' => 'sometimes|required|in:Goods,Works',
            'schedule_details' => 'nullable|string',
            'file_path' => 'nullable|string|max:255'
        ]);

        $tenderSchedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderSchedule updated successfully',
            'data' => $tenderSchedule,
        ]);
    }

    public function destroy(TenderSchedule $tenderSchedule)
    {
        $tenderSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderSchedule deleted successfully',
        ]);
    }
}
