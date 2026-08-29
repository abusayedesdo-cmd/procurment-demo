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
        $query->with(['procurementCase.purchaseRequisition', 'recordedBy', 'attendees', 'awards']);

        if ($request->filled('procurement_case_id')) {
            $query->where('procurement_case_id', $request->integer('procurement_case_id'));
        }

        if ($request->filled('meeting_type')) {
            $query->where('meeting_type', $request->string('meeting_type'));
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

    public function show(Meeting $meeting)
    {
        $meeting->load(['procurementCase.purchaseRequisition', 'recordedBy', 'attendees.committeeMember', 'awards.vendor']);

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_case_id' => 'required|exists:procurement_cases,id',
            'meeting_type' => 'required|in:first,second',
            'location' => 'nullable|string|max:120',
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|string|max:40',
            'agenda' => 'nullable|string',
        ]);

        abort_if(
            Meeting::where('procurement_case_id', $validated['procurement_case_id'])
                ->where('meeting_type', $validated['meeting_type'])->exists(),
            422,
            ucfirst($validated['meeting_type']) . ' meeting already recorded for this case.'
        );

        $meeting = Meeting::create($validated + [
            'recorded_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meeting created successfully',
            'data' => $meeting,
        ], 201);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'location' => 'nullable|string|max:120',
            'meeting_date' => 'sometimes|required|date',
            'meeting_time' => 'nullable|string|max:40',
            'agenda' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:publish_date',
            'opening_date' => 'nullable|date|after_or_equal:closing_date',
            'schedule_override_reason' => 'nullable|string|max:255',
            'decisions' => 'nullable|string',
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
