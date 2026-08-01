<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparative_statement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comparative_statement_id')->constrained('comparative_statements')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->unsignedInteger('rank')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparative_statement_items');
    }
};
