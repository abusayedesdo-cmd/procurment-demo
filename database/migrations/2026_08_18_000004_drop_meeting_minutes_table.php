<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Minutes content now lives directly on `meetings` (rezulation_no,
    // decisions, agenda, held_at) since one meeting produces exactly one
    // Rezulation/Minutes document — a separate table was redundant.
    public function up(): void
    {
        Schema::dropIfExists('meeting_minutes');
    }

    public function down(): void
    {
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('minutes_number')->unique();
            $table->text('resolution_text')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }
};
