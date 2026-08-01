<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrItem;
use App\Models\PurchaseRequisition;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Document section A — "Input PR"
 * Handles PR / BOQ+TOR / Design & Drawing window creation, item lines,
 * auto serial numbers, auto total calculation, and remarks.
 * The actual BOQ/TOR/Design & Drawing sub-forms are separate resources
 * (BoqDetailController, TorDetailController, DesignDrawingController)
 * linked by pr_id, created after this PR record exists.
 */
class PurchaseRequisitionController extends Controller
{
    public function __construct(protected NumberGeneratorService $numberGenerator)
    {
    }

    public function index(Request $request)
    {
        $query = PurchaseRequisition::query()->with(['category', 'raisedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('window_type')) {
            $query->where('window_type', $request->string('window_type'));
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

    public function show(PurchaseRequisition $purchaseRequisition)
    {
        // $purchaseRequisition->load([
        //     'category', 'raisedBy', 'items.item', 'items.unit',
        //     'approvals.user', 'boqDetail', 'torDetail', 'designDrawing', 'procurementPlan',
        // ]);
        $purchaseRequisition->load([
            'category', 'raisedBy', 'budgetLine', 'package.plan', 'items.item', 'items.unit',
            'approvals.user', 'boqDetail', 'torDetail', 'designDrawing', 'procurementPlan',
        ]);

        return response()->json([
            'success' => true,
            'data' => $purchaseRequisition,
        ]);
    }

    /**
     * Step-by-step per document section A:
     * window_type -> category -> date -> items (item/unit/qty/rate) ->
     * auto serial + auto total -> remarks -> estimated delivery date.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'window_type' => 'required|in:PR,BOQ,TOR,Design_Drawing',
            'category_id' => 'required|exists:procurement_categories,id',
            'budget_line_id' => 'nullable|exists:budget_lines,id',
            'procurement_plan_package_id' => 'nullable|exists:procurement_plan_packages,id',
            'requisition_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date|after_or_equal:requisition_date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate_bdt' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ]);

        $purchaseRequisition = DB::transaction(function () use ($validated, $request) {
            $total = 0;
            foreach ($validated['items'] as $line) {
                $total += $line['quantity'] * $line['rate_bdt'];
            }

            $pr = PurchaseRequisition::create([
                'pr_number' => $this->numberGenerator->next('pr', 'PR-'),
                'window_type' => $validated['window_type'],
                'category_id' => $validated['category_id'],
                'budget_line_id' => $validated['budget_line_id'] ?? null,
                'procurement_plan_package_id' => $validated['procurement_plan_package_id'] ?? null,
                'requisition_date' => $validated['requisition_date'],
                'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
                'total_estimated_amount' => $total,
                'status' => 'draft',
                'raised_by' => $request->user()->id,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($validated['items'] as $index => $line) {
                PrItem::create([
                    'pr_id' => $pr->id,
                    'serial_no' => $index + 1,
                    'item_id' => $line['item_id'],
                    'unit_id' => $line['unit_id'],
                    'quantity' => $line['quantity'],
                    'rate_bdt' => $line['rate_bdt'],
                    'total_amount' => $line['quantity'] * $line['rate_bdt'],
                    'remarks' => $line['remarks'] ?? null,
                ]);
            }

            return $pr;
        });

        $purchaseRequisition->load(['items.item', 'items.unit', 'category']);

        return response()->json([
            'success' => true,
            'message' => 'Purchase requisition created successfully',
            'data' => $purchaseRequisition,
        ], 201);
    }

    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:procurement_categories,id',
            'budget_line_id' => 'sometimes|nullable|exists:budget_lines,id',
            'requisition_date' => 'sometimes|required|date',
            'estimated_delivery_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        // NB: only draft PRs can be edited — once past reviewer/checker/approver
        // stages the record should be immutable except through the approval flow.
        if ($purchaseRequisition->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft purchase requisitions can be edited.',
            ], 422);
        }

        $purchaseRequisition->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Purchase requisition updated successfully',
            'data' => $purchaseRequisition,
        ]);
    }

    public function destroy(PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft purchase requisitions can be deleted.',
            ], 422);
        }

        $purchaseRequisition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase requisition deleted successfully',
        ]);
    }
}
