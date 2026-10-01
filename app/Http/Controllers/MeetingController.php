<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\RfqController;
use App\Models\Meeting;
use App\Models\ProcurementCase;
use App\Models\ProcurementCommitteeMember;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * The 1st/2nd meeting is recorded in 3 separate steps, matching the 3
 * separate sidebar/process-step pages (Notice, Attendance, Resolution)
 * instead of one combined form:
 *
 *   1. Notice     — location/date/time/agenda, before the meeting happens.
 *   2. Attendance — who actually attended, once the meeting has been held.
 *   3. Resolution — decisions + tender schedule (1st) / awards (2nd),
 *                   assigns the Rezulation No. and finalizes the minutes.
 *
 * Each step can only be recorded once the previous one is done, so a case
 * only ever shows up on the process-step page for the step it's actually
 * ready for.
 */
class MeetingController extends Controller
{
    /** Step 1 — Notice: form to schedule a new meeting against a case. */
    public function createNotice(ProcurementCase $case, string $type)
    {
        abort_unless(in_array($type, ['first', 'second'], true), 404);

        if ($case->meetings()->where('meeting_type', $type)->exists()) {
            return redirect()->route('cases.show', $case)->with('ok', ucfirst($type) . ' meeting notice already sent for this case.');
        }

        return view('meetings.create-notice', ['case' => $case, 'type' => $type]);
    }

