<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementPlan;
use App\Models\SubCommitteeTransfer;
use Illuminate\Http\Request;

class SubCommitteeTransferController extends Controller
{
    // ESDO Procurement Policy §9 "Formation of Sub-Committees": a
    // sub-committee is responsible for purchases up to Tk. 500,000 (Five
    // Lac) only. Above that, the central procurement committee must
    // handle it directly — a case above this amount cannot be transferred
    // to a sub-committee.
    public const SUB_COMMITTEE_MAX_AMOUNT = 500000;

    public function index(Request $request)
    {
        $query = SubCommitteeTransfer::query();
        $query->with(['procurementPlan', 'fromCommittee', 'toCommittee']);

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

    public function show(SubCommitteeTransfer $subCommitteeTransfer)
    {
        $subCommitteeTransfer->load(['procurementPlan', 'fromCommittee', 'toCommittee']);

        return response()->json([
            'success' => true,
            'data' => $subCommitteeTransfer,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'required|exists:procurement_plans,id',
            'from_committee_id' => 'required|exists:purchase_committees,id',
            'to_committee_id' => 'required|exists:purchase_committees,id',
            'transfer_note' => 'nullable|string',
            'transfer_date' => 'required|date'
        ]);

        $this->assertWithinSubCommitteeLimit($validated['to_committee_id'], $validated['procurement_plan_id']);

        $subCommitteeTransfer = SubCommitteeTransfer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'SubCommitteeTransfer created successfully',
            'data' => $subCommitteeTransfer,
        ], 201);
    }

    public function update(Request $request, SubCommitteeTransfer $subCommitteeTransfer)
    {
        $validated = $request->validate([
            'procurement_plan_id' => 'sometimes|required|exists:procurement_plans,id',
            'from_committee_id' => 'sometimes|required|exists:purchase_committees,id',
            'to_committee_id' => 'sometimes|required|exists:purchase_committees,id',
            'transfer_note' => 'nullable|string',
            'transfer_date' => 'sometimes|required|date'
        ]);

        $this->assertWithinSubCommitteeLimit(
            $validated['to_committee_id'] ?? $subCommitteeTransfer->to_committee_id,
            $validated['procurement_plan_id'] ?? $subCommitteeTransfer->procurement_plan_id
        );

        $subCommitteeTransfer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'SubCommitteeTransfer updated successfully',
            'data' => $subCommitteeTransfer,
        ]);
    }

    public function destroy(SubCommitteeTransfer $subCommitteeTransfer)
    {
        $subCommitteeTransfer->delete();

        return response()->json([
            'success' => true,
            'message' => 'SubCommitteeTransfer deleted successfully',
        ]);
    }

    /**
     * Policy §9: only applies when the destination is a 'sub' committee —
     * transferring back to a 'main' committee has no ceiling. Aborts with
     * a 422 if the plan's estimated_amount exceeds the limit.
     */
    private function assertWithinSubCommitteeLimit(int $toCommitteeId, int $procurementPlanId): void
    {
        $toCommittee = \App\Models\PurchaseCommittee::find($toCommitteeId);
        if (! $toCommittee || $toCommittee->type !== 'sub') {
            return;
        }

        $amount = (float) (ProcurementPlan::find($procurementPlanId)?->estimated_amount ?? 0);

        abort_if($amount > self::SUB_COMMITTEE_MAX_AMOUNT, 422,
            'A sub-committee can only handle purchases up to Tk. '
            . number_format(self::SUB_COMMITTEE_MAX_AMOUNT)
            . ' (Five Lac) per ESDO Procurement Policy §9. This plan\'s estimated amount is Tk. '
            . number_format($amount) . ' — route it through the central procurement committee instead.'
        );
    }
}