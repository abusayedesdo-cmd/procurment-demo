<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_plan_package_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_plan_package_id');
            $table->enum('period_type', ['previous_year', 'quarter', 'year_total', 'grand_total']);
            $table->string('period_label');
            $table->unsignedTinyInteger('year_number')->nullable();
            $table->decimal('no_of_unit', 12, 2)->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('procurement_plan_package_id', 'ppp_periods_package_fk')
                ->references('id')->on('procurement_plan_packages')
                ->cascadeOnDelete();
        });
    }

   public function down(): void
    {
        Schema::dropIfExists('procurement_plan_package_periods');
    }
};