<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The PR intake form (Section A) and its API controller have always sent
 * `project_name` and `delivery_location`, and the PurchaseRequisition model
 * lists both as fillable — but no migration ever actually created these
 * columns on `purchase_requisitions`. That's why the Committee Meeting
 * Notice/Attendance/Resolution documents show "N/A" for "Name of
 * Project/Program/Department" and its location: CommitteeDocumentText
 * reads these two columns straight off the linked PR.
 *
 * Guarded with hasColumn() so this is safe to run even on a database
 * where the columns were already added by hand outside of migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_requisitions', 'project_name')) {
                $table->string('project_name')->nullable()->after('category_id');
            }
            if (! Schema::hasColumn('purchase_requisitions', 'delivery_location')) {
                $table->string('delivery_location')->nullable()->after('estimated_delivery_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requisitions', 'project_name')) {
                $table->dropColumn('project_name');
            }
            if (Schema::hasColumn('purchase_requisitions', 'delivery_location')) {
                $table->dropColumn('delivery_location');
            }
        });
    }
};
