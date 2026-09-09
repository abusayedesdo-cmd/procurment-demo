<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_awards', function (Blueprint $table) {
            // ESDO Procurement Policy §23 (Security Deposit): the winning
            // bidder's Earnest Money is converted into a Security Deposit
            // that stays with ESDO until the warranty period ends; the
            // required percentage is set by the Project Coordinator based on
            // the nature/volume of the goods or works; for items with no
            // warranty, it's returned once terms are met and final payment
            // is made; management may relax or waive it. This mirrors the
            // "Performance Security" language used in tender documents (e.g.
            // 2% held for 90 days after PO completion) — one concept,
            // tracked here per awarded contract (only the winner has one).
            $table->boolean('security_deposit_required')->default(false)->after('file_path');
            $table->decimal('security_deposit_amount', 12, 2)->nullable()->after('security_deposit_required');
            $table->decimal('security_deposit_percentage', 5, 2)->nullable()->after('security_deposit_amount');
            $table->enum('security_deposit_status', ['held', 'returned', 'waived'])
                ->default('held')->after('security_deposit_percentage');
            $table->date('warranty_period_ends_at')->nullable()->after('security_deposit_status');
            $table->date('security_deposit_returned_at')->nullable()->after('warranty_period_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('contract_awards', function (Blueprint $table) {
            $table->dropColumn([
                'security_deposit_required',
                'security_deposit_amount',
                'security_deposit_percentage',
                'security_deposit_status',
                'warranty_period_ends_at',
                'security_deposit_returned_at',
            ]);
        });
    }
};
