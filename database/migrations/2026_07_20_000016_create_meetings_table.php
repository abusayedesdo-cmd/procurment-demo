<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_plan_id')->nullable()->constrained('procurement_plans')->nullOnDelete();
            $table->unsignedInteger('meeting_sequence')->nullable();
            $table->date('meeting_date')->nullable();
            $table->string('notice_number')->nullable();
            $table->string('notice_file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};