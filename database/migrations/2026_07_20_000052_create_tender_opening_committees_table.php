<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_opening_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_opening_id')->constrained('tender_openings')->cascadeOnDelete();
            $table->unsignedInteger('serial_no');
            $table->string('name');
            $table->string('designation'); // Convenor, Member, Member Secretary
            $table->boolean('signed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_opening_committees');
    }
};
