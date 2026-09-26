<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_evaluation_reports', function (Blueprint $table) {
            // এক RFQ-তে একাধিক Scheme/Shelter থাকলে (rfq_items.scheme_name
            // অনুযায়ী), প্রতিটা Scheme-এর জন্য আলাদা Financial Evaluation
            // Report — এই ফিল্ড দিয়ে বোঝা যাবে কোনটা কোন Scheme-এর।
            $table->string('scheme_name')->nullable()->after('rfq_id');
        });
    }

    public function down(): void
    {
        Schema::table('financial_evaluation_reports', function (Blueprint $table) {
            $table->dropColumn('scheme_name');
        });
    }
};