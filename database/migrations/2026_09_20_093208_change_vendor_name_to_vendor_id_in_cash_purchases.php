<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_purchases', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('committee_id')->constrained('vendors')->nullOnDelete();
        });
        Schema::table('cash_purchases', function (Blueprint $table) {
            $table->dropColumn('vendor_name');
        });
    }

    public function down(): void
    {
        Schema::table('cash_purchases', function (Blueprint $table) {
            $table->string('vendor_name')->nullable();
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};