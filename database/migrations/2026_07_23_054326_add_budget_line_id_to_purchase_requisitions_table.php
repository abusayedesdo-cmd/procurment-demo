<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->foreignId('budget_line_id')->nullable()->after('category_id')
                ->constrained('budget_lines')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('budget_line_id');
        });
    }
};