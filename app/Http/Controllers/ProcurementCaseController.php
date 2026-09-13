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
        return view('cases.show', ['case' => $case, 'phases' => $phases]);
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