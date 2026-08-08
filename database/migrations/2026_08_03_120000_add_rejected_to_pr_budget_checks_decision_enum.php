<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pr_budget_checks MODIFY decision ENUM('recommended','returned','rejected') NOT NULL");
    }

    public function down(): void
    {
        DB::table('pr_budget_checks')->where('decision', 'rejected')->delete();
        DB::statement("ALTER TABLE pr_budget_checks MODIFY decision ENUM('recommended','returned') NOT NULL");
    }
};
