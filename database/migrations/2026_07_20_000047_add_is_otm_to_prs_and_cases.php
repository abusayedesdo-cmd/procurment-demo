<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->boolean('is_otm')->nullable()->after('window_type');
        });

        Schema::table('procurement_cases', function (Blueprint $table) {
            $table->boolean('is_otm')->default(false)->after('method');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropColumn('is_otm');
        });

        Schema::table('procurement_cases', function (Blueprint $table) {
            $table->dropColumn('is_otm');
        });
    }
};