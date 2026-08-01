<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_plan_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_annual_plan_id')->constrained('procurement_annual_plans')->cascadeOnDelete();
            $table->unsignedInteger('sl_no')->nullable(); // display order, matches sheet's "Sl. No."
            $table->foreignId('procurement_category_id')->constrained('procurement_categories');
            $table->string('budgeted_head'); // "Budgeted Head (Items)" e.g. "Computer"
            $table->string('unit')->nullable();
            $table->string('package_number')->unique()->nullable();
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->enum('procurement_method', ['RFQ', 'Tender', 'Quotation', 'Framework Agreement', 'Sole Sourcing'])->nullable();
            $table->foreignId('responsible_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('budget_line_id')->nullable()->constrained('budget_lines')->nullOnDelete();

            $table->date('planned_invitation_date')->nullable();
            $table->date('actual_invitation_date')->nullable();
            $table->date('planned_evaluation_date')->nullable();
            $table->date('actual_evaluation_date')->nullable();
            $table->date('planned_award_date')->nullable();
            $table->date('actual_award_date')->nullable();
            $table->date('planned_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_plan_packages');
    }
};