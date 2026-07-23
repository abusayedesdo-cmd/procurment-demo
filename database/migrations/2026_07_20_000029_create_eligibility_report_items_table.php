<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eligibility_report_id')->constrained('eligibility_reports')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->boolean('eligible')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_report_items');
    }
};
