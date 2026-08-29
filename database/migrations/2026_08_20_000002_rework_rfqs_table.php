<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Note on numbering: unlike meetings (separate Notice/Attendance/
    // Minutes numbers), the real case example shows ONE memo number
    // (rfq_number) reused as the reference across the Tender Notice, the
    // Work Order, and the vendor invoice/PO — so no separate notice_number
    // field is needed here.
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            // Constraint name can differ between environments (local vs the
            // live server) since this table's history includes manual raw
            // SQL — look up the real name instead of assuming Laravel's
            // default naming convention.
            $fk = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'rfqs'
                 AND COLUMN_NAME = 'procurement_plan_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            if (! empty($fk)) {
                $table->dropForeign($fk[0]->CONSTRAINT_NAME);
            }
            if (Schema::hasColumn('rfqs', 'procurement_plan_id')) {
                $table->dropColumn('procurement_plan_id');
            }
        });

        Schema::table('rfqs', function (Blueprint $table) {
            if (! Schema::hasColumn('rfqs', 'procurement_case_id')) {
                $table->foreignId('procurement_case_id')->nullable()->after('id')
                    ->constrained('procurement_cases')->cascadeOnDelete();
            }

            // Model already listed this in $fillable but the column never
            // existed — the Tender Notice subject line needs it.
            if (! Schema::hasColumn('rfqs', 'subject')) {
                $table->string('subject')->nullable()->after('rfq_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropForeign(['procurement_case_id']);
            $table->dropColumn(['procurement_case_id', 'subject']);
            $table->foreignId('procurement_plan_id')->nullable()->constrained('procurement_plans')->cascadeOnDelete();
        });
    }
};