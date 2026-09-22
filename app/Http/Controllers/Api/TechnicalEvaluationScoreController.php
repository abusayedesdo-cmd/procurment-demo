<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicalEvaluationItem;
use App\Models\TechnicalEvaluationScore;
use Illuminate\Http\Request;

class TechnicalEvaluationScoreController extends Controller
{
    public function index(Request $request)
    {
        $query = TechnicalEvaluationScore::query()->with(['item.vendor', 'criterion']);
        if ($request->filled('technical_evaluation_item_id')) {
            $query->where('technical_evaluation_item_id', $request->query('technical_evaluation_item_id'));
        }
        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'technical_evaluation_item_id' => 'required|exists:technical_evaluation_items,id',
            'criterion_id' => 'required|exists:technical_evaluation_criteria,id',
            'score' => 'required|numeric|min:0',
        ]);

        $score = TechnicalEvaluationScore::updateOrCreate(
            ['technical_evaluation_item_id' => $validated['technical_evaluation_item_id'], 'criterion_id' => $validated['criterion_id']],
            ['score' => $validated['score']]
        );

        $this->recalculateTotal($validated['technical_evaluation_item_id']);

        return response()->json(['success' => true, 'data' => $score->fresh()], 201);
    }

    public function update(Request $request, TechnicalEvaluationScore $technicalEvaluationScore)
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0',
        ]);
        $technicalEvaluationScore->update($validated);
        $this->recalculateTotal($technicalEvaluationScore->technical_evaluation_item_id);

        return response()->json(['success' => true, 'data' => $technicalEvaluationScore->fresh()]);
    }

    public function destroy(TechnicalEvaluationScore $technicalEvaluationScore)
    {
        $itemId = $technicalEvaluationScore->technical_evaluation_item_id;
        $technicalEvaluationScore->delete();
        $this->recalculateTotal($itemId);

        return response()->json(['success' => true]);
    }

    // প্রতিটা criteria-স্কোর সেভ/আপডেট/ডিলিট হওয়ার পর, সেই ভেন্ডরের
    // মোট Technical Marks (সব criteria-স্কোরের যোগফল) স্বয়ংক্রিয়ভাবে আপডেট।
    private function recalculateTotal(int $itemId): void
    {
        $total = TechnicalEvaluationScore::where('technical_evaluation_item_id', $itemId)->sum('score');
        TechnicalEvaluationItem::whereKey($itemId)->update(['score' => $total]);
    }
}