    public function storeNotice(Request $request, ProcurementCase $case, string $type, NumberGeneratorService $numbers)
    {
        abort_unless(in_array($type, ['first', 'second'], true), 404);

        abort_if(
            $case->meetings()->where('meeting_type', $type)->exists(),
            422,
            ucfirst($type) . ' meeting notice already sent for this case.'
        );

        $validator = Validator::make($request->all(), [
            'location' => 'required|string|max:120',
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|string|max:40',
            'agenda' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Redirect explicitly to this same form (not the framework's
            // automatic back(), which can land on the dashboard instead if
            // the "previous URL" isn't tracked reliably on this host).
            return redirect()->route('meetings.notice.create', [$case, $type])
                ->withErrors($validator)->withInput();
        }
        $data = $validator->validated();

        $meeting = Meeting::create([
            'procurement_case_id' => $case->id,
            'meeting_type' => $type,
            'location' => $data['location'],
            'meeting_date' => $data['meeting_date'],
            'meeting_time' => $data['meeting_time'] ?? null,
            'agenda' => $data['agenda'],
            'notice_number' => $numbers->nextDocMemo('Procurement', 'Notice'),
            'notice_date' => now(),
            'recorded_by' => Auth::id(),
        ]);

        // Email the notice to the roster now, before the meeting happens —
        // attendance (who actually showed up) isn't recorded until step 2.
        $sentCount = $this->emailNoticeToRoster($meeting);
        $noticeMsg = $sentCount > 0 ? " Notice is being emailed to {$sentCount} committee member(s)." : '';

        return redirect()->route('cases.show', $case)->with('ok', 'Meeting notice recorded — ' . $meeting->notice_number . '.' . $noticeMsg . ' Attendance can now be recorded once the meeting is held.');
    }

    /** Standalone Step 1 — Notice: no Case required up front; the form lets the user optionally pick one. */
    public function createNoticeStandalone()
    {
        return view('meetings.create-notice', [
            'case' => null,
            'type' => null,
            'openCases' => ProcurementCase::query()
                ->orderByDesc('id')
                ->limit(200)
                ->get(['id', 'ref', 'title']),
        ]);
    }

    public function storeNoticeStandalone(Request $request, NumberGeneratorService $numbers)
    {
        $validator = Validator::make($request->all(), [
            'case_id' => 'nullable|exists:procurement_cases,id',
            'meeting_type' => 'required|in:first,second',
            'location' => 'required|string|max:120',
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable|string|max:40',
            'agenda' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('meetings.notice.create.standalone')
                ->withErrors($validator)->withInput();
        }
        $data = $validator->validated();

        $case = ! empty($data['case_id']) ? ProcurementCase::find($data['case_id']) : null;

        if ($case) {
            abort_if(
                $case->meetings()->where('meeting_type', $data['meeting_type'])->exists(),
                422,
                ucfirst($data['meeting_type']) . ' meeting notice already sent for this case.'
            );
        }

        $meeting = Meeting::create([
            'procurement_case_id' => $case?->id,
            'meeting_type' => $data['meeting_type'],
            'location' => $data['location'],
            'meeting_date' => $data['meeting_date'],
            'meeting_time' => $data['meeting_time'] ?? null,
            'agenda' => $data['agenda'],
            'notice_number' => $numbers->nextDocMemo('Procurement', 'Notice'),
            'notice_date' => now(),
            'recorded_by' => Auth::id(),
        ]);

        $sentCount = $this->emailNoticeToRoster($meeting);
        $noticeMsg = $sentCount > 0 ? " Notice is being emailed to {$sentCount} committee member(s)." : '';

        return redirect()->route('meetings.show', $meeting)
            ->with('ok', 'Meeting notice recorded — ' . $meeting->notice_number . '.' . $noticeMsg . ' Attendance can now be recorded once the meeting is held.');
    }

    /** Step 2 — Attendance: who attended the already-noticed meeting. */
    public function createAttendance(Meeting $meeting)
    {
        abort_if($meeting->attendance_number, 422, 'Attendance has already been recorded for this meeting.');

        $meeting->load('procurementCase');

        return view('meetings.create-attendance', [
            'meeting' => $meeting,
            'roster' => ProcurementCommitteeMember::activeRoster(),
        ]);
    }

    public function storeAttendance(Request $request, Meeting $meeting, NumberGeneratorService $numbers)
    {
        abort_if($meeting->attendance_number, 422, 'Attendance has already been recorded for this meeting.');

        $validator = Validator::make($request->all(), [
            'attendees' => 'required|array|min:1',
            'attendees.*.name' => 'required|string|max:120',
            'attendees.*.designation' => 'required|string|max:120',
            'attendees.*.committee_member_id' => 'nullable|exists:procurement_committee_members,id',
            'attendees.*.email' => 'nullable|email|max:190',
        ]);

        if ($validator->fails()) {
            return redirect()->route('meetings.attendance.create', $meeting)
                ->withErrors($validator)->withInput();
        }
        $data = $validator->validated();

        DB::transaction(function () use ($data, $meeting, $numbers) {
            foreach ($data['attendees'] as $i => $a) {
                $meeting->attendees()->create([
                    'committee_member_id' => $a['committee_member_id'] ?? null,
                    'name' => $a['name'], 'designation' => $a['designation'], 'sort_order' => $i,
                ]);
            }

            $meeting->update(['attendance_number' => $numbers->nextDocMemo('Procurement', 'Attendence')]);
        });

        [$emailedCount, $noEmail] = $this->emailAttendanceConfirmation($meeting, $data['attendees']);
        $emailMsg = ($emailedCount > 0 ? " Confirmation is being emailed to {$emailedCount} attendee(s)." : '')
            . ($noEmail ? ' No email address for: ' . implode(', ', $noEmail) . ' — not emailed.' : '');

        return redirect()->route('cases.show', $meeting->procurementCase)
            ->with('ok', 'Attendance recorded — ' . $meeting->attendance_number . '.' . $emailMsg);
    }

    /**
     * Runs $callback once the HTTP response has already gone to the browser
     * (Laravel "terminating" callbacks), so saving a notice / attendance no
     * longer waits on the SMTP server — the page loads straight away and the
     * emails go out right after. (No queue worker needed.)
     */
    private function afterResponse(callable $callback): void
    {
        app()->terminating($callback);
    }

    /**
     * Emails the attendance confirmation to every attendee that has an address.
     * Address = the Email typed/pre-filled on the attendance form, else the
     * email on their Committee Roster entry. Duplicates are sent once.
     *
     * @return array{0: int, 1: array<int, string>}  [emails queued, names with no email]
     */
    private function emailAttendanceConfirmation(Meeting $meeting, array $attendees): array
    {
        $meeting->load('procurementCase');

        $memberIds = array_values(array_unique(array_filter(
            array_column($attendees, 'committee_member_id')
        )));
        $members = $memberIds
            ? ProcurementCommitteeMember::whereIn('id', $memberIds)->get()->keyBy('id')
            : collect();

        $recipients = [];
        $missing = [];
        foreach ($attendees as $a) {
            $email = trim((string) ($a['email'] ?? ''));
            if ($email === '' && ! empty($a['committee_member_id'])) {
                $email = trim((string) ($members[$a['committee_member_id']]->email ?? ''));
            }
            if ($email === '') {
                $missing[] = $a['name'];
                continue;
            }
            $recipients[strtolower($email)] ??= [$email, $a['name'], $a['designation']];
        }

        if ($recipients) {
            $this->afterResponse(function () use ($meeting, $recipients) {
                foreach ($recipients as [$email, $name, $designation]) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($email)
                            ->send(new \App\Mail\MeetingAttendanceMail($meeting, $name, $designation));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Attendance confirmation email failed', [
                            'meeting_id' => $meeting->id,
                            'email' => $email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        }

        return [count($recipients), $missing];
    }

    /** Step 3 — Resolution: decisions + tender schedule / award, finalizes the minutes. */
    public function createResolution(Meeting $meeting)
    {
        abort_unless($meeting->attendance_number, 422, 'Record attendance before finalizing the resolution.');
        abort_if($meeting->rezulation_no, 422, 'Resolution has already been finalized for this meeting.');

        $meeting->load('procurementCase');

        return view('meetings.create-resolution', [
            'meeting' => $meeting,
            'vendors' => $meeting->meeting_type === 'second' ? Vendor::orderBy('name')->get() : collect(),
        ]);
    }

    public function storeResolution(Request $request, Meeting $meeting, NumberGeneratorService $numbers)
    {
        abort_unless($meeting->attendance_number, 422, 'Record attendance before finalizing the resolution.');
        abort_if($meeting->rezulation_no, 422, 'Resolution has already been finalized for this meeting.');

        $validator = Validator::make($request->all(), [
            'decisions' => 'required|string',
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

        if ($validator->fails()) {
            return redirect()->route('meetings.resolution.create', $meeting)
                ->withErrors($validator)->withInput();
        }
        $data = $validator->validated();

        $case = $meeting->procurementCase;

        $rfqCreated = false;

        DB::transaction(function () use ($data, $meeting, $numbers, &$rfqCreated) {
            $meeting->update([
                'decisions' => $data['decisions'],
                'publish_date' => $data['publish_date'] ?? null,
                'closing_date' => $data['closing_date'] ?? null,
                'opening_date' => $data['opening_date'] ?? null,
                'schedule_override_reason' => $data['schedule_override_reason'] ?? null,
                'rezulation_no' => $numbers->nextRezulation(),
                'held_at' => now(),
            ]);

            foreach ($data['awards'] ?? [] as $a) {
                $meeting->awards()->create([
                    'vendor_id' => $a['vendor_id'] ?? null,
                    'vendor_name' => $a['vendor_name'],
                    'scope_note' => $a['scope_note'],
                    'amount' => $a['amount'] ?? 0,
                ]);
            }

            // 1st Meeting Resolution already carries everything an RFQ
            // needs (the case, the publish/closing window) — so instead
            // of making the officer re-enter it on the RFQ module, create
            // it here automatically. Only once per case; if one already
            // exists (e.g. created manually before this resolution was
            // finalized), leave it alone.
            if ($meeting->meeting_type === 'first') {
                $rfqCreated = $this->autoCreateRfqFromResolution($meeting, $data, $numbers);
            }
        });

        // Mark the related checklist step done: step 4 (Tender Schedule) for
        // the 1st meeting, step 16 (NOA/Work Order regulation) for the 2nd.
        $stepNo = $meeting->meeting_type === 'first' ? 4 : 16;
        $case->steps()->where('step_no', $stepNo)->whereNull('completed_at')->update(['completed_at' => now()]);
        if ($case->current_step < $stepNo) {
            $case->update(['current_step' => $stepNo]);
        }

        $message = 'Meeting resolution finalized — Rezulation No. ' . $meeting->rezulation_no . '.';
        if ($rfqCreated) {
            $message .= ' An RFQ has been auto-created for this case from the resolution.';
        }

        return redirect()->route('meetings.show', $meeting)->with('ok', $message);
    }

    /**
     * Builds the case's RFQ straight from the 1st meeting's own data —
     * publish_date becomes the RFQ's issue_date, closing_date carries
     * over as-is, and type is worked out the same way RfqController does
     * (ESDO Procurement Policy §11 amount thresholds). Skipped entirely
     * if the case already has an RFQ (created manually, or a re-run).
     */
    private function autoCreateRfqFromResolution(Meeting $meeting, array $data, NumberGeneratorService $numbers): bool
    {
        $case = $meeting->procurementCase;
        if (! $case || $case->rfqs()->exists()) {
            return false;
        }

        $issueDate = $data['publish_date'] ?? now()->toDateString();
        $closingDate = $data['closing_date'] ?? null;
        // Rfq requires closing strictly after issue — the resolution form
        // only enforces closing >= publish, so nudge it forward if needed.
        if (! $closingDate || $closingDate <= $issueDate) {
            $closingDate = date('Y-m-d', strtotime($issueDate . ' +15 days'));
        }

        $threshold = $case->category === 'Works'
            ? RfqController::OTM_THRESHOLD_WORKS
            : RfqController::OTM_THRESHOLD_GOODS_SERVICES;
        $type = $case->amount > $threshold ? 'OTM' : 'RFQ';

        Rfq::create([
            'procurement_case_id' => $case->id,
            'rfq_number' => $numbers->nextCommitteeMemo('Purchases Committee'),
            'subject' => $case->title,
            'type' => $type,
            'issue_date' => $issueDate,
            'closing_date' => $closingDate,
            'created_by' => Auth::id(),
        ]);

        return true;
    }

    /**
     * Email the notice to every active Central Procurement roster member with
     * an email on file, PLUS — when the case is currently with a Sub-Committee —
     * every member of that Sub-Committee (using the email stored on their
     * Committee Roster entry; login-user members fall back to their account
     * email). Sent at Step 1, before the meeting happens, since attendance
     * (who actually showed up) isn't known until Step 2. Sent without a
     * queue worker (shared hosting) — failures are logged,
     * not thrown, so a bad/missing SMTP setup never blocks saving the
     * notice itself. The mails go out right AFTER the response (see afterResponse()),
     * so the page doesn't wait on SMTP. Returns how many recipients were queued.
     */
    private function emailNoticeToRoster(Meeting $meeting): int
    {
        $meeting->load('procurementCase');
        $sent = 0;

        $recipients = [];
        foreach (ProcurementCommitteeMember::activeRoster() as $member) {
            if ($member->email) {
                $recipients[strtolower($member->email)] = $member->name;
            }
        }

        $committee = $meeting->procurementCase
            ? \App\Support\CommitteeScope::currentCommitteeForCase($meeting->procurementCase)
            : null;
        if ($committee && $committee->type === 'sub') {
            $subMembers = \App\Models\CommitteeMember::where('committee_id', $committee->id)
                ->with(['user', 'procurementCommitteeMember'])
                ->get();

            foreach ($subMembers as $cm) {
                $email = $cm->procurementCommitteeMember?->email ?: $cm->user?->email;
                if ($email) {
                    $recipients[strtolower($email)] ??= ($cm->member_name ?? $email);
                }
            }
        }

        if ($recipients) {
            $this->afterResponse(function () use ($meeting, $recipients) {
                foreach ($recipients as $email => $name) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($email)
                            ->send(new \App\Mail\MeetingNoticeMail($meeting, $name));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Meeting notice email failed', [
                            'meeting_id' => $meeting->id,
                            'email' => $email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        }

        return count($recipients);
    }

    public function show(Meeting $meeting)
    {
        $meeting->load(['attendees', 'awards.vendor', 'procurementCase', 'recordedBy']);
        return view('meetings.show', ['meeting' => $meeting]);
    }
}