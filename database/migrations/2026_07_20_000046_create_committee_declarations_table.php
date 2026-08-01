<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_member_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_conflict')->nullable(); // null = not yet declared
            $table->string('notes')->nullable();
            $table->timestamp('declared_at')->nullable();
            $table->timestamps();
            $table->unique(['committee_meeting_id', 'committee_member_id'], 'meeting_member_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_declarations');
    }
};
