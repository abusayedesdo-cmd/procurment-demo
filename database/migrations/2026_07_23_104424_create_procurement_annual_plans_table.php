<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_annual_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('plan_type', ['annual', 'project']);
            $table->string('title');
            $table->string('project_name')->nullable();
            $table->string('district')->nullable();
            $table->date('fiscal_year_start');
            $table->date('fiscal_year_end');
            $table->string('donor_name')->nullable();
            $table->string('funding_source')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_annual_plans');
    }
};