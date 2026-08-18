<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fixes found while wiring the Notice/Attendance/Minutes document flow:
 *
 *  1. committee_declarations.committee_member_id was constrained against
 *     `committee_members` (the old PurchaseCommittee/user-tied roster),
 *     but every controller/view/seeder in this module actually reads the
 *     standalone `procurement_committee_members` roster (name,
 *     designation, role, active — the one matching real ESDO documents,
 *     e.g. "Md. Delwar Islam / Convener"). Re-point the FK so it resolves.
 *
 *  2. Adds the fields needed to print the Notice, Attendance and
 *     Minutes/Rezulation documents: meeting location/time, the three
 *     auto-generated document numbers + their issue timestamps.
 *
 *  3. Adds per-agenda-item fields (on the meeting/case pivot) needed for
 *     the tender-schedule paragraph in the Minutes doc: the committee's
 *     chosen notice period and an optional justification note for a
 *     shortened period.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Re-point committee_declarations to procurement_committee_members.
        Schema::table('committee_declarations', function (Blueprint $table) {
            $table->dropForeign(['committee_member_id']);
        });

        // Drop rows that can't be remapped (declarations against the old,
        // unrelated committee_members table) — this feature never worked
        // end-to-end, so there is no real data to preserve here.
        DB::table('committee_declarations')->truncate();

        Schema::table('committee_declarations', function (Blueprint $table) {
            $table->foreign('committee_member_id')
                ->references('id')->on('procurement_committee_members')
                ->cascadeOnDelete();
        });

        // 2. Document fields on committee_meetings.
        Schema::table('committee_meetings', function (Blueprint $table) {
            $table->string('location')->nullable()->after('meeting_date');
            $table->string('meeting_time')->nullable()->after('location');

            $table->string('notice_number')->nullable()->unique()->after('meeting_time');
            $table->timestamp('notice_generated_at')->nullable()->after('notice_number');

            $table->string('attendance_number')->nullable()->unique()->after('notice_generated_at');
            $table->timestamp('attendance_generated_at')->nullable()->after('attendance_number');

            $table->unsignedInteger('minutes_number')->nullable()->unique()->after('attendance_generated_at');
            $table->timestamp('minutes_generated_at')->nullable()->after('minutes_number');
        });

        // 3. Per-agenda-item tender schedule fields.
        Schema::table('committee_meeting_case', function (Blueprint $table) {
            $table->unsignedTinyInteger('notice_period_days')->nullable()->after('agenda_type');
            $table->string('special_note')->nullable()->after('notice_period_days');
            $table->date('publish_date')->nullable()->after('special_note');
            $table->string('publish_channel')->nullable()->after('publish_date');
        });
    }

    public function down(): void
    {
        Schema::table('committee_meeting_case', function (Blueprint $table) {
            $table->dropColumn(['notice_period_days', 'special_note', 'publish_date', 'publish_channel']);
        });

        Schema::table('committee_meetings', function (Blueprint $table) {
            $table->dropColumn([
                'location', 'meeting_time',
                'notice_number', 'notice_generated_at',
                'attendance_number', 'attendance_generated_at',
                'minutes_number', 'minutes_generated_at',
            ]);
        });

        Schema::table('committee_declarations', function (Blueprint $table) {
            $table->dropForeign(['committee_member_id']);
            $table->foreign('committee_member_id')
                ->references('id')->on('committee_members')
                ->cascadeOnDelete();
        });
    }
};
