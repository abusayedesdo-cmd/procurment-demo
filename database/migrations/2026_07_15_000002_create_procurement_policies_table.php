<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('group'); // 'Threshold' or 'Milestone offset'
            $table->decimal('value', 14, 2);
            $table->string('unit')->nullable(); // 'BDT', 'days'
            $table->string('help')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_policies');
    }
};
