<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvaluationItem;
use App\Models\FinancialEvaluationReport;
use App\Models\TenderSchedule;
use Illuminate\Http\Request;

class FinancialEvaluationItemController extends Controller
{
    /**
     * সর্বনিম্ন দর (Lowest Price) আর tender_schedules.financial_weight
     * ব্যবহার করে এই রিপোর্টের সব ভেন্ডরের Financial Marks
     * (= Lowest Price / এই ভেন্ডরের দর × Weight) নতুন করে হিসাব করে।
     * Weight পাওয়া না গেলে (কোনো Tender Schedule নেই) কিছুই বদলায় না।
     */
    private function recalculateMarks(int $ferId): void
    {
        $report = FinancialEvaluationReport::find($ferId);
        if (! $report) {
            return;
        }

        $weight = TenderSchedule::where('rfq_id', $report->rfq_id)->value('financial_weight');
        if (! $weight) {
            return;
        }

        $items = FinancialEvaluationItem::where('fer_id', $ferId)->get();
        $lowest = $items->where('quoted_amount', '>', 0)->min('quoted_amount');
        if (! $lowest) {
            return;
        }

        foreach ($items as $item) {
            $marks = $item->quoted_amount > 0
                ? round(($lowest / $item->quoted_amount) * $weight, 2)
                : 0;
            $item->updateQuietly(['financial_marks' => $marks]);
        }
    }

    public function index(Request $request)
    {
        $query = FinancialEvaluationItem::query();
        $query->with(['report', 'vendor']);

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

    public function show(FinancialEvaluationItem $financialEvaluationItem)
    {
        $financialEvaluationItem->load(['report', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => $financialEvaluationItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fer_id' => 'required|exists:financial_evaluation_reports,id',
            'vendor_id' => 'required|exists:vendors,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'quoted_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $financialEvaluationItem = FinancialEvaluationItem::create($validated);
        $this->recalculateMarks($financialEvaluationItem->fer_id);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationItem created successfully',
            'data' => $financialEvaluationItem->fresh(),
        ], 201);
    }

    public function update(Request $request, FinancialEvaluationItem $financialEvaluationItem)
    {
        $validated = $request->validate([
            'fer_id' => 'sometimes|required|exists:financial_evaluation_reports,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'quoted_amount' => 'sometimes|required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $financialEvaluationItem->update($validated);
        $this->recalculateMarks($financialEvaluationItem->fer_id);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationItem updated successfully',
            'data' => $financialEvaluationItem->fresh(),
        ]);
    }

    public function destroy(FinancialEvaluationItem $financialEvaluationItem)
    {
        $ferId = $financialEvaluationItem->fer_id;
        $financialEvaluationItem->delete();
        $this->recalculateMarks($ferId);

        return response()->json([
            'success' => true,
            'message' => 'FinancialEvaluationItem deleted successfully',
        ]);
    }
}
