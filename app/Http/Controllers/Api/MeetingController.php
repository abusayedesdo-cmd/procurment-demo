<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::query();
        $query->with(['procurementPlan.purchaseRequisition', 'createdBy', 'attendances', 'minutes']);
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

    public function show(Meeting $meeting)
    {
        $meeting->load(['procurementPlan.purchaseRequisition', 'createdBy', 'attendances.user', 'minutes']);

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'required|exists:procurement_plans,id',
            'meeting_sequence' => 'required|in:1st,2nd',
            'meeting_date' => 'required|date',
            'notice_number' => 'nullable|string|max:255',
            'notice_file' => 'nullable|string|max:255',
            'created_by' => 'required|exists:users,id',
        ]);

        $meeting = Meeting::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meeting created successfully',
            'data' => $meeting,
        ], 201);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'meeting_sequence' => 'sometimes|required|in:1st,2nd',
            'meeting_date' => 'sometimes|required|date',
            'notice_number' => 'nullable|string|max:255',
            'notice_file' => 'nullable|string|max:255',
        ]);

        $meeting->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meeting updated successfully',
            'data' => $meeting,
        ]);
    }

    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meeting deleted successfully',
        ]);
    }
}
