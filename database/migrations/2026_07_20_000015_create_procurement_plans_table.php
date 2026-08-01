<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->date('received_pr_date');
            $table->enum('nature', ['RFQ', 'OTM']);
            $table->decimal('estimated_amount', 15, 2);
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->date('est_advertisement_date')->nullable();
            $table->date('est_closing_date')->nullable();
            $table->date('est_opening_date')->nullable();
            $table->date('est_evaluation_date')->nullable();
            $table->date('est_noa_date')->nullable();
            $table->date('est_contract_signing_date')->nullable();
            $table->date('est_work_order_date')->nullable();
            $table->date('est_delivery_date')->nullable();
            $table->unsignedInteger('est_completion_days')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_plans');
    }
};
