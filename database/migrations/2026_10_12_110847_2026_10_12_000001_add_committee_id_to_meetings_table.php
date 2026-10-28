<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A meeting now remembers WHICH committee it was called for (Main/Central or a
 * Sub-Committee), instead of working it out from the case every time. Older
 * meetings keep committee_id = NULL and are still resolved from their case.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('meetings', 'committee_id')) {
            return;
        }

        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignId('committee_id')
                ->nullable()
                ->after('procurement_case_id')
                ->constrained('purchase_committees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('committee_id');
        });
    }
};