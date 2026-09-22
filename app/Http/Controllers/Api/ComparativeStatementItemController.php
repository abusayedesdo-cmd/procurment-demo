<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComparativeStatement;
use App\Models\ComparativeStatementItem;
use App\Models\FinancialEvaluationItem;
use App\Models\TechnicalEvaluationItem;
use Illuminate\Http\Request;

class ComparativeStatementItemController extends Controller
{
    public function index(Request $request)
    {
        $query = ComparativeStatementItem::query();
        $query->with(['statement', 'vendor']);

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

    public function show(ComparativeStatementItem $comparativeStatementItem)
    {
        $comparativeStatementItem->load(['statement', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => $comparativeStatementItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'comparative_statement_id' => 'required|exists:comparative_statements,id',
            'vendor_id' => 'required|exists:vendors,id',
            'financial_evaluation_item_id' => 'nullable|exists:financial_evaluation_items,id',
            'technical_evaluation_item_id' => 'nullable|exists:technical_evaluation_items,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $validated = $this->attachMarks($validated);
        $comparativeStatementItem = ComparativeStatementItem::create($validated);
        $this->recalculateRanksAndWinner($comparativeStatementItem->comparative_statement_id);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatementItem created successfully',
            'data' => $comparativeStatementItem->fresh(),
        ], 201);
    }

    public function update(Request $request, ComparativeStatementItem $comparativeStatementItem)
    {
        $validated = $request->validate([
            'comparative_statement_id' => 'sometimes|required|exists:comparative_statements,id',
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'financial_evaluation_item_id' => 'nullable|exists:financial_evaluation_items,id',
            'technical_evaluation_item_id' => 'nullable|exists:technical_evaluation_items,id',
            'amount' => 'sometimes|required|numeric|min:0',
        ]);

        $validated = $this->attachMarks($validated);
        $comparativeStatementItem->update($validated);
        $this->recalculateRanksAndWinner($comparativeStatementItem->comparative_statement_id);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatementItem updated successfully',
            'data' => $comparativeStatementItem->fresh(),
        ]);
    }

    public function destroy(ComparativeStatementItem $comparativeStatementItem)
    {
        $statementId = $comparativeStatementItem->comparative_statement_id;
        $comparativeStatementItem->delete();
        $this->recalculateRanksAndWinner($statementId);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatementItem deleted successfully',
        ]);
    }

    // Financial/Technical Evaluation থেকে সেই ভেন্ডরের marks টেনে এনে
    // financial_marks/technical_marks/total_marks বসিয়ে দেয় — ম্যানুয়ালি
    // টাইপ করতে হয় না, তাই কারচুপির সুযোগও থাকে না।
    private function attachMarks(array $data): array
    {
        $financialMarks = null;
        $technicalMarks = null;

        if (! empty($data['financial_evaluation_item_id'])) {
            $financialMarks = FinancialEvaluationItem::find($data['financial_evaluation_item_id'])?->financial_marks;
        }
        if (! empty($data['technical_evaluation_item_id'])) {
            $technicalMarks = TechnicalEvaluationItem::find($data['technical_evaluation_item_id'])?->score;
        }

        $data['financial_marks'] = $financialMarks;
        $data['technical_marks'] = $technicalMarks;
        $data['total_marks'] = ($financialMarks !== null || $technicalMarks !== null)
            ? round(($financialMarks ?? 0) + ($technicalMarks ?? 0), 2)
            : null;

        return $data;
    }

    // Total Marks অনুযায়ী Rank বসায় (১ = সর্বোচ্চ), আর সবচেয়ে বেশি Total
    // Marks-ওয়ালা ভেন্ডরকে "Lowest Evaluated Bidder" (প্রস্তাবিত বিজয়ী)
    // হিসেবে statement-এ সেট করে — কমিটি চাইলে পরে ম্যানুয়ালি বদলে দিতে পারবে।
    private function recalculateRanksAndWinner(int $statementId): void
    {
        $items = ComparativeStatementItem::where('comparative_statement_id', $statementId)
            ->orderByDesc('total_marks')
            ->get();

        $rank = 1;
        foreach ($items as $item) {
            $item->updateQuietly(['rank' => $item->total_marks !== null ? $rank++ : null]);
        }

        $best = $items->whereNotNull('total_marks')->sortByDesc('total_marks')->first();
        if ($best) {
            ComparativeStatement::whereKey($statementId)->update(['lowest_evaluated_vendor_id' => $best->vendor_id]);
        }
    }
}