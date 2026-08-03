<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->foreignId('district_id')->nullable()->after('district')
                ->constrained('procurement_districts')->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->after('district_id')
                ->constrained('procurement_upazilas')->nullOnDelete();
        });

        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->dropColumn('district');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->string('district')->nullable()->after('project_name');
        });

        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('upazila_id');
        });
    }
};
