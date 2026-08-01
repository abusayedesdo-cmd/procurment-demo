<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_category_id')->constrained('budget_categories')->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('item_code');   // e.g. 3.3.12, A.1.1
            $table->string('item_name');
            $table->string('unit')->nullable();
            $table->decimal('no_of_units', 12, 2)->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('original_budget', 15, 2)->default(0);      // "Existing" total
            $table->decimal('approved_budget', 15, 2)->default(0);      // current/realigned total
            $table->decimal('percent_change', 6, 2)->nullable();
            $table->text('realignment_remarks')->nullable();
            $table->decimal('reported_actual_expense', 15, 2)->default(0); // baseline from last donor fin. report
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};