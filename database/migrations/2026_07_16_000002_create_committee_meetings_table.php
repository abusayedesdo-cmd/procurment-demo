<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_no')->unique(); // e.g. CM-2026-014
            $table->date('meeting_date');
            $table->enum('status', ['scheduled', 'held', 'cancelled'])->default('scheduled');
            $table->text('minutes')->nullable();
            $table->timestamp('held_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meetings');
    }
};
