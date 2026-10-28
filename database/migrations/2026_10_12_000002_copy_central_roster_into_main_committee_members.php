<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Main / Central committee members now live in committee_members like every
 * Sub-Committee (one place for all recipients). Copy today's active Central
 * Procurement roster into the Main committee ONCE.
 *
 * - Skipped if there is no Main committee, or if it already has members
 *   (someone set it up by hand - never overwrite that).
 * - user_id is left NULL on purpose: this is only for notices/attendance, so it
 *   must not change who can see or act on PRs (committee-scoped access keys off user_id).
 * - Roster role (Convener / Member / Member Secretary) becomes the member's
 *   designation_in_committee.
 */
return new class extends Migration
{
    public function up(): void
    {
        $main = DB::table('purchase_committees')->where('type', 'main')->orderBy('id')->first();
        if (! $main) {
            return;
        }

        if (DB::table('committee_members')->where('committee_id', $main->id)->exists()) {
            return;
        }

        $labels = ['convener' => 'Convener', 'member' => 'Member', 'member_secretary' => 'Member Secretary'];

        $roster = DB::table('procurement_committee_members')
            ->where('active', 1)
            ->where('committee_type', 'central_procurement')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        foreach ($roster as $r) {
            DB::table('committee_members')->insert([
                'committee_id' => $main->id,
                'user_id' => null,
                'procurement_committee_member_id' => $r->id,
                'designation_in_committee' => $labels[$r->role] ?? $r->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Data copy only - nothing safe to undo automatically.
    }
};
