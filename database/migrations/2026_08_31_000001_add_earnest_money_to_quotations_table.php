<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // ESDO Procurement Policy §24 (Earnest Money): required for
            // purchases through enlistment or Open Tendering Method (OTM);
            // refundable to unsuccessful bidders after the procurement
            // completes; forfeited or adjusted if the bidder violates the
            // tender's terms; generally not required for services. Tracked
            // per bidder (per Quotation), since each vendor submits and may
            // forfeit their own Earnest Money.
            $table->boolean('earnest_money_required')->default(false)->after('opening_remarks');
            $table->decimal('earnest_money_amount', 12, 2)->nullable()->after('earnest_money_required');
            $table->enum('earnest_money_status', ['not_required', 'held', 'refunded', 'forfeited'])
                ->default('not_required')->after('earnest_money_amount');
            $table->text('earnest_money_notes')->nullable()->after('earnest_money_status');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'earnest_money_required',
                'earnest_money_amount',
                'earnest_money_status',
                'earnest_money_notes',
            ]);
        });
    }
};
