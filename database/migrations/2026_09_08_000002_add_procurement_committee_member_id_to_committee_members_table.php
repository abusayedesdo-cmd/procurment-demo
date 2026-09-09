<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sub-Committee members can now be picked either from real login Users
 * (as before) or straight from the procurement_committee_members roster
 * (the same names/designations used on meeting documents) — an Admin can
 * use either. Main/Central committee membership is unchanged and still
 * requires a login User (enforced in CommitteeMemberController, not here).
 *
 * Raw ALTER MODIFY is used instead of Schema::table()->change() so this
 * doesn't need doctrine/dbal added as a dependency.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE committee_members MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('committee_members', function (Blueprint $table) {
            $table->foreignId('procurement_committee_member_id')
                ->nullable()
                ->after('user_id')
                ->constrained('procurement_committee_members')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('committee_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('procurement_committee_member_id');
        });

        DB::statement('ALTER TABLE committee_members MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};
