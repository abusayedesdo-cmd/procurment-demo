<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE purchase_requisitions MODIFY status ENUM('draft','reviewed','checked','focal_reviewed','approved','rejected') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        // A PR sitting at the new stage has been forwarded by the Budget
        // Checker but not yet reached the ED — closest fallback under the
        // old chain is 'checked' (awaiting final approval).
        DB::table('purchase_requisitions')->where('status', 'focal_reviewed')->update(['status' => 'checked']);
        DB::statement("ALTER TABLE purchase_requisitions MODIFY status ENUM('draft','reviewed','checked','approved','rejected') NOT NULL DEFAULT 'draft'");
    }
};
