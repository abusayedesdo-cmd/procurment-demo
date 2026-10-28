<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mark of the Sample Verification Report for a bidder (0-100). Optional: when it is empty the
 * statement works exactly as before (Technical + Financial); when it is filled it is added to the total.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comparative_statement_items') && ! Schema::hasColumn('comparative_statement_items', 'sample_marks')) {
            Schema::table('comparative_statement_items', function (Blueprint $table) {
                $table->decimal('sample_marks', 6, 2)->nullable()->after('technical_marks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('comparative_statement_items', 'sample_marks')) {
            Schema::table('comparative_statement_items', function (Blueprint $table) {
                $table->dropColumn('sample_marks');
            });
        }
    }
};
