<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Each meeting now remembers WHICH committee it is for (Main / a project's
 * Sub-Committee). The notice e-mail, the Attendance page and the attendance
 * e-mail all read it, instead of guessing from the case every time.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Safe to run again / after the column was already added by hand or by another copy of this migration.
        if (! Schema::hasColumn('meetings', 'committee_id')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->foreignId('committee_id')->nullable()->after('procurement_case_id')
                    ->constrained('purchase_committees')->nullOnDelete();
            });
        }

        // Old meetings: the committee that holds the case now (latest sub-committee
        // transfer of its plan), else the Main committee.
        DB::statement("
            UPDATE meetings m
            JOIN procurement_cases c ON c.id = m.procurement_case_id
            JOIN procurement_plans p ON p.pr_id = c.purchase_requisition_id
            SET m.committee_id = (
                SELECT t.to_committee_id FROM sub_committee_transfers t
                WHERE t.procurement_plan_id = p.id
                ORDER BY t.transfer_date DESC, t.id DESC LIMIT 1
            )
            WHERE m.committee_id IS NULL
        ");

        DB::statement("
            UPDATE meetings
            SET committee_id = (SELECT id FROM purchase_committees WHERE type = 'main' ORDER BY id LIMIT 1)
            WHERE committee_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('committee_id');
        });
    }
};