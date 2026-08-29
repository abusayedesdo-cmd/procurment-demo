<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Drop the old plan-based link + raw sequence number.
            $table->dropForeign(['procurement_plan_id']);
            $table->dropColumn(['procurement_plan_id', 'meeting_sequence']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('procurement_case_id')->nullable()->after('id')
                ->constrained('procurement_cases')->cascadeOnDelete();

            $table->enum('meeting_type', ['first', 'second'])->nullable()->after('procurement_case_id');

            // Rezulation/Minutes — separate running counter, filled in when the
            // meeting is held and the minutes are finalized (not at creation).
            $table->unsignedInteger('rezulation_no')->nullable()->unique()->after('meeting_type');

            $table->string('location')->nullable()->after('meeting_date');
            $table->string('meeting_time')->nullable()->after('location');

            $table->date('notice_date')->nullable()->after('notice_number');

            // Case-specific agenda text — "Notice Agenda No.-1" in the paper form.
            $table->text('agenda')->nullable();

            // 1st-meeting tender schedule decision block.
            $table->date('publish_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->date('opening_date')->nullable();
            $table->string('schedule_override_reason')->nullable();

            // Free-form extra decision notes (rarely needed — most of the
            // Rezulation/Minutes text is generated from the fields above).
            $table->text('decisions')->nullable();

            // Generated document paths.
            $table->string('attendance_file')->nullable();
            $table->string('minutes_file')->nullable();

            // Set once the minutes are approved/finalized (rezulation_no + this
            // both get filled together).
            $table->timestamp('held_at')->nullable();

            $table->string('attendance_number')->nullable()->unique()->after('notice_date');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->renameColumn('created_by', 'recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->renameColumn('recorded_by', 'created_by');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['procurement_case_id']);
            $table->dropColumn([
                'procurement_case_id', 'meeting_type', 'rezulation_no',
                'location', 'meeting_time', 'notice_date', 'attendance_number', 'agenda',
                'publish_date', 'closing_date', 'opening_date',
                'schedule_override_reason', 'decisions',
                'attendance_file', 'minutes_file', 'held_at',
            ]);
            $table->foreignId('procurement_plan_id')->nullable()->constrained('procurement_plans')->nullOnDelete();
            $table->unsignedInteger('meeting_sequence')->nullable();
        });
    }
};
