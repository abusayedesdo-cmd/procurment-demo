<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MeetingMinute;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;

/**
 * Document section C, steps 5 & 22 —
 * "Create 1st/2nd Meeting Minutes/Rezulation ... Minutes/Rezulation
 * Number will be auto create"
 */
class MeetingMinuteController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = MeetingMinute::query()->with('meeting');

        if ($request->filled('meeting_id')) {
            $query->where('meeting_id', $request->integer('meeting_id'));
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

    public function show(MeetingMinute $meetingMinute)
    {
        $meetingMinute->load('meeting');

        return response()->json([
            'success' => true,
            'data' => $meetingMinute,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
            'resolution_text' => 'nullable|string',
            'file_path' => 'nullable|string|max:255',
        ]);

        $minute = MeetingMinute::create($validated + [
            'minutes_number' => $this->numberGenerator->nextRezulation(),
        ]);

        $minute->load('meeting');

        return response()->json([
            'success' => true,
            'message' => 'Meeting minutes recorded successfully',
            'data' => $minute,
        ], 201);
    }

    public function update(Request $request, MeetingMinute $meetingMinute)
    {
        $validated = $request->validate([
            'resolution_text' => 'nullable|string',
            'file_path' => 'nullable|string|max:255',
        ]);

        $meetingMinute->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Meeting minutes updated successfully',
            'data' => $meetingMinute,
        ]);
    }

    public function destroy(MeetingMinute $meetingMinute)
    {
        $meetingMinute->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meeting minutes deleted successfully',
        ]);
    }
}
