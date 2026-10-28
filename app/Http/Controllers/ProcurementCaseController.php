<?php

namespace App\Http\Controllers;

use App\Models\ProcurementCase;
use App\Models\ProcurementPolicy;
use App\Models\PurchaseRequisition;
use App\Services\NumberGeneratorService;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;

class ProcurementCaseController extends Controller
{
    public function index()
    {
        // Sub-committee scoping (design item 8): once a case's PR has been
        // transferred to a Sub-Committee, only Admin/Procurement Officer or
        // a member of that Sub-Committee should see it on this list.
        $user = request()->user();
        $cases = ProcurementCase::latest()->get()
            ->filter(fn ($case) => CommitteeScope::userCanActOnCase($user, $case))
            ->values();

        return view('cases.index', ['cases' => $cases]);
    }

    /** Form to open a new case from an approved PR that doesn't have one yet. */

    // public function create(Request $request)
    // {
    //     $selectedPrId = $request->integer('pr_id') ?: null;

    //     $eligiblePrs = PurchaseRequisition::with('category')
    //         ->where(function ($q) use ($selectedPrId) {
    //             $q->where('status', 'approved');
    //             if ($selectedPrId) {
    //                 $q->orWhere('id', $selectedPrId);
    //             }
    //         })
    //         ->whereDoesntHave('procurementCase')
    //         ->orderBy('requisition_date')
    //         ->get();

    //     return view('cases.create', [
    //         'eligiblePrs' => $eligiblePrs,
    //         'selectedPrId' => $selectedPrId,
    //     ]);
    // }

    public function create(Request $request)
    {
        $eligiblePrs = PurchaseRequisition::with('category')
            ->where('status', 'approved')
            ->whereDoesntHave('procurementCase')
            ->orderBy('requisition_date')
            ->get();

        return view('cases.create', [
            'eligiblePrs' => $eligiblePrs,
            'selectedPrId' => $request->integer('pr_id') ?: null,
        ]);
    }

    public function store(Request $request, NumberGeneratorService $numbers)
    {
        $validated = $request->validate([
            'purchase_requisition_id' => 'required|exists:purchase_requisitions,id',
            'title' => 'required|string|max:255',
            'category' => 'required|in:Goods,Works,Services',
            'method' => 'required|in:RFQ,RFP,RFT',
            'is_otm' => 'boolean',
            'amount' => 'required|numeric|min:0',
        ]);

        abort_if(
            ProcurementCase::where('purchase_requisition_id', $validated['purchase_requisition_id'])->exists(),
            422,
            'This PR already has a procurement case.'
        );

        $case = ProcurementCase::create($validated + [
            'ref' => $numbers->nextUnique('case_ref', 'PC-', 'procurement_cases', 'ref', 4),
            'current_step' => 0,
        ]);

        $case->seedSteps();

        return redirect()->route('cases.show', $case)->with('ok', 'Case opened — ' . $case->ref);
    }

