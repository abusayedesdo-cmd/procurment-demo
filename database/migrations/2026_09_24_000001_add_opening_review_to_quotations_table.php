<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Step 11 (Quotations Receiving/Opening Report): after the opening, the
 * committee either "Forwards for Evaluation" or "Rejects" each quotation.
 *
 * Raw ALTER MODIFY is used for the enum (same approach as the
 * committee_members migration) so doctrine/dbal isn't needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY status ENUM('received','opened','evaluated','disqualified','forwarded','rejected') NOT NULL DEFAULT 'received'");

        Schema::table('quotations', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('opening_remarks');
            $table->foreignId('reviewed_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['rejection_reason', 'reviewed_at']);
        });

        // Map the new values back before shrinking the enum, or MySQL errors out.
        DB::table('quotations')->where('status', 'forwarded')->update(['status' => 'opened']);
        DB::table('quotations')->where('status', 'rejected')->update(['status' => 'disqualified']);

        DB::statement("ALTER TABLE quotations MODIFY status ENUM('received','opened','evaluated','disqualified') NOT NULL DEFAULT 'received'");
    }
};
