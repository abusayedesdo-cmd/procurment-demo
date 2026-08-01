<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE procurement_plan_package_periods MODIFY period_type ENUM('previous_year','current_year','quarter','year_total','grand_total') NOT NULL");
    }

    public function down(): void
    {
        DB::table('procurement_plan_package_periods')->where('period_type', 'current_year')->delete();
        DB::statement("ALTER TABLE procurement_plan_package_periods MODIFY period_type ENUM('previous_year','quarter','year_total','grand_total') NOT NULL");
    }
};