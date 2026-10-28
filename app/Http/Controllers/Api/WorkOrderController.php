<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Services\WorkOrderData;
use App\Support\CommitteeScope;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;

/**
 * Document section C, steps 30-32 —
 * "Create Work Order for Work/Goods/Service"
 * wo_number is auto-generated.
 */
class WorkOrderController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = WorkOrder::query()->with('contractAgreement.contractAward.vendor');

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

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['contractAgreement.contractAward.vendor', 'deliveryReceipts']);

        return response()->json([
            'success' => true,
            'data' => $workOrder,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_agreement_id' => 'required|exists:contract_agreements,id',
            'category' => 'required|in:Work,Goods,Service',
            'wo_date' => 'required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $workOrder = WorkOrder::create($validated + [
            'wo_number' => $this->numberGenerator->nextMemo(),
            'created_by' => $request->user()?->id,
        ]);

        $workOrder->load('contractAgreement');

        return response()->json([
            'success' => true,
            'message' => 'Work order created successfully',
            'data' => $workOrder,
        ], 201);
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'wo_date' => 'sometimes|required|date',
            'file_path' => 'nullable|string|max:255',
        ]);

        $workOrder->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Work order updated successfully',
            'data' => $workOrder,
        ]);
    }

        /** Terms printed on this Work Order: the saved (edited) list if there is one, else the RFQ-generated list. */
    public function terms(WorkOrder $workOrder)
    {
        $this->authorizeCommittee($workOrder);
        $data = WorkOrderData::for($workOrder);

        return response()->json([
            'success' => true,
            'data' => [
                'terms' => $data['terms'],
                'default_terms' => $data['defaultTerms'],
                'is_custom' => is_array($workOrder->terms) && $workOrder->terms !== [],
            ],
        ]);
    }

    public function updateTerms(Request $request, WorkOrder $workOrder)
    {
        $this->authorizeCommittee($workOrder);

        $validated = $request->validate([
            'terms' => 'required|array|min:1|max:50',
            'terms.*' => 'required|string|max:1000',
        ]);

        $clean = array_values(array_filter(array_map('trim', $validated['terms']), fn ($t) => $t !== ''));
        abort_if($clean === [], 422, 'Add at least one term, or reset to the RFQ terms.');

        $workOrder->update(['terms' => $clean]);

        return response()->json(['success' => true, 'message' => 'Terms saved.']);
    }

    /** Throw the edits away: the Work Order goes back to printing the RFQ's terms. */
    public function resetTerms(WorkOrder $workOrder)
    {
        $this->authorizeCommittee($workOrder);
        $workOrder->update(['terms' => null]);

        return response()->json(['success' => true, 'message' => 'Reset to the RFQ terms.']);
    }

    protected function authorizeCommittee(WorkOrder $workOrder): void
    {
        $workOrder->loadMissing('contractAgreement.contractAward');
        abort_unless(
            CommitteeScope::userCanActOnPlan(request()->user(), $workOrder->contractAgreement?->contractAward?->procurement_plan_id),
            403,
            'This case is currently with a different committee.'
        );
    }

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order deleted successfully',
        ]);
    }
}
