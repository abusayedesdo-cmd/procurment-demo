<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->dateTime('submitted_at');
            $table->decimal('quoted_amount', 15, 2);
            $table->string('file_path')->nullable();
            $table->enum('status', ['received', 'opened', 'evaluated', 'disqualified'])->default('received');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
