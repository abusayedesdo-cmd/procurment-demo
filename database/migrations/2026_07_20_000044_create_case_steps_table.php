<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_case_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step_no'); // 1..23
            $table->string('name');
            $table->string('phase');
            $table->string('detail')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['procurement_case_id', 'step_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_steps');
    }
};
