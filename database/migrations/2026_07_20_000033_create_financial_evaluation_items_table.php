<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_evaluation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fer_id')->constrained('financial_evaluation_reports')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->decimal('quoted_amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_evaluation_items');
    }
};
