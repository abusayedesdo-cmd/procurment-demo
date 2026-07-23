<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_plan_id')->constrained('procurement_plans')->cascadeOnDelete();
            $table->enum('category', ['Work', 'Goods', 'Service']);
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('noa_number')->unique();
            $table->date('noa_date');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_awards');
    }
};
