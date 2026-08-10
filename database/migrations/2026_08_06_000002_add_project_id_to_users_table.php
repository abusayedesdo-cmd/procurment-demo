<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable: existing users won't have a project assigned yet,
            // and this stays optional going forward (not every account —
            // e.g. a cross-project Admin — needs to sit under one project).
            $table->foreignId('project_id')->nullable()->after('role_id')
                ->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
