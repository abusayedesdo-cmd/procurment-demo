<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            // Sub-category label as it appears in the tender schedule / RFQ item list,
            // e.g. "Bicycle and Van", "Cloth", "Laptop" — groups line items under one heading.
            $table->string('category')->nullable();
            $table->unsignedInteger('serial_no');
            $table->text('description');
            $table->decimal('quantity', 15, 2);
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->text('delivery_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
    }
};
