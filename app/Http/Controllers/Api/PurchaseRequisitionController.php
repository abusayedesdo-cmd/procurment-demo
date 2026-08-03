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
            'category', 'raisedBy', 'budgetLine', 'package.plan', 'items.item.chartOfAccount', 'items.unit',
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
            'project_name' => 'nullable|string|max:255',
            'budget_line_id' => 'nullable|exists:budget_lines,id',
            'procurement_plan_package_id' => 'nullable|exists:procurement_plan_packages,id',
            'requisition_date' => 'required|date',
            'estimated_delivery_date' => 'nullable|date|after_or_equal:requisition_date',
            'delivery_location' => 'required|string|max:255',
            'estimated_delivery_time' => 'nullable|string|max:100',
            'requestor_name' => 'nullable|string|max:255',
            'requestor_designation' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.specification' => 'nullable|string',
            'items.*.ac_code' => 'nullable|string|max:255',
            'items.*.is_fixed_asset' => 'nullable|boolean',
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
                'project_name' => $validated['project_name'] ?? null,
                'budget_line_id' => $validated['budget_line_id'] ?? null,
                'procurement_plan_package_id' => $validated['procurement_plan_package_id'] ?? null,
                'requisition_date' => $validated['requisition_date'],
                'estimated_delivery_date' => $validated['estimated_delivery_date'] ?? null,
                'delivery_location' => $validated['delivery_location'],
                'estimated_delivery_time' => $validated['estimated_delivery_time'] ?? null,
                'total_estimated_amount' => $total,
                'status' => 'draft',
                'raised_by' => $request->user()->id,
                'requestor_name' => $validated['requestor_name'] ?? $request->user()->name,
                'requestor_designation' => $validated['requestor_designation'] ?? $request->user()->designation,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($validated['items'] as $index => $line) {
                PrItem::create([
                    'pr_id' => $pr->id,
                    'serial_no' => $index + 1,
                    'item_id' => $line['item_id'],
                    'specification' => $line['specification'] ?? null,
                    'ac_code' => $line['ac_code'] ?? null,
                    'is_fixed_asset' => $line['is_fixed_asset'] ?? false,
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

    /**
     * Attachment upload (photo / drawing / BOQ / ToR etc, as on the paper
     * form). Separate endpoint since the main store() call is JSON, and
     * only the PR's own requester (or admin) may attach a file, and only
     * while it's still a draft.
     */
    public function uploadAttachment(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->raised_by !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this section.',
            ], 403);
        }

        if ($purchaseRequisition->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft purchase requisitions can be edited.',
            ], 422);
        }

        $request->validate([
            'attachment' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
        ]);

        $file = $request->file('attachment');
        $destination = public_path('uploads/pr-attachments');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $filename = $purchaseRequisition->pr_number.'-'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($destination, $filename);

        $purchaseRequisition->update([
            'attachment_path' => 'uploads/pr-attachments/'.$filename,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attachment uploaded successfully',
            'data' => $purchaseRequisition,
        ]);
    }

    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:procurement_categories,id',
            'project_name' => 'nullable|string|max:255',
            'budget_line_id' => 'sometimes|nullable|exists:budget_lines,id',
            'requisition_date' => 'sometimes|required|date',
            'estimated_delivery_date' => 'nullable|date',
            'delivery_location' => 'sometimes|required|string|max:255',
            'estimated_delivery_time' => 'nullable|string|max:100',
            'requestor_name' => 'nullable|string|max:255',
            'requestor_designation' => 'nullable|string|max:255',
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
