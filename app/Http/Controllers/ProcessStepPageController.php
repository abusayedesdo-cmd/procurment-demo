<?php

namespace App\Http\Controllers;

class ProcessStepPageController extends Controller
{
    /**
     * Step -> Subject -> module mapping, derived from
     * "7. Process Action Windows Mapping.docx". Each module slug/title pair
     * links to the existing generic /modules/{slug} page; 'route' overrides
     * that for steps whose real UI lives elsewhere (the Case-based meeting
     * flow).
     */
    public const STEPS = [
        'pr-receive' => [
            'step_no' => '1st',
            'subject' => 'PR Receive',
            'modules' => [],
            'is_pr_picker' => true,
        ],
        'procurement-plan' => [
            'step_no' => '2nd',
            'subject' => 'Procurement Plan',
            'modules' => [
                ['slug' => 'procurement-plans', 'title' => 'Procurement Plan (auto-generated from approved PR)'],
                ['slug' => 'procurement-plans', 'title' => 'Procurement Plan History (All Records)', 'no_context' => true],
            ],
        ],
        'sub-committee' => [
            'step_no' => '3rd',
            'subject' => 'Sub-Committee',
            'modules' => [
                ['slug' => 'sub-committee-transfers', 'title' => 'Sub-Committee Transfer'],
                ['slug' => 'sub-committee-transfers', 'title' => 'Sub-Committee Transfer History (All Records)', 'no_context' => true],
            ],
        ],
        'meeting-notice' => [
            'step_no' => '4th',
            'subject' => '1st Meeting Notice',
            'modules' => [
                ['route' => 'cases.index', 'title' => 'Open a Case \u2192 1st/2nd Meeting Notice'],
            ],
        ],
        'meeting-attendance' => [
            'step_no' => '5th',
            'subject' => '1st Meeting Attendance',
            'modules' => [
                ['route' => 'cases.index', 'title' => 'Open a Case \u2192 Meeting Attendance'],
            ],
        ],
        'meeting-resolution' => [
            'step_no' => '6th',
            'subject' => '1st Meeting Resolution',
            'modules' => [
                ['route' => 'cases.index', 'title' => 'Open a Case \u2192 Meeting Resolution'],
            ],
        ],
        'rfq' => [
            'step_no' => '7th',
            'subject' => 'Request for Quotation (RFQ)',
            'modules' => [
                ['slug' => 'rfqs', 'title' => 'RFQ / OTM'],
                ['slug' => 'rfqs', 'title' => 'RFQ History (All Records)', 'no_context' => true],
                ['slug' => 'tender-schedules', 'title' => 'Tender Schedule (Goods/Works)'],
                ['slug' => 'tender-schedules', 'title' => 'Tender Schedule History (All Records)', 'no_context' => true],
                ['slug' => 'rfq-terms-conditions', 'title' => 'RFQ Terms & Conditions (Manage List)', 'no_context' => true],
            ],
        ],
        'rfp-rfi' => [
            'step_no' => '8th',
            'subject' => 'RFP/RFI/Hiring Vendor/Consultant',
            'modules' => [
                ['slug' => 'rfqs', 'title' => 'RFP / RFI / Hiring Vendor-Consultant'],
                ['slug' => 'rfqs', 'title' => 'RFP History (All Records)', 'no_context' => true],
                ['slug' => 'tender-proposals', 'title' => 'Tender Proposal (Professional Service)'],
                ['slug' => 'tender-proposals', 'title' => 'Tender Proposal History (All Records)', 'no_context' => true],
            ],
        ],
        'tender-otm' => [
            'step_no' => '9th',
            'subject' => 'Tender Schedule/OTM/Press Tender/STD',
            'modules' => [
                ['slug' => 'tender-advertisements', 'title' => 'Tender Advertisement'],
                ['slug' => 'tender-advertisements', 'title' => 'Tender Advertisement History (All Records)', 'no_context' => true],
            ],
        ],
        'quotations-drop' => [
            'step_no' => '10th',
            'subject' => 'Quotations Drop by Vendor',
            'modules' => [
                ['slug' => 'quotations', 'title' => 'Quotations Received'],
                ['slug' => 'vendors', 'title' => 'Vendors'],
            ],
        ],
        'quotations-opening' => [
            'step_no' => '11th',
            'subject' => 'Quotations Receiving/Opening Report',
            'modules' => [
                ['slug' => 'tender-openings', 'title' => 'Tender Opening Report'],
                ['slug' => 'opening-quotation-review', 'title' => 'Quotation Review — Forward for Evaluation / Reject'],
            ],
        ],
        'quotations-evaluation' => [
            'step_no' => '12th',
            'subject' => 'Quotations Evaluation',
            'modules' => [
                ['slug' => 'eligibility-reports', 'title' => 'Eligibility Report (ER)'],
                ['slug' => 'eligibility-report-items', 'title' => 'Eligibility Report — Vendor Result'],
                ['slug' => 'technical-evaluation-reports', 'title' => 'Technical Evaluation Report (TER)'],
                ['slug' => 'technical-evaluation-items', 'title' => 'Technical Evaluation — Vendor Score'],
                ['slug' => 'financial-evaluation-reports', 'title' => 'Financial Evaluation Report (FER)'],
                ['slug' => 'financial-evaluation-items', 'title' => 'Financial Evaluation — Vendor Amount'],
                ['slug' => 'comparative-statements', 'title' => 'Comparative Statement (CS)'],
                ['slug' => 'comparative-statement-items', 'title' => 'Comparative Statement — Vendor Ranking'],
                ['slug' => 'contract-awards', 'title' => 'Notification of Contract Award (NOA)'],
                ['slug' => 'contract-awards', 'title' => 'NOA History (All Records)', 'no_context' => true],
                ['slug' => 'pay-orders', 'title' => 'Pay Order'],
                ['slug' => 'contract-agreements', 'title' => 'Contract Agreement'],
                ['slug' => 'work-orders', 'title' => 'Work Order'],
                ['slug' => 'delivery-receipts', 'title' => 'Delivery Received'],
            ],
        ],
    ];

