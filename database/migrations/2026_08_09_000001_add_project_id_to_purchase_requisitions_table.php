<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('raised_by')
                ->constrained('projects')->nullOnDelete();
        });

        // Backfill existing PRs from the project of the user who raised them.
        DB::statement(<<<'SQL'
            UPDATE purchase_requisitions pr
            INNER JOIN users u ON u.id = pr.raised_by
            SET pr.project_id = u.project_id
            WHERE u.project_id IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
