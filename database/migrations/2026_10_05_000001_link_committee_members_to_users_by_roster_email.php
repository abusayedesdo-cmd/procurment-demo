<?php

use App\Models\CommitteeMember;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Committee members added from the roster without a login were saved with user_id = NULL.
     * If that roster person later got a login (same e-mail), the account was never linked, so the
     * system treated them as being in no committee and showed them every PR. Link them now.
     */
    public function up(): void
    {
        CommitteeMember::whereNull('user_id')
            ->whereNotNull('procurement_committee_member_id')
            ->with('procurementCommitteeMember')
            ->get()
            ->each(function (CommitteeMember $member) {
                $email = trim((string) $member->procurementCommitteeMember?->email);
                if ($email === '') {
                    return;
                }

                $userId = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->value('id');
                if (! $userId) {
                    return;
                }

                $alreadyThere = CommitteeMember::where('committee_id', $member->committee_id)
                    ->where('user_id', $userId)
                    ->exists();
                if (! $alreadyThere) {
                    $member->update(['user_id' => $userId]);
                }
            });
    }

    public function down(): void
    {
        // Data repair only — nothing to undo.
    }
};
