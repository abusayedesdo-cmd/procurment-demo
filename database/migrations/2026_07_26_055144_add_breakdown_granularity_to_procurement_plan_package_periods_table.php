<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_plan_package_periods', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_plan_package_periods', 'slot_order')) {
                $table->unsignedTinyInteger('slot_order')->default(0)->after('year_number');
            }
            if (! Schema::hasColumn('procurement_plan_package_periods', 'breakdown_granularity')) {
                $table->enum('breakdown_granularity', ['month', 'quarter'])->nullable()->after('slot_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_plan_package_periods', function (Blueprint $table) {
            $table->dropColumn(['slot_order', 'breakdown_granularity']);
        });
    }
};