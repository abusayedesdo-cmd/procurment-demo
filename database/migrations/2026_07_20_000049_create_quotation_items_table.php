<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->cascadeOnDelete();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['quotation_id', 'rfq_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
