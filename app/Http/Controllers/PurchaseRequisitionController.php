<?php

namespace App\Http\Controllers;

use App\Models\ProcurementCase;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseRequisitionController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'All');
        $q = PurchaseRequisition::with('items')->latest('pr_date');

        if (in_array($filter, ['Goods', 'Works', 'Services'])) {
            $q->where('category', $filter);
        } elseif ($filter === 'Pending') {
            $q->where('stage', '<', 4)->where('rejected', false);
        } elseif ($filter === 'Approved') {
            $q->where('stage', '>=', 4);
        }

        return view('prs.index', ['prs' => $q->get(), 'filter' => $filter]);
    }

    public function create()
    {
        $next = 'PR-' . now()->year . '-' . str_pad((string) (PurchaseRequisition::count() + 41), 3, '0', STR_PAD_LEFT);
        return view('prs.create', ['nextPrNo' => $next]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'nullable|string',
            'project'          => 'required|string',
            'requestor'        => 'required|string',
            'designation'      => 'nullable|string',
            'category'         => 'required|in:Goods,Works,Services',
            'delivery_date'    => 'nullable|date',
            'allocated_budget' => 'nullable|numeric',
            'items'            => 'required|array|min:1',
            'items.*.name'     => 'required|string',
            'items.*.unit'     => 'nullable|string',
            'items.*.qty'      => 'required|numeric|min:0',
            'items.*.rate'     => 'required|numeric|min:0',
            'items.*.ac_code'  => 'nullable|string',
        ]);

        $pr = PurchaseRequisition::create([
            'pr_no'            => 'PR-' . now()->year . '-' . str_pad((string) (PurchaseRequisition::count() + 41), 3, '0', STR_PAD_LEFT),
            'title'            => $data['title'] ?? null ?: $data['items'][0]['name'],
            'project'          => $data['project'],
            'requestor'        => $data['requestor'],
            'designation'      => $data['designation'] ?? null,
            'category'         => $data['category'],
            'pr_date'          => now(),
            'delivery_date'    => $data['delivery_date'] ?? null,
            'allocated_budget' => $data['allocated_budget'] ?? 0,
            'stage'            => 1, // submitted → with Reviewer
        ]);

        foreach ($data['items'] as $item) {
            $pr->items()->create([
                'name'    => $item['name'],
                'unit'    => $item['unit'] ?: 'Pcs',
                'qty'     => $item['qty'],
                'rate'    => $item['rate'],
                'ac_code' => $item['ac_code'] ?? null,
            ]);
        }

        return redirect()->route('prs.show', $pr)->with('ok', 'Requisition submitted for review.');
    }

    public function show(PurchaseRequisition $pr)
    {
        $pr->load('items');
        $canAct = !$pr->rejected && $pr->stage < 4 && Auth::user()->prStage() === (int) $pr->stage;

        return view('prs.show', ['pr' => $pr, 'canAct' => $canAct]);
    }

    public function print(PurchaseRequisition $pr)
    {
        $pr->load('items');
        return view('prs.print', ['pr' => $pr]);
    }

    public function approve(PurchaseRequisition $pr)
    {
        abort_unless(!$pr->rejected && $pr->stage < 4 && Auth::user()->prStage() === (int) $pr->stage, 403);

        $pr->update(['stage' => min($pr->stage + 1, 4), 'rejected' => false]);

        // Fully approved → open a procurement case with the 23-step process
        if ($pr->stage >= 4 && !$pr->procurementCase) {
            $isOtm = $pr->determineIsOtm();
            $pr->update(['is_otm' => $isOtm]);

            $case = ProcurementCase::create([
                'ref'      => sprintf('ESDO/%s/%s-%03d', $isOtm ? 'OTM' : $pr->method(), now()->format('y'), ProcurementCase::count() + 1),
                'purchase_requisition_id' => $pr->id,
                'title'    => $pr->title,
                'category' => $pr->category,
                'method'   => $pr->method(),
                'is_otm'   => $isOtm,
                'amount'   => $pr->total(),
                'current_step' => 2, // plan + PR approved
            ]);
            $case->seedSteps();
            $case->steps()->where('step_no', '<=', 2)->update(['completed_at' => now()]);
        }

        return back()->with('ok', 'Approved.');
    }

    public function reject(PurchaseRequisition $pr)
    {
        abort_unless(!$pr->rejected && $pr->stage < 4 && Auth::user()->prStage() === (int) $pr->stage, 403);

        $pr->update(['rejected' => true]);
        return back()->with('ok', 'Sent back for correction.');
    }
}
