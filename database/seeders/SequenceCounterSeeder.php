<?php

namespace Database\Seeders;

use App\Services\MemoSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SequenceCounterSeeder extends Seeder
{
    /**
     * PLACEHOLDER starting points — the real documents show memo numbers
     * 652/674/726 and Rezulation numbers 1175/1183 in FY 2025-2026 as of
     * mid-2026. Set here so new numbers continue past the highest seen
     * value instead of restarting at 1. An Admin should confirm/adjust
     * these to match ESDO's actual current running numbers before this
     * is used for real documents.
     */
    public function run(): void
    {
        $fy = MemoSequence::fiscalYear();

        DB::table('sequence_counters')->updateOrInsert(
            ['key' => "memo_{$fy}"],
            ['last_value' => 726, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('sequence_counters')->updateOrInsert(
            ['key' => 'rezulation'],
            ['last_value' => 1183, 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
