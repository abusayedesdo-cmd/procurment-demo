<?php

namespace App\Services;

use App\Models\SequenceCounter;
use Illuminate\Support\Facades\DB;

/**
 * Generates sequential document numbers (PR, RFQ, NOA, Work Order,
 * Agreement, Rezulation/Minutes) using the existing `sequence_counters`
 * table (kept from the earlier system: key => last_value).
 *
 * Usage:
 *   app(NumberGeneratorService::class)->next('pr');
 *   app(NumberGeneratorService::class)->next('rezulation');
 *
 * The fiscal-year-aware office memo sequence (used for RFQ/NOA/Work
 * Order/Tender Notice sharing ONE running number, as in the old system)
 * can be generated with:
 *   app(NumberGeneratorService::class)->nextMemo();
 */
class NumberGeneratorService
{
    /**
     * Get the next value for a given counter key, formatted with a prefix.
     * Example: next('pr', 'PR-') => "PR-000123"
     */
    public function next(string $key, string $prefix = '', int $pad = 6): string
    {
        return DB::transaction(function () use ($key, $prefix, $pad) {
            $counter = SequenceCounter::lockForUpdate()->firstOrCreate(
                ['key' => $key],
                ['last_value' => 0]
            );

            $counter->last_value += 1;
            $counter->save();

            return $prefix . str_pad((string) $counter->last_value, $pad, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Same as next(), but self-healing: if the counter has fallen behind
     * the data actually in the table (e.g. rows imported/seeded outside
     * this service, or a counter reset during a migration), it keeps
     * advancing past any number that's already taken instead of returning
     * a duplicate and letting the insert fail with a constraint violation.
     *
     * Usage: nextUnique('pr', 'PR-', 'purchase_requisitions', 'pr_number')
     */
    public function nextUnique(string $key, string $prefix, string $table, string $column, int $pad = 6): string
    {
        return DB::transaction(function () use ($key, $prefix, $table, $column, $pad) {
            $counter = SequenceCounter::lockForUpdate()->firstOrCreate(
                ['key' => $key],
                ['last_value' => 0]
            );

            do {
                $counter->last_value += 1;
                $candidate = $prefix . str_pad((string) $counter->last_value, $pad, '0', STR_PAD_LEFT);
            } while (DB::table($table)->where($column, $candidate)->exists());

            $counter->save();

            return $candidate;
        });
    }

    /**
     * Fiscal-year memo sequence shared by RFQ/NOA/Work Order/Tender
     * Notice numbers, matching the real ESDO memo format:
     * .../126/652/2025-2026
     */
    public function nextMemo(string $officeCode = '126'): string
    {
        $fiscalYear = $this->currentFiscalYear();
        $key = "memo_{$fiscalYear}";
        $seq = $this->next($key, '', 1);

        return "{$officeCode}/{$seq}/{$fiscalYear}";
    }

    /**
     * Rezulation (meeting minutes) number — separate running sequence.
     */
    public function nextRezulation(): string
    {
        return $this->next('rezulation', '', 1);
    }

    /**
     * Document-typed memo number sharing the office-wide fiscal-year
     * counter, matching real ESDO paper forms:
     *   ESDO/Procurement/Notice/126/{seq}/{FY}
     *   ESDO/Procurement/Attendence/126/{seq}/{FY}   (ESDO's own spelling)
     */
    public function nextDocMemo(string $department, string $docType, string $officeCode = '126'): string
    {
        $fiscalYear = $this->currentFiscalYear();
        $seq = $this->next("memo_{$fiscalYear}", '', 3);

        return "ESDO/{$department}/{$docType}/{$officeCode}/{$seq}/{$fiscalYear}";
    }

    /**
     * Same shared office-wide fiscal-year counter as nextDocMemo(), but
     * without a document-type segment — matches ESDO/Purchases Committee/
     * 126/{seq}/{FY}, the single reference number a case's RFQ/Resolution/
     * Work Order/Bill all carry throughout its lifecycle.
     */
    public function nextCommitteeMemo(string $committee, string $officeCode = '126'): string
    {
        $fiscalYear = $this->currentFiscalYear();
        $seq = $this->next("memo_{$fiscalYear}", '', 3);

        return "ESDO/{$committee}/{$officeCode}/{$seq}/{$fiscalYear}";
    }

    protected function currentFiscalYear(): string
    {
        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');
        // Bangladeshi fiscal year: July–June
        if ($month >= 7) {
            return $year . '-' . ($year + 1);
        }
        return ($year - 1) . '-' . $year;
    }
}
