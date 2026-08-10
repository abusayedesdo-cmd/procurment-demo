<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_plan_packages', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('procurement_annual_plan_id')
                ->constrained('projects')->nullOnDelete();
        });

        // Backfill: a package always belongs to its parent annual plan's project.
        DB::statement(<<<'SQL'
            UPDATE procurement_plan_packages ppp
            INNER JOIN procurement_annual_plans pap ON pap.id = ppp.procurement_annual_plan_id
            SET ppp.project_id = pap.project_id
            WHERE pap.project_id IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('procurement_plan_packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
