<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Reproduces the two numbering schemes seen in real ESDO documents:
 *
 *  - Memo numbers (RFQ, Tender Notice, NOA, Work Order, ...) follow
 *    ESDO/{Department}/126/{seq}/{fiscal-year}, where {seq} is ONE
 *    running counter shared by every memo type in that fiscal year
 *    (e.g. .../652/2025-2026 and .../674/2025-2026 a few weeks apart,
 *    issued as different document types).
 *
 *  - Rezulation (meeting minutes) numbers are a separate running
 *    counter, e.g. "Rezulation No: -1175", "-1183".
 *
 * Both use row-locked increments so two requests can't collide.
 */
class MemoSequence
{
    /** Bangladesh fiscal year: 1 July – 30 June. */
    public static function fiscalYear(?Carbon $date = null): string
    {
        $date ??= now();
        return $date->month >= 7
            ? $date->year . '-' . ($date->year + 1)
            : ($date->year - 1) . '-' . $date->year;
    }

    /** e.g. MemoSequence::nextMemo('Procurement') -> "ESDO/Procurement/126/675/2025-2026" */
    public static function nextMemo(string $department, ?Carbon $date = null): string
    {
        $fy = self::fiscalYear($date);
        $n = self::increment("memo_{$fy}");
        return "ESDO/{$department}/126/{$n}/{$fy}";
    }

    /** e.g. MemoSequence::nextRezulation() -> 1184 (displayed as "Rezulation No: -1184") */
    public static function nextRezulation(): int
    {
        return self::increment('rezulation');
    }

    private static function increment(string $key): int
    {
        return DB::transaction(function () use ($key) {
            $row = DB::table('sequence_counters')->where('key', $key)->lockForUpdate()->first();

            if (! $row) {
                DB::table('sequence_counters')->insert([
                    'key' => $key, 'last_value' => 1,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                return 1;
            }

            $next = $row->last_value + 1;
            DB::table('sequence_counters')->where('key', $key)->update([
                'last_value' => $next, 'updated_at' => now(),
            ]);
            return $next;
        });
    }
}
