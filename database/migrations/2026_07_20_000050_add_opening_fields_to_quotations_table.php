<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Attendance sheet fields — who represented the bidder at opening.
            $table->string('representative_name')->nullable()->after('vendor_id');
            $table->string('representative_contact')->nullable()->after('representative_name');
            $table->boolean('attended')->default(false)->after('representative_contact');

            // Eligibility document checklist recorded at the opening (Tender/RFQ
            // Opening Record Form — "All Required Documents Submitted Yes/No").
            $table->boolean('trade_license_submitted')->default(false)->after('status');
            $table->boolean('tin_submitted')->default(false)->after('trade_license_submitted');
            $table->boolean('bin_submitted')->default(false)->after('tin_submitted');
            $table->text('opening_remarks')->nullable()->after('bin_submitted');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'representative_name',
                'representative_contact',
                'attended',
                'trade_license_submitted',
                'tin_submitted',
                'bin_submitted',
                'opening_remarks',
            ]);
        });
    }
};
