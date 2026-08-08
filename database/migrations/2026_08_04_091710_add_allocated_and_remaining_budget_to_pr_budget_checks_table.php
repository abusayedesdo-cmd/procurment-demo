<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pr_budget_checks', function (Blueprint $table) {
            // These three snapshot what the accountant actually typed/confirmed
            // on the Budgetary Check form — they may be edited away from the
            // budget line's live figures, so they're stored separately from
            // available_budget_amount (which stays as the live-balance snapshot).
            $table->decimal('allocated_budget', 15, 2)->nullable()->after('available_budget_amount');
            $table->decimal('remaining_budget_bf', 15, 2)->nullable()->after('allocated_budget');
            $table->decimal('remaining_budget_cf', 15, 2)->nullable()->after('remaining_budget_bf');
        });
    }

    public function down(): void
    {
        Schema::table('pr_budget_checks', function (Blueprint $table) {
            $table->dropColumn(['allocated_budget', 'remaining_budget_bf', 'remaining_budget_cf']);
        });
    }
};