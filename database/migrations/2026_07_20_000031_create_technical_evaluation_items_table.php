<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_evaluation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ter_id')->constrained('technical_evaluation_reports')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_evaluation_items');
    }
};
