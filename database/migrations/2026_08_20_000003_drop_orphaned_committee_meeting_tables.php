<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The Notice/Attendance/Minutes flow was rebuilt on `meetings` (belongs
 * to procurement_cases directly, meeting_type first/second) instead of
 * `committee_meetings`. These tables — and the controller/model/view
 * code that used them — are now dead. Drop them to stop the confusion
 * of two parallel, same-purpose systems in the schema.
 *
 * Order matters: committee_declarations and committee_meeting_case both
 * hold FKs into committee_meetings, so they must go first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('committee_declarations');
        Schema::dropIfExists('committee_meeting_case');
        Schema::dropIfExists('committee_meetings');
    }

    public function down(): void
    {
        // Deliberately not recreated — see database/migrations for the
        // original create_committee_meetings_table / create_committee_
        // meeting_case_table / create_committee_declarations_table
        // migrations if this ever needs to be resurrected.
    }
};
