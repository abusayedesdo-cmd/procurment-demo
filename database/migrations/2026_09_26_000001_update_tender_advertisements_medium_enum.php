<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Process doc, Step 9 (Tender Schedule/OTM): distribution medium should be
 * BD Jobs / National Newspaper / Local Newspaper, not the old
 * Newspaper/bdjobs pair. Existing rows are mapped so nothing is lost:
 *   bdjobs    -> BD Jobs
 *   Newspaper -> National Newspaper (best guess; re-check old rows by hand
 *                if any were actually placed in a local paper)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tender_advertisements MODIFY medium VARCHAR(50) NOT NULL");
        DB::table('tender_advertisements')->where('medium', 'bdjobs')->update(['medium' => 'BD Jobs']);
        DB::table('tender_advertisements')->where('medium', 'Newspaper')->update(['medium' => 'National Newspaper']);
        DB::statement("ALTER TABLE tender_advertisements MODIFY medium ENUM('BD Jobs','National Newspaper','Local Newspaper') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tender_advertisements MODIFY medium VARCHAR(50) NOT NULL");
        DB::table('tender_advertisements')->where('medium', 'BD Jobs')->update(['medium' => 'bdjobs']);
        DB::table('tender_advertisements')->whereIn('medium', ['National Newspaper', 'Local Newspaper'])->update(['medium' => 'Newspaper']);
        DB::statement("ALTER TABLE tender_advertisements MODIFY medium ENUM('Newspaper','bdjobs') NOT NULL");
    }
};
