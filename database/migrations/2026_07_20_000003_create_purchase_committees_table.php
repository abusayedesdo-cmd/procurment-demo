<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_committees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['main', 'sub'])->default('main');
            $table->foreignId('parent_committee_id')->nullable()->constrained('purchase_committees')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_committees');
    }
};
