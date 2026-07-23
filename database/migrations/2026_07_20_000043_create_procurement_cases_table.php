<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_cases', function (Blueprint $table) {
            $table->id();
            $table->string('ref')->unique();
            $table->foreignId('purchase_requisition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('category', ['Goods', 'Works', 'Services']);
            $table->enum('method', ['RFQ', 'RFP', 'RFT']); // Goods→RFQ, Services→RFP, Works→RFT
            $table->decimal('amount', 14, 2)->default(0);
            $table->unsignedTinyInteger('current_step')->default(0); // 0..23
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_cases');
    }
};
