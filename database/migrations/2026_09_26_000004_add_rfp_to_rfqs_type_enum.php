<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Step 8 "RFP/RFI/Hiring Vendor/Consultant" — adds 'RFP' as a third
 * rfqs.type value (alongside the existing RFQ/OTM), covering RFP, RFI and
 * direct Vendor/Consultant hiring as a single type. A Services-category
 * case now requires this type below the OTM threshold, same as a Goods
 * case requires 'RFQ' (see RfqController::assertTypeMatchesPolicy()).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE rfqs MODIFY type ENUM('RFQ','OTM','RFP') NOT NULL");
    }

    public function down(): void
    {
        // Any existing 'RFP' rows would violate the narrower enum — move
        // them to 'RFQ' first so the rollback doesn't fail outright.
        DB::table('rfqs')->where('type', 'RFP')->update(['type' => 'RFQ']);
        DB::statement("ALTER TABLE rfqs MODIFY type ENUM('RFQ','OTM') NOT NULL");
    }
};
