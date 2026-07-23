<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->enum('window_type', ['PR', 'BOQ', 'TOR', 'Design_Drawing'])->default('PR');
            $table->foreignId('category_id')->constrained('procurement_categories');
            $table->date('requisition_date');
            $table->date('estimated_delivery_date')->nullable();
            $table->decimal('total_estimated_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'reviewed', 'checked', 'approved', 'rejected'])->default('draft');
            $table->foreignId('raised_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};