<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ter_id')->constrained('technical_evaluation_reports')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('max_marks', 5, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('technical_evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technical_evaluation_item_id')->constrained('technical_evaluation_items')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('technical_evaluation_criteria')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['technical_evaluation_item_id', 'criterion_id'], 'tes_item_criterion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_evaluation_scores');
        Schema::dropIfExists('technical_evaluation_criteria');
    }
};