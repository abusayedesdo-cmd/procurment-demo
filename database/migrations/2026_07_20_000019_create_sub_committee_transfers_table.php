<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_committee_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_plan_id')->constrained('procurement_plans')->cascadeOnDelete();
            $table->foreignId('from_committee_id')->constrained('purchase_committees');
            $table->foreignId('to_committee_id')->constrained('purchase_committees');
            $table->text('transfer_note')->nullable();
            $table->date('transfer_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_committee_transfers');
    }
};