    public function show(ProcurementCase $case)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $case),
            403,
            'This case is currently with a different committee.'
        );

        $case->load([
            'steps',
            'meetings',
            'purchaseRequisition.procurementPlan.subCommitteeTransfers' => function ($q) {
                $q->latest('transfer_date')->with(['fromCommittee', 'toCommittee']);
            },
        ]);
        $phases = $case->steps->groupBy('phase');
        return view('cases.show', ['case' => $case, 'phases' => $phases, 'flow' => $this->buildFlow($case)]);
    }

    /**
     * Everything that happens after the committee meeting, gathered in one list for the case page.
     * Read-only: each row is worked out from records that already exist (RFQ, quotations, reports,
     * award...), and its button opens the same page the team already uses for that step.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function buildFlow(ProcurementCase $case): array
    {
        $pr = $case->purchaseRequisition;
        $open = fn (string $slug) => route('process-steps.show', ['slug' => $slug] + ($pr ? ['pr_id' => $pr->id] : []));

        $rfqs = $case->rfqs()->with([
            'tenderSchedules', 'quotations', 'tenderOpenings',
            'eligibilityReports', 'technicalEvaluationReports', 'financialEvaluationReports', 'comparativeStatements',
        ])->orderBy('id')->get();
        $rfq = $rfqs->last();                       // the RFQ currently running (latest)

        $plan = $pr?->procurementPlan;
        $awards = $plan
            ? \App\Models\ContractAward::where('procurement_plan_id', $plan->id)
                ->with(['payOrders', 'contractAgreements.workOrders.deliveryReceipts'])->get()
            : collect();
        $agreements = $awards->flatMap->contractAgreements;
        $workOrders = $agreements->flatMap->workOrders;
        $deliveries = $workOrders->flatMap->deliveryReceipts;

        $latest = fn ($c) => $c->sortByDesc('id')->first();
        $sum = fn (string $rel) => $rfqs->sum(fn ($r) => $r->{$rel}->count());
        $api = fn (string $path) => url('/api/' . ltrim($path, '/'));

        // Plain-language grouping, in the order the work really happens.
        $phases = [
            'invite' => ['Invite quotations', 'Prepare the request, publish it and collect the vendors\' offers.'],
            'open' => ['Open & check eligibility', 'On the opening day: record the bids and check each vendor\'s papers.'],
            'evaluate' => ['Evaluate & compare', 'Give marks for technique and price, then compare everyone and rank them.'],
            'award' => ['Award & contract', 'Tell the winner, collect the pay order and sign the agreement.'],
            'delivery' => ['Delivery', 'Issue the work order and record what was received.'],
            'report' => ['Report', 'One summary of the whole procurement — print it or file it with the papers.'],
        ];

        $rows = [];
        $add = function (string $phase, string $label, string $hint, int $count, string $slug, ?array $docs = null) use (&$rows, $open) {
            $rows[] = ['phase' => $phase, 'label' => $label, 'hint' => $hint, 'count' => $count, 'url' => $open($slug), 'docs' => $docs];
        };

        $add('invite', 'Request for Quotation (RFQ / RFP)', 'The request sent to vendors: items, quantities and terms.', $rfqs->count(), 'rfq', $rfq ? [
            'preview' => $api("rfqs/{$rfq->id}/pdf-preview"), 'word' => $api("rfqs/{$rfq->id}/document"), 'pdf' => $api("rfqs/{$rfq->id}/pdf"),
        ] : null);
        $ts = $rfq ? $latest($rfq->tenderSchedules) : null;
        $add('invite', 'Tender Schedule', 'Dates, submission rules and how marks will be given.', $sum('tenderSchedules'), 'rfq', $ts ? [
            'preview' => $api("rfqs/{$rfq->id}/tender-schedule-preview"), 'word' => $api("rfqs/{$rfq->id}/tender-schedule-document"), 'pdf' => $api("rfqs/{$rfq->id}/tender-schedule-pdf"),
        ] : null);
        $add('invite', 'Quotations received', 'The offers vendors have submitted.', $sum('quotations'), 'quotations-drop');

        $op = $rfq ? $latest($rfq->tenderOpenings) : null;
        $add('open', 'Tender Opening Report', 'Who opened the bids, when, and what each vendor offered.', $sum('tenderOpenings'), 'quotations-opening', $op ? [
            'preview' => $api("tender-openings/{$op->id}/preview"), 'word' => $api("tender-openings/{$op->id}/document"), 'pdf' => $api("tender-openings/{$op->id}/pdf"),
        ] : null);
        $er = $rfq ? $latest($rfq->eligibilityReports) : null;
        $add('open', 'Eligibility Report', 'Document check: trade licence, TIN, BIN, PSR, experience. Eligible or not.', $sum('eligibilityReports'), 'quotations-evaluation', $er ? [
            'preview' => $api("eligibility-reports/{$er->id}/preview"), 'word' => $api("eligibility-reports/{$er->id}/document"),
        ] : null);

        $tr = $rfq ? $latest($rfq->technicalEvaluationReports) : null;
        $add('evaluate', 'Technical Evaluation Report', 'Marks for each vendor\'s technical merit (out of 100).', $sum('technicalEvaluationReports'), 'quotations-evaluation', $tr ? [
            'preview' => $api("technical-evaluation-reports/{$tr->id}/preview"), 'word' => $api("technical-evaluation-reports/{$tr->id}/document"),
        ] : null);
        $fr = $rfq ? $latest($rfq->financialEvaluationReports) : null;
        $add('evaluate', 'Financial Evaluation Report', 'Marks for price: the lowest price gets 100.', $sum('financialEvaluationReports'), 'quotations-evaluation', $fr ? [
            'preview' => $api("financial-evaluation-reports/{$fr->id}/preview"), 'word' => $api("financial-evaluation-reports/{$fr->id}/document"),
        ] : null);
        $cs = $rfq ? $latest($rfq->comparativeStatements) : null;
        $add('evaluate', 'Comparative Statement', 'All marks together (Technical + Financial + Sample Verification if used), ranking and the recommended vendor.', $sum('comparativeStatements'), 'quotations-evaluation', $cs ? [
            'preview' => $api("comparative-statements/{$cs->id}/preview"), 'word' => $api("comparative-statements/{$cs->id}/document"),
        ] : null);

        $add('award', 'Notification of Award (NOA)', 'The letter telling the winning vendor they have been selected.', $awards->count(), 'quotations-evaluation');
        $add('award', 'Pay Order', 'The security deposit paid by the winning vendor.', $awards->sum(fn ($a) => $a->payOrders->count()), 'quotations-evaluation');
        $add('award', 'Contract Agreement', 'The signed contract with the vendor.', $agreements->count(), 'quotations-evaluation');

        $wo = $latest($workOrders);
        $add('delivery', 'Work Order', 'The order to start supplying or working.', $workOrders->count(), 'quotations-evaluation', $wo ? [
            'preview' => $api("work-orders/{$wo->id}/preview"), 'word' => $api("work-orders/{$wo->id}/document"), 'pdf' => $api("work-orders/{$wo->id}/pdf"),
        ] : null);
        $add('delivery', 'Delivery Received (Challan)', 'Record of goods or work received.', $deliveries->count(), 'quotations-evaluation');

        // The Procurement Report is built on demand from everything above, so it can be opened at any time.
        $rows[] = [
            'phase' => 'report', 'label' => 'Procurement Report',
            'hint' => 'Summary of this procurement: items, dates, meetings, bids, evaluation, award, contract and delivery. Always up to date.',
            'count' => 0, 'url' => null, 'report' => true,
            'docs' => [
                'preview' => $api("procurement-cases/{$case->id}/report-preview"),
                'word' => $api("procurement-cases/{$case->id}/report"),
                'pdf' => $api("procurement-cases/{$case->id}/report-pdf"),
            ],
        ];

        // The first row that has nothing yet is "next".
        $next = null;
        foreach ($rows as $i => $r) {
            if ($r['count'] === 0) {
                $next = $i;
                break;
            }
        }
        foreach ($rows as $i => &$r) {
            $r['state'] = $r['count'] > 0 ? 'done' : ($i === $next ? 'next' : 'waiting');
        }
        unset($r);

        return [
            'phases' => $phases,
            'rows' => $rows,
            'rfq_count' => $rfqs->count(),
            'meetings_ready' => $case->meetings->contains(fn ($m) => (bool) $m->rezulation_no),
        ];
    }

    public function completeStep(ProcurementCase $case)
    {
        $step = $case->steps()->where('step_no', $case->current_step + 1)->first();

        abort_if(! $step, 404, 'No pending step found for this case.');
        abort_if($step->isDone(), 422, 'That step is already marked complete.');

        $step->update(['completed_at' => now()]);
        $case->update(['current_step' => $step->step_no]);

        return redirect()->route('cases.show', $case)->with('ok', 'Step marked complete: ' . $step->name);
    }

    /** Procurement plan — milestone dates auto-calculated from PR date per policy. */
    public function plan()
    {
        $prs = PurchaseRequisition::with('items')->where('stage', '>=', 4)->get();
        if ($prs->isEmpty()) {
            $prs = PurchaseRequisition::with('items')->take(2)->get();
        }

        $rows = $prs->map(function ($pr) {
            $d = $pr->pr_date->copy();
            $milestones = [
                ['label' => 'Advertise / publish',  'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_PUBLISH))],
                ['label' => 'Submission closing',   'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_CLOSING))],
                ['label' => 'Opening',              'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_OPENING))],
                ['label' => 'Evaluation',           'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_EVALUATION))],
                ['label' => 'NOA issued',           'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_NOA))],
                ['label' => 'Contract signing',     'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_CONTRACT))],
                ['label' => 'Work order',           'date' => $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_WORK_ORDER))],
                ['label' => 'Delivery',             'date' => $pr->delivery_date ?? $d->copy()->addDays(ProcurementPolicy::get(ProcurementPolicy::OFFSET_DELIVERY))],
            ];
            return ['pr' => $pr, 'milestones' => $milestones, 'nature' => $pr->natureLabel(),
                    'days' => (int) $d->diffInDays($milestones[7]['date'])];
        });

        return view('plan', ['rows' => $rows]);
    }
}