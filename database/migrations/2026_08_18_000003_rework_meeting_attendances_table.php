<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('meeting_attendances', function (Blueprint $table) {
            $table->foreignId('committee_member_id')->nullable()->after('meeting_id')
                ->constrained('procurement_committee_members')->nullOnDelete();

            // Snapshot the name/designation at attendance time so the record
            // stays accurate even if the roster changes later.
            $table->string('name')->after('committee_member_id');
            $table->string('designation')->after('name');

            $table->string('remarks')->nullable()->after('signature_file');
            $table->unsignedTinyInteger('sort_order')->default(0)->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_attendances', function (Blueprint $table) {
            $table->dropForeign(['committee_member_id']);
            $table->dropColumn(['committee_member_id', 'name', 'designation', 'remarks', 'sort_order']);
            $table->foreignId('user_id')->constrained('users');
        });
    }
};
