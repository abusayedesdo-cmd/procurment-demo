<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_line_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_line_id')->constrained('budget_lines')->cascadeOnDelete();
            $table->enum('period_type', ['previous_year', 'quarter', 'year_total', 'grand_total']);
            $table->string('period_label'); // e.g. "2024-2025", "Quarter-1 (July-October)"
            $table->unsignedTinyInteger('year_number')->nullable(); // groups Year-1/2/3
            $table->date('period_start')->nullable(); // used for PR-date matching; null for year_total/grand_total rows
            $table->date('period_end')->nullable();
            $table->decimal('no_of_unit', 12, 2)->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_line_periods');
    }
};