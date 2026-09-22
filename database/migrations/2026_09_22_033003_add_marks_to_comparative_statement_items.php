<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comparative_statement_items', function (Blueprint $table) {
            $table->foreignId('financial_evaluation_item_id')->nullable()->after('vendor_id')->constrained('financial_evaluation_items')->nullOnDelete();
            $table->foreignId('technical_evaluation_item_id')->nullable()->after('financial_evaluation_item_id')->constrained('technical_evaluation_items')->nullOnDelete();
            $table->decimal('financial_marks', 6, 2)->nullable();
            $table->decimal('technical_marks', 6, 2)->nullable();
            $table->decimal('total_marks', 6, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('comparative_statement_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('financial_evaluation_item_id');
            $table->dropConstrainedForeignId('technical_evaluation_item_id');
            $table->dropColumn(['financial_marks', 'technical_marks', 'total_marks']);
        });
    }
};