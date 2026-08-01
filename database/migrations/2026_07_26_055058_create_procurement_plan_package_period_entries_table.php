<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_plan_package_period_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_plan_package_period_id');
            $table->enum('granularity', ['month', 'quarter']);
            $table->unsignedTinyInteger('slot_number'); // 1-12 for month, 1-4 for quarter
            $table->string('entry_label'); // "January", "Quarter-1", etc.
            $table->decimal('no_of_unit', 12, 2)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('procurement_plan_package_period_id', 'ppp_period_entries_fk')
                ->references('id')->on('procurement_plan_package_periods')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_plan_package_period_entries');
    }
};