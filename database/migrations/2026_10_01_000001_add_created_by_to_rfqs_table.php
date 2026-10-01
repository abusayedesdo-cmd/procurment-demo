<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who created the RFQ — printed as the signatory (name + designation) on the
     * RFQ and Tender Schedule documents. Older RFQs stay NULL and keep the
     * previous signatory (the committee's Member Secretary).
     */
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('finalized_by')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
