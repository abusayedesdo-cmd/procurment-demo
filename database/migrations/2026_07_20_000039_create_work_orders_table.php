<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_agreement_id')->constrained('contract_agreements')->cascadeOnDelete();
            $table->enum('category', ['Work', 'Goods', 'Service']);
            $table->string('wo_number')->unique();
            $table->date('wo_date');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
