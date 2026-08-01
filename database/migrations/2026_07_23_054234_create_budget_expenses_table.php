<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_line_id')->constrained('budget_lines')->cascadeOnDelete();
            $table->foreignId('pr_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('source')->default('pr'); // pr, manual, fin_report
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_expenses');
    }
};