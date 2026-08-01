<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pr_budget_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('pr_approval_id')->nullable()->constrained('pr_approvals')->nullOnDelete();
            $table->foreignId('budget_line_id')->nullable()->constrained('budget_lines')->nullOnDelete();
            $table->string('budget_code')->nullable();                       // snapshot, in case entered manually
            $table->decimal('available_budget_amount', 15, 2)->nullable();   // balance at time of check
            $table->boolean('is_budget_code_verified')->default(false);
            $table->boolean('is_budget_available')->default(false);
            $table->enum('decision', ['recommended', 'returned']);           // recommended = confirmed & sent to Approver
            $table->foreignId('checked_by')->constrained('users');
            $table->date('checked_at');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_budget_checks');
    }
};