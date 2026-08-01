<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\ProcurementCase;
use App\Models\ProcurementCommitteeMember;
use App\Models\Vendor;
use App\Services\MemoSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{
    /** Form to record a new meeting against a case. */
    public function create(ProcurementCase $case, string $type)
    {
        abort_unless(in_array($type, ['first', 'second'], true), 404);

        // A case normally has at most one of each meeting type.
        if ($case->meetings()->where('meeting_type', $type)->exists()) {
            return redirect()->route('cases.show', $case)->with('ok', ucfirst($type) . ' meeting already recorded for this case.');
        }

        return view('meetings.create', [
            'case' => $case,
            'type' => $type,
            'roster' => ProcurementCommitteeMember::activeRoster(),
            'vendors' => $type === 'second' ? Vendor::orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request, ProcurementCase $case, string $type)
    {
        abort_unless(in_array($type, ['first', 'second'], true), 404);

        $data = $request->validate([
            'location' => 'required|string|max:120',
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|string|max:40',
            'agenda' => 'required|string',
            'decisions' => 'required|string',
            'attendees' => 'required|array|min:1',
            'attendees.*.name' => 'required|string|max:120',
            'attendees.*.designation' => 'required|string|max:120',
            // 1st-meeting tender schedule fields
            'publish_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:publish_date',
            'opening_date' => 'nullable|date|after_or_equal:closing_date',
            'schedule_override_reason' => 'nullable|string|max:255',
            // 2nd-meeting award fields
            'awards' => 'nullable|array',
            'awards.*.vendor_name' => 'required_with:awards|string|max:150',
            'awards.*.vendor_id' => 'nullable|exists:vendors,id',
            'awards.*.scope_note' => 'required_with:awards|string|max:255',
            'awards.*.amount' => 'nullable|numeric|min:0',
        ]);

        $meeting = DB::transaction(function () use ($data, $case, $type) {
            $meeting = Meeting::create([
                'rezulation_no' => MemoSequence::nextRezulation(),
                'procurement_case_id' => $case->id,
                'meeting_type' => $type,
                'location' => $data['location'],
                'meeting_date' => $data['meeting_date'],
                'meeting_time' => $data['meeting_time'] ?? null,
                'agenda' => $data['agenda'],
                'decisions' => $data['decisions'],
                'publish_date' => $data['publish_date'] ?? null,
                'closing_date' => $data['closing_date'] ?? null,
                'opening_date' => $data['opening_date'] ?? null,
                'schedule_override_reason' => $data['schedule_override_reason'] ?? null,
                'recorded_by' => Auth::id(),
            ]);

            foreach ($data['attendees'] as $i => $a) {
                $meeting->attendees()->create([
                    'name' => $a['name'], 'designation' => $a['designation'], 'sort_order' => $i,
                ]);
            }

            foreach ($data['awards'] ?? [] as $a) {
                $meeting->awards()->create([
                    'vendor_id' => $a['vendor_id'] ?? null,
                    'vendor_name' => $a['vendor_name'],
                    'scope_note' => $a['scope_note'],
                    'amount' => $a['amount'] ?? 0,
                ]);
            }

            return $meeting;
        });

        // Mark the related checklist step done: step 4 (Tender Schedule) for
        // the 1st meeting, step 16 (NOA/Work Order regulation) for the 2nd.
        $stepNo = $type === 'first' ? 4 : 16;
        $case->steps()->where('step_no', $stepNo)->whereNull('completed_at')->update(['completed_at' => now()]);
        if ($case->current_step < $stepNo) {
            $case->update(['current_step' => $stepNo]);
        }

        return redirect()->route('meetings.show', $meeting)->with('ok', 'Meeting recorded — Rezulation No. ' . $meeting->rezulation_no);
    }

    public function show(Meeting $meeting)
    {
        $meeting->load(['attendees', 'awards.vendor', 'procurementCase']);
        return view('meetings.show', ['meeting' => $meeting]);
    }
}
