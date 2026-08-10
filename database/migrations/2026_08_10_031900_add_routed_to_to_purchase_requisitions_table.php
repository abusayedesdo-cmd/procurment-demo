<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Lets the Budget Checker (Accounts) explicitly choose who the PR goes
     * to next — Focal Person or Executive Director — instead of that being
     * decided automatically from the PR amount (see
     * PrApprovalController::HIGH_VALUE_THRESHOLD, which is now only a
     * fallback for PRs checked before this column existed).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE purchase_requisitions ADD COLUMN routed_to ENUM('focal_person','executive_director') NULL AFTER status");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_requisitions DROP COLUMN routed_to");
    }
};