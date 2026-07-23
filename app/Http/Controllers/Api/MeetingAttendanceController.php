<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingAttendance;
use Illuminate\Http\Request;

class MeetingAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MeetingAttendance::query();
        $query->with(['meeting', 'user']);

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

    public function show(MeetingAttendance $meetingAttendance)
    {
        $meetingAttendance->load(['meeting', 'user']);

        return response()->json([
            'success' => true,
            'data' => $meetingAttendance,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
            'user_id' => 'required|exists:users,id',
            'present' => 'boolean',
            'signature_file' => 'nullable|string|max:255'
        ]);

        $meetingAttendance = MeetingAttendance::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'MeetingAttendance created successfully',
            'data' => $meetingAttendance,
        ], 201);
    }

    public function update(Request $request, MeetingAttendance $meetingAttendance)
    {
        $validated = $request->validate([
            'meeting_id' => 'sometimes|required|exists:meetings,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'present' => 'boolean',
            'signature_file' => 'nullable|string|max:255'
        ]);

        $meetingAttendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'MeetingAttendance updated successfully',
            'data' => $meetingAttendance,
        ]);
    }

    public function destroy(MeetingAttendance $meetingAttendance)
    {
        $meetingAttendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'MeetingAttendance deleted successfully',
        ]);
    }
}
