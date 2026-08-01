<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boq_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_details');
    }
};
