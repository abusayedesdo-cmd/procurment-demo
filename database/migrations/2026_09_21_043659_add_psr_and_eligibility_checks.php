<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('psr_submitted')->default(false)->after('bin_submitted');
        });

        Schema::table('eligibility_report_items', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('vendor_id')->constrained('quotations')->nullOnDelete();
            $table->boolean('trade_license_verified')->default(false);
            $table->boolean('tin_verified')->default(false);
            $table->boolean('bin_verified')->default(false);
            $table->boolean('psr_verified')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('psr_submitted');
        });
        Schema::table('eligibility_report_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropColumn(['trade_license_verified', 'tin_verified', 'bin_verified', 'psr_verified']);
        });
    }
};