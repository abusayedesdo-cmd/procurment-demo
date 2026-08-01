<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->foreignId('procurement_plan_package_id')->nullable()->after('budget_line_id')
                ->constrained('procurement_plan_packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('procurement_plan_package_id');
        });
    }
};