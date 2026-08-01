<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->text('project_location')->nullable()->after('district');
            $table->text('working_area')->nullable()->after('project_location');
            $table->text('activity_summary')->nullable()->after('working_area');
            $table->string('project_duration')->nullable()->after('fiscal_year_end');
            $table->date('agreement_date')->nullable()->after('project_duration');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_annual_plans', function (Blueprint $table) {
            $table->dropColumn(['project_location', 'working_area', 'activity_summary', 'project_duration', 'agreement_date']);
        });
    }
};