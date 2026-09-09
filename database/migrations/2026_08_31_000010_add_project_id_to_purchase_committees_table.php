<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Policy §9 "Formation of Sub-Committees": sub-committees are formed
        // for remote or specific projects, while the central committee is
        // organization-wide. Nullable because only sub-committees carry a
        // project — main/central committees leave this null.
        Schema::table('purchase_committees', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('type')
                ->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_committees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
