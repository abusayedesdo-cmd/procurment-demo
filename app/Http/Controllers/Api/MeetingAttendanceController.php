<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingAttendance;
use App\Models\ProcurementCommitteeMember;
use Illuminate\Http\Request;

class MeetingAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MeetingAttendance::query()->with(['meeting', 'committeeMember']);

        if ($request->filled('meeting_id')) {
            $query->where('meeting_id', $request->integer('meeting_id'));
        }

        $items = $query->orderBy('sort_order')->paginate($request->integer('per_page', 20));

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
        $meetingAttendance->load(['meeting', 'committeeMember']);

        return response()->json([
            'success' => true,
            'data' => $meetingAttendance,
        ]);
    }

    /**
     * Bulk-seed the attendance sheet for a meeting from the active
     * committee roster (procurement_committee_members), so the printed
     * form has a row per member ready to sign.
     */
    public function seedFromRoster(Request $request)
    {
        $validated = $request->validate(['meeting_id' => 'required|exists:meetings,id']);

        $roster = ProcurementCommitteeMember::activeRoster();

        $rows = $roster->map(function (ProcurementCommitteeMember $member, $i) use ($validated) {
            return MeetingAttendance::firstOrCreate(
                ['meeting_id' => $validated['meeting_id'], 'committee_member_id' => $member->id],
                ['name' => $member->name, 'designation' => $member->designation, 'sort_order' => $i]
            );
        });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
            'committee_member_id' => 'nullable|exists:procurement_committee_members,id',
            'name' => 'required|string|max:150',
            'designation' => 'required|string|max:150',
            'present' => 'boolean',
            'signature_file' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $meetingAttendance = MeetingAttendance::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $meetingAttendance,
        ], 201);
    }

    public function update(Request $request, MeetingAttendance $meetingAttendance)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'designation' => 'sometimes|required|string|max:150',
            'present' => 'boolean',
            'signature_file' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $meetingAttendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $meetingAttendance,
        ]);
    }

    public function destroy(MeetingAttendance $meetingAttendance)
    {
        $meetingAttendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully',
        ]);
    }
}
