<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_award_id')->constrained('contract_awards')->cascadeOnDelete();
            $table->enum('category', ['Work', 'Goods', 'Service']);
            $table->string('agreement_number')->unique();
            $table->date('agreement_date');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_agreements');
    }
};
