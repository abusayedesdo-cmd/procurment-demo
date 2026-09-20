<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained('purchase_committees')->nullOnDelete();
            $table->string('vendor_name');
            $table->text('item_description');
            $table->decimal('amount', 14, 2);
            $table->date('purchase_date');
            $table->string('receipt_file')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('purchased_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_purchases');
    }
};