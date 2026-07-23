<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMeeting;
use App\Models\CommitteeMember;
use App\Models\ProcurementCase;
use Illuminate\Http\Request;

class CommitteeMeetingController extends Controller
{
    public function index()
    {
        $meetings = CommitteeMeeting::withCount('declarations')->latest('meeting_date')->get();

        // Cases whose next pending step needs a committee sitting they haven't had yet.
        $awaiting = ProcurementCase::where('current_step', '<', 23)->get()
            ->filter(fn ($case) => $case->pendingAgendaType() !== null
                && ! $case->hasResolvedAgenda($case->pendingAgendaType()));

        return view('committee.index', [
            'meetings'     => $meetings,
            'suggested'    => CommitteeMeeting::suggestNextDate(),
            'awaiting'     => $awaiting,
            'memberCount'  => CommitteeMember::where('active', true)->count(),
        ]);
    }

    public function create(Request $request)
    {
        $awaiting = ProcurementCase::where('current_step', '<', 23)->get()
            ->filter(fn ($case) => $case->pendingAgendaType() !== null
                && ! $case->hasResolvedAgenda($case->pendingAgendaType()));

        return view('committee.create', [
            'suggested'    => CommitteeMeeting::suggestNextDate(),
            'awaiting'     => $awaiting,
            'preselectId'  => (int) $request->query('case', 0),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'meeting_date' => 'required|date',
            'cases'        => 'nullable|array',
            'cases.*'      => 'exists:procurement_cases,id',
        ]);

        $meeting = CommitteeMeeting::create([
            'meeting_no'   => CommitteeMeeting::nextMeetingNo(),
            'meeting_date' => $data['meeting_date'],
            'status'       => 'scheduled',
        ]);

        // Pre-create a blank declaration row per active member so the sign-off list is ready.
        foreach (CommitteeMember::where('active', true)->get() as $member) {
            $meeting->declarations()->create(['committee_member_id' => $member->id]);
        }

        foreach ($data['cases'] ?? [] as $caseId) {
            $case = ProcurementCase::find($caseId);
            $agenda = $case?->pendingAgendaType();
            if ($agenda) {
                $meeting->cases()->attach($caseId, ['agenda_type' => $agenda]);
            }
        }

        return redirect()->route('committee.show', $meeting)->with('ok', 'Committee meeting scheduled.');
    }

    public function show(CommitteeMeeting $committee)
    {
        $committee->load(['cases', 'declarations.committeeMember']);

        $awaiting = ProcurementCase::where('current_step', '<', 23)->get()
            ->filter(fn ($case) => $case->pendingAgendaType() !== null
                && ! $case->hasResolvedAgenda($case->pendingAgendaType())
                && ! $committee->cases->contains($case->id));

        return view('committee.show', ['meeting' => $committee, 'awaiting' => $awaiting]);
    }

    public function addCase(Request $request, CommitteeMeeting $committee)
    {
        $data = $request->validate(['case_id' => 'required|exists:procurement_cases,id']);
        $case = ProcurementCase::findOrFail($data['case_id']);
        $agenda = $case->pendingAgendaType();

        abort_unless($agenda, 422, 'This case has no pending committee agenda item.');

        $committee->cases()->syncWithoutDetaching([$case->id => ['agenda_type' => $agenda]]);

        return back()->with('ok', 'Case added to the agenda.');
    }

    public function declare(Request $request, CommitteeMeeting $committee)
    {
        $data = $request->validate([
            'committee_member_id' => 'required|exists:committee_members,id',
            'has_conflict'        => 'required|boolean',
            'notes'               => 'nullable|string|max:255',
        ]);

        $committee->declarations()->updateOrCreate(
            ['committee_member_id' => $data['committee_member_id']],
            ['has_conflict' => $data['has_conflict'], 'notes' => $data['notes'] ?? null, 'declared_at' => now()]
        );

        return back()->with('ok', 'Declaration recorded.');
    }

    public function resolveCase(CommitteeMeeting $committee, ProcurementCase $case)
    {
        abort_unless($committee->isHeld(), 422, 'Mark the meeting as held before resolving agenda items.');

        $pivot = $committee->cases()->find($case->id)?->pivot;
        abort_unless($pivot, 404);

        $committee->cases()->updateExistingPivot($case->id, [
            'resolved'    => true,
            'resolved_at' => now(),
        ]);

        foreach (CommitteeMeeting::STEPS_BY_AGENDA[$pivot->agenda_type] as $stepNo) {
            $case->steps()->where('step_no', $stepNo)->whereNull('completed_at')
                ->update(['completed_at' => now()]);
        }
        $case->update(['current_step' => max($case->current_step, max(CommitteeMeeting::STEPS_BY_AGENDA[$pivot->agenda_type]))]);

        return back()->with('ok', 'Case resolved for this agenda item.');
    }

    public function saveMinutes(Request $request, CommitteeMeeting $committee)
    {
        $data = $request->validate(['minutes' => 'nullable|string']);
        $committee->update(['minutes' => $data['minutes'] ?? null]);

        return back()->with('ok', 'Minutes saved.');
    }

    public function hold(CommitteeMeeting $committee)
    {
        abort_if($committee->isHeld(), 422, 'Meeting already held.');
        abort_unless($committee->minutes, 422, 'Add meeting minutes before marking it held.');
        abort_unless($committee->allDeclared(), 422, 'All committee members must submit a Conflict of Interest declaration first.');

        $committee->update(['status' => 'held', 'held_at' => now()]);

        return back()->with('ok', 'Meeting marked as held. Agenda items can now be resolved.');
    }
}
