<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Real ESDO memo numbers (RFQ/NOA/Work Order/Tender Notice) share ONE
     * running sequence across the whole office per fiscal year — e.g.
     * .../126/652/2025-2026 and .../126/674/2025-2026 issued weeks apart,
     * regardless of document type. Rezulation (meeting minutes) numbers
     * are a separate running sequence. This table holds both kinds of
     * counters, keyed by a string so new counters can be added later
     * without a schema change.
     */
    public function up(): void
    {
        Schema::create('sequence_counters', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. 'memo_2025-2026', 'rezulation'
            $table->unsignedInteger('last_value')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_counters');
    }
};
