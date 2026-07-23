<?php

namespace App\Http\Controllers;

use App\Models\ProcurementCase;
use App\Models\ProcurementPolicy;
use App\Models\PurchaseRequisition;

class ProcurementCaseController extends Controller
{
    public function index()
    {
        return view('cases.index', ['cases' => ProcurementCase::latest()->get()]);
    }

    public function show(ProcurementCase $case)
    {
        $case->load(['steps', 'meetings']);
        $phases = $case->steps->groupBy('phase');
        return view('cases.show', ['case' => $case, 'phases' => $phases]);
    }

    public function completeStep(ProcurementCase $case)
    {
        $next = $case->steps()->whereNull('completed_at')->orderBy('step_no')->first();
        if ($next) {
            $next->update(['completed_at' => now()]);
            $case->update(['current_step' => $next->step_no]);
        }
        return back()->with('ok', 'Step completed.');
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
