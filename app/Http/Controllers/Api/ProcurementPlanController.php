<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementPlan;
use App\Models\ProcurementPolicy;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;

/**
 * Document section B — "Create Procurement Plan from PR"
 * Every field here is either copied straight from the approved PR, or
 * auto-calculated from PR date + the day-offsets stored in
 * `procurement_policies` (see App\Models\ProcurementPolicy).
 */
class ProcurementPlanController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcurementPlan::query()->with(['purchaseRequisition.category']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

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

    public function show(ProcurementPlan $procurementPlan)
    {
        $procurementPlan->load([
            'purchaseRequisition.category', 'purchaseRequisition.items.item',
            'meetings', 'rfqs', 'contractAwards',
        ]);

        return response()->json([
            'success' => true,
            'data' => $procurementPlan,
        ]);
    }

    /**
     * Auto-create a Procurement Plan from an approved PR.
     * Body: { "pr_id": 123 }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_id' => 'required|exists:purchase_requisitions,id',
        ]);

        $pr = PurchaseRequisition::with('category')->findOrFail($validated['pr_id']);

        if ($pr->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Procurement Plan can only be created from an approved PR.',
            ], 422);
        }

        if ($pr->procurementPlan()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A Procurement Plan already exists for this PR.',
            ], 422);
        }

        $thresholdKey = $this->thresholdKeyForCategory($pr->category->name ?? '');
        $threshold = ProcurementPolicy::get($thresholdKey, 0);
        $nature = $pr->total_estimated_amount >= $threshold ? 'OTM' : 'RFQ';

        $prDate = $pr->requisition_date;
        $advertisementDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_PUBLISH));
        $closingDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_CLOSING));
        $openingDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_OPENING));
        $evaluationDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_EVALUATION));
        $noaDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_NOA));
        $contractDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_CONTRACT));
        $workOrderDate = $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_WORK_ORDER));
        $deliveryDate = $pr->estimated_delivery_date
            ?? $prDate->copy()->addDays((int) ProcurementPolicy::get(ProcurementPolicy::OFFSET_DELIVERY));

        $plan = ProcurementPlan::create([
            'pr_id' => $pr->id,
            'received_pr_date' => $prDate,
            'nature' => $nature,
            'estimated_amount' => $pr->total_estimated_amount,
            'status' => 'planned',
            'est_advertisement_date' => $advertisementDate,
            'est_closing_date' => $closingDate,
            'est_opening_date' => $openingDate,
            'est_evaluation_date' => $evaluationDate,
            'est_noa_date' => $noaDate,
            'est_contract_signing_date' => $contractDate,
            'est_work_order_date' => $workOrderDate,
            'est_delivery_date' => $deliveryDate,
            'est_completion_days' => $prDate->diffInDays($deliveryDate),
        ]);

        $plan->load('purchaseRequisition.category');

        return response()->json([
            'success' => true,
            'message' => "Procurement plan created ({$nature})",
            'data' => $plan,
        ], 201);
    }

    public function update(Request $request, ProcurementPlan $procurementPlan)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:planned,ongoing,completed,cancelled',
        ]);

        $procurementPlan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Procurement plan updated successfully',
            'data' => $procurementPlan,
        ]);
    }

    protected function thresholdKeyForCategory(string $categoryName): string
    {
        $name = strtolower($categoryName);

        return match (true) {
            str_contains($name, 'work') => ProcurementPolicy::THRESHOLD_WORKS,
            str_contains($name, 'service') => ProcurementPolicy::THRESHOLD_SERVICES,
            default => ProcurementPolicy::THRESHOLD_GOODS,
        };
    }
}
