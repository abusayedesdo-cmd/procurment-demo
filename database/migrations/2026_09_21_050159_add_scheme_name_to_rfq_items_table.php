<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            // একই RFQ-এর ভেতরে একাধিক Scheme/Shelter/Sub-BOQ থাকলে
            // (যেমন "Najirhat A Malek GPS & Cyclone Shelter") — প্রতিটার
            // নিজস্ব সাব-টোটাল বের করার জন্য এই গ্রুপিং ফিল্ড।
            $table->string('scheme_name')->nullable()->after('rfq_id');
        });
    }

    public function down(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropColumn('scheme_name');
        });
    }
};