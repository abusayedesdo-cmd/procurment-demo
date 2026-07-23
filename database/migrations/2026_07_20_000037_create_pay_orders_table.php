<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_award_id')->constrained('contract_awards')->cascadeOnDelete();
            $table->decimal('awarded_amount', 15, 2);
            $table->decimal('pay_order_amount', 15, 2);
            $table->decimal('received_amount', 15, 2)->nullable();
            $table->date('received_date')->nullable();
            $table->text('calculation_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_orders');
    }
};
