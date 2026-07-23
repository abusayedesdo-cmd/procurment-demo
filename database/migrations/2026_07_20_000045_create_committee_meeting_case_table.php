<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_meeting_case', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('procurement_case_id')->constrained()->cascadeOnDelete();
            // 'first'  -> Tender Opening Form + Conflict of Interest declaration (steps 7-8)
            // 'second' -> Comparative Statement + its regulation (steps 14-15)
            $table->enum('agenda_type', ['first', 'second']);
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['committee_meeting_id', 'procurement_case_id', 'agenda_type'], 'meeting_case_agenda_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_meeting_case');
    }
};
