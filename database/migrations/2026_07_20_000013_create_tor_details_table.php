<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tor_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tor_details');
    }
};