    /**
     * Which of a module's own form fields holds "the record we're already
     * working on" — used together with the resolved PR/Plan/Case/RFQ chain
     * below to prefill+lock that field via the same
     * `?new=1&field_x=y&context_label=...` mechanism the generic module
     * engine (public/js/resource-ui.js) already understands.
     */
    public const PREFILL_FIELD_BY_SLUG = [
        'procurement-plans' => 'pr_id',
        'sub-committee-transfers' => 'procurement_plan_id',
        'rfqs' => 'procurement_case_id',
        'tender-schedules' => 'rfq_id',
        'tender-proposals' => 'rfq_id',
        'tender-advertisements' => 'rfq_id',
        'quotations' => 'rfq_id',
        'tender-openings' => 'rfq_id',
        'opening-quotation-review' => 'rfq_id',
        'eligibility-reports' => 'rfq_id',
        'technical-evaluation-reports' => 'rfq_id',
        'financial-evaluation-reports' => 'rfq_id',
        'comparative-statements' => 'rfq_id',
        'contract-awards' => 'procurement_plan_id',
    ];

    /**
     * The 1st meeting's Notice/Attendance/Resolution are 3 separate steps
     * sharing one Meeting record. Each of these pages should only list
     * cases that are actually ready for that specific step — not every
     * case, or a case would sit on all 3 pages at once with no way to
     * tell which action is actually next.
     */
    private const MEETING_STEP_SLUGS = ['meeting-notice', 'meeting-attendance', 'meeting-resolution'];

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::STEPS), 404);

        $step = self::STEPS[$slug];
        $cases = null;


        $activePrId = request()->query('pr_id');
        if (request()->query('clear_pr')) {
            session()->forget('active_pr_id');
            $activePrId = null;
        } elseif ($activePrId) {
            session(['active_pr_id' => (int) $activePrId]);
        } else {
            $activePrId = session('active_pr_id');
        }

        $activePr = $activePrId ? \App\Models\PurchaseRequisition::find($activePrId) : null;
        if (! $activePr) {
            // Stale/invalid session value — don't keep carrying a dead reference.
            session()->forget('active_pr_id');
        }

        $planId = null;
        $caseId = null;
        $rfqId = null;
        $missingPlanForPr = null;

        if ($activePr) {
            $planId = \App\Models\ProcurementPlan::where('pr_id', $activePr->id)->latest('id')->value('id');
            $caseId = \App\Models\ProcurementCase::where('purchase_requisition_id', $activePr->id)->latest('id')->value('id');
            $rfqId = $caseId ? \App\Models\Rfq::where('procurement_case_id', $caseId)->latest('id')->value('id') : null;

            // Sub-Committee step specifically needs a Plan to transfer —
            // if the active PR doesn't have one yet, point the officer at
            // the "B. Procurement Plan" module instead of an empty dropdown.
            if ($slug === 'sub-committee' && ! $planId) {
                $missingPlanForPr = $activePr;
            }
        }

        // Sub-Committee Transfer auto-fill: "From" is whichever committee
        // currently holds the plan (the to_committee of its most recent
        // transfer), or the Main Committee if it's never been transferred.
        // "To" is a toggle: Main -> this project's own Sub-Committee, or
        // that Sub-Committee -> back to Main. Anywhere else, leave "To"
        // for manual pick.
        $fromCommitteeId = null;
        $toCommitteeId = null;
        $isSubCommitteeHolder = false;

        $mainCommitteeId = \App\Models\PurchaseCommittee::where('type', 'main')->value('id');
        $projectSubCommitteeId = ($activePr && $activePr->project_id)
            ? \App\Models\PurchaseCommittee::where('type', 'sub')
                ->where('project_id', $activePr->project_id)
                ->value('id')
            : null;

        if ($planId) {
            $lastTransfer = \App\Models\SubCommitteeTransfer::where('procurement_plan_id', $planId)
                ->orderByDesc('transfer_date')
                ->orderByDesc('id')
                ->first();

            $fromCommitteeId = $lastTransfer ? $lastTransfer->to_committee_id : $mainCommitteeId;

            if ($fromCommitteeId === $mainCommitteeId) {
                $toCommitteeId = $projectSubCommitteeId;
            } elseif ($fromCommitteeId === $projectSubCommitteeId) {
                $toCommitteeId = $mainCommitteeId;
            }

            $isSubCommitteeHolder = $fromCommitteeId && $fromCommitteeId !== $mainCommitteeId;

        }
        

        $missingSubCommitteeForProject = null;
        if ($slug === 'sub-committee' && $planId && $activePr && $activePr->project_id
            && $fromCommitteeId === $mainCommitteeId && ! $projectSubCommitteeId) {
            $missingSubCommitteeForProject = [
                'project_id' => $activePr->project_id,
                'suggested_name' => trim(($activePr->project_name ?: 'Project') . ' Sub-Committee'),
            ];
        }


        $prReceiveList = null;
        if (! empty($step['is_pr_picker'])) {
            $user = request()->user();
            $prReceiveList = \App\Models\PurchaseRequisition::where('status', 'approved')
                ->with([
                    'procurementCase',
                    'procurementPlan.subCommitteeTransfers' => fn ($q) => $q
                        ->orderByDesc('transfer_date')->orderByDesc('id')->with('toCommittee'),
                ])
                ->latest('id')->get(['id', 'pr_number', 'project_name', 'total_estimated_amount'])
                ->filter(fn ($pr) => \App\Support\CommitteeScope::prVisibleToUser($user, $pr))
                ->values();
        }
        $usesCasesFlow = collect($step['modules'])->contains(fn ($m) => ($m['route'] ?? null) === 'cases.index');
        if ($usesCasesFlow && in_array($slug, self::MEETING_STEP_SLUGS, true)) {
            $cases = $this->casesReadyForMeetingStep($slug);
        } elseif ($usesCasesFlow) {
            $cases = \App\Models\ProcurementCase::latest()->get();
        }

        // "শুধু সেই পিআর নিয়ে" — with an active PR that already has its own
        // Case, narrow the meeting-step case picker down to just that one.
        if ($cases !== null && $caseId) {
            $cases = $cases->filter(fn ($c) => $c->id === $caseId)->values();
        }
        if ($activePr && in_array($slug, self::MEETING_STEP_SLUGS, true) && $cases !== null && $cases->count() === 1 && ! request()->boolean('skip_redirect')) {
            $case = $cases->first();
            $firstMeeting = $case->relationLoaded('meetings') ? $case->meetings->first() : null;

            $url = match ($slug) {
                'meeting-notice' => route('meetings.notice.create', [$case, 'first']),
                'meeting-attendance' => $firstMeeting ? route('meetings.attendance.create', $firstMeeting) : route('cases.show', $case),
                'meeting-resolution' => $firstMeeting ? route('meetings.resolution.create', $firstMeeting) : route('cases.show', $case),
                default => route('cases.show', $case),
            };

            return redirect($url);
        }

        return view('process-steps.show', [
            'slug' => $slug,
            'step' => $step,
            'cases' => $cases,
            'planId' => $planId,
            'caseId' => $caseId,
            'rfqId' => $rfqId,
            'activePr' => $activePr,
            'missingPlanForPr' => $missingPlanForPr,
            'prReceiveList' => $prReceiveList,
            'prefillFieldBySlug' => self::PREFILL_FIELD_BY_SLUG,
            'fromCommitteeId' => $fromCommitteeId,
            'toCommitteeId' => $toCommitteeId,
            'missingSubCommitteeForProject' => $missingSubCommitteeForProject,
            'mainCommitteeId' => $mainCommitteeId,
            'isSubCommitteeHolder' => $isSubCommitteeHolder,
        ]);
    }

    private function casesReadyForMeetingStep(string $slug)
    {
        $query = \App\Models\ProcurementCase::query();

        return match ($slug) {
            // Ready for Notice: the 1st meeting hasn't been scheduled yet.
            'meeting-notice' => $query->whereDoesntHave('meetings', fn ($q) => $q->where('meeting_type', 'first'))
                ->latest()->get(),
            // Ready for Attendance: notice sent, attendance not recorded yet.
            'meeting-attendance' => $query->whereHas('meetings', fn ($q) => $q
                ->where('meeting_type', 'first')->whereNull('attendance_number'))
                ->with(['meetings' => fn ($q) => $q->where('meeting_type', 'first')])
                ->latest()->get(),
            // Ready for Resolution: attendance recorded, not yet finalized.
            'meeting-resolution' => $query->whereHas('meetings', fn ($q) => $q
                ->where('meeting_type', 'first')->whereNotNull('attendance_number')->whereNull('rezulation_no'))
                ->with(['meetings' => fn ($q) => $q->where('meeting_type', 'first')])
                ->latest()->get(),
            default => $query->latest()->get(),
        };
    }
}