<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_evaluation_items', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('vendor_id')->constrained('quotations')->nullOnDelete();
            $table->decimal('financial_marks', 6, 2)->nullable()->after('quoted_amount');
        });
    }

    public function down(): void
    {
        Schema::table('financial_evaluation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropColumn('financial_marks');
        });
    }
};