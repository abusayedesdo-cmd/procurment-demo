<?php

namespace App\Services;

use App\Models\ComparativeStatement;
use App\Models\ComparativeStatementItem;
use App\Models\EligibilityReportItem;
use App\Models\FinancialEvaluationItem;
use App\Models\FinancialEvaluationReport;
use App\Models\Quotation;
use App\Models\TechnicalEvaluationItem;
use App\Models\TechnicalEvaluationReport;
use App\Models\TenderSchedule;

/**
 * Fills the vendor rows of the evaluation reports straight from the earlier steps, so a new
 * report no longer prints "[No ... items recorded yet]" until someone types every vendor in:
 *
 *   Quotations -> Eligibility -> Technical -> Financial -> Comparative Statement
 *
 * Every method only ADDS missing vendors; rows that already exist are never touched or duplicated.
 * (The Eligibility report has its own loader in EligibilityReportController.)
 */
class EvaluationVendorSeeder
{
    /**
     * Vendors allowed to go on to evaluation: the ones the Eligibility report marked eligible.
     * If there is no Eligibility report yet, fall back to every quotation that was not rejected
     * (only the "Forwarded for Evaluation" ones when any were forwarded).
     *
     * @return array<int, int>
     */
    public static function eligibleVendorIds(int $rfqId): array
    {
        $eligibility = EligibilityReportItem::whereHas('report', fn ($q) => $q->where('rfq_id', $rfqId))->get();
        if ($eligibility->isNotEmpty()) {
            return $eligibility->where('eligible', true)->pluck('vendor_id')->unique()->values()->all();
        }

        $quotations = Quotation::where('rfq_id', $rfqId)
            ->whereNotIn('status', ['rejected', 'disqualified'])
            ->get();
        $forwarded = $quotations->where('status', 'forwarded');

        return ($forwarded->isNotEmpty() ? $forwarded : $quotations)->pluck('vendor_id')->unique()->values()->all();
    }

    /** Technical report: one score row (score left empty) per eligible vendor. */
    public static function seedTechnical(TechnicalEvaluationReport $report): int
    {
        $already = $report->items()->pluck('vendor_id')->all();
        $count = 0;

        foreach (self::eligibleVendorIds($report->rfq_id) as $vendorId) {
            if (in_array($vendorId, $already, true)) {
                continue;
            }
            TechnicalEvaluationItem::create(['ter_id' => $report->id, 'vendor_id' => $vendorId]);
            $count++;
        }

        return $count;
    }

    /**
     * Financial report: one amount row per vendor that went through technical evaluation
     * (or, with no technical report yet, per eligible vendor), amount taken from the quotation.
     */
    public static function seedFinancial(FinancialEvaluationReport $report): int
    {
        $technicalVendors = self::latestPerVendor(
            TechnicalEvaluationItem::whereHas('report', fn ($q) => $q->where('rfq_id', $report->rfq_id))
        )->keys()->all();
        $vendorIds = $technicalVendors ?: self::eligibleVendorIds($report->rfq_id);

        $already = $report->items()->pluck('vendor_id')->all();
        $count = 0;

        foreach ($vendorIds as $vendorId) {
            if (in_array($vendorId, $already, true)) {
                continue;
            }
            $quotation = Quotation::where('rfq_id', $report->rfq_id)
                ->where('vendor_id', $vendorId)
                ->whereNotIn('status', ['rejected', 'disqualified'])
                ->latest('id')
                ->first();
            if (! $quotation) {
                continue; // no price to evaluate
            }
            FinancialEvaluationItem::create([
                'fer_id' => $report->id,
                'vendor_id' => $vendorId,
                'quotation_id' => $quotation->id,
                'quoted_amount' => $quotation->quoted_amount,
            ]);
            $count++;
        }

        if ($count > 0) {
            self::recalculateFinancialMarks($report);
        }

        return $count;
    }

    /**
     * Comparative statement: one row per vendor on the Financial report (amount + financial marks),
     * with the matching Technical score attached, then total marks, ranks and the winner worked out.
     */
    public static function seedComparative(ComparativeStatement $statement): int
    {
        $rfqId = $statement->rfq_id;
        $allowed = self::eligibleVendorIds($rfqId);

        $financial = self::latestPerVendor(
            FinancialEvaluationItem::whereHas('report', fn ($q) => $q->where('rfq_id', $rfqId))
        );
        $technical = self::latestPerVendor(
            TechnicalEvaluationItem::whereHas('report', fn ($q) => $q->where('rfq_id', $rfqId))
        );

        $already = $statement->items()->pluck('vendor_id')->all();
        $count = 0;

        foreach ($financial as $vendorId => $fin) {
            if (in_array($vendorId, $already, true) || ! in_array($vendorId, $allowed, true)) {
                continue;
            }
            $tech = $technical->get($vendorId);
            $fm = $fin->financial_marks;
            $tm = $tech?->score;

            ComparativeStatementItem::create([
                'comparative_statement_id' => $statement->id,
                'vendor_id' => $vendorId,
                'financial_evaluation_item_id' => $fin->id,
                'technical_evaluation_item_id' => $tech?->id,
                'amount' => $fin->quoted_amount,
                'financial_marks' => $fm,
                'technical_marks' => $tm,
                'total_marks' => ($fm !== null || $tm !== null) ? round(($fm ?? 0) + ($tm ?? 0), 2) : null,
            ]);
            $count++;
        }

        if ($count > 0) {
            self::recalculateRanksAndWinner($statement);
        }

        return $count;
    }

    /** Newest row per vendor, keyed by vendor_id (a vendor can appear in more than one report). */
    private static function latestPerVendor($query)
    {
        return $query->orderByDesc('id')->get()->unique('vendor_id')->keyBy('vendor_id');
    }

    /** Same rule as FinancialEvaluationItemController: lowest price / vendor price x financial weight. */
    private static function recalculateFinancialMarks(FinancialEvaluationReport $report): void
    {
        $weight = TenderSchedule::where('rfq_id', $report->rfq_id)->value('financial_weight');
        if (! $weight) {
            return;
        }

        $items = FinancialEvaluationItem::where('fer_id', $report->id)->get();
        $lowest = $items->where('quoted_amount', '>', 0)->min('quoted_amount');
        if (! $lowest) {
            return;
        }

        foreach ($items as $item) {
            $item->updateQuietly([
                'financial_marks' => $item->quoted_amount > 0 ? round(($lowest / $item->quoted_amount) * $weight, 2) : 0,
            ]);
        }
    }

    /** Same rule as ComparativeStatementItemController: rank 1 = highest total marks, that vendor wins. */
    private static function recalculateRanksAndWinner(ComparativeStatement $statement): void
    {
        $items = ComparativeStatementItem::where('comparative_statement_id', $statement->id)
            ->orderByDesc('total_marks')
            ->get();

        $rank = 1;
        foreach ($items as $item) {
            $item->updateQuietly(['rank' => $item->total_marks !== null ? $rank++ : null]);
        }

        $best = $items->whereNotNull('total_marks')->sortByDesc('total_marks')->first();
        if ($best) {
            ComparativeStatement::whereKey($statement->id)->update(['lowest_evaluated_vendor_id' => $best->vendor_id]);
        }
    }
}
