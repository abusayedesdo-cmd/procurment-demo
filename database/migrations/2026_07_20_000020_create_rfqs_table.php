<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_plan_id')->constrained('procurement_plans')->cascadeOnDelete();
            $table->string('rfq_number')->unique();
            $table->enum('type', ['RFQ', 'OTM']);
            $table->date('issue_date');
            $table->date('closing_date');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
