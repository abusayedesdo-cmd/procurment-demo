<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sole_sourcing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->text('justification')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->date('approval_date')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sole_sourcing_requests');
    }
};
