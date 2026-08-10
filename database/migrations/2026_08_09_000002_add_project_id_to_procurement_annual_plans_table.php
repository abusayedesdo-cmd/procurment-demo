<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('prepared_by')
                ->constrained('projects')->nullOnDelete();
        });

        // Backfill existing plans from the project of the user who prepared them.
        DB::statement(<<<'SQL'
            UPDATE procurement_annual_plans pap
            INNER JOIN users u ON u.id = pap.prepared_by
            SET pap.project_id = u.project_id
            WHERE u.project_id IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
