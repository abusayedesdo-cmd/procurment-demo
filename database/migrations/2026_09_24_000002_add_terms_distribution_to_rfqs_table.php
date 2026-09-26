<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RFQ form already had "Terms & Conditions" and "Distribution Process"
 * inputs, but the columns never existed, so the values were silently
 * dropped on save. Guarded with hasColumn() in case someone already
 * added them by hand in phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            if (! Schema::hasColumn('rfqs', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('closing_date');
            }
            if (! Schema::hasColumn('rfqs', 'distribution_process')) {
                $table->string('distribution_process')->nullable()->after('terms_conditions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['terms_conditions', 'distribution_process'],
                fn ($c) => Schema::hasColumn('rfqs', $c)
            ));
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
