<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which ESDO office (Dhaka / Thakurgaon — see config/esdo.php) a Main-committee document is issued
 * from. Null = use the committee's own saved address, exactly as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['meetings', 'rfqs'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'office')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('office', 30)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['meetings', 'rfqs'] as $table) {
            if (Schema::hasColumn($table, 'office')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('office');
                });
            }
        }
    }
};
