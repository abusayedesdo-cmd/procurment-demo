<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->enum('document_type', [
                'trade_license', 'vat_certificate', 'tax_certificate', 'psr',
                'professional_certificate', 'experience', 'bank_solvency',
                'manpower_list', 'machinery_capacity',
            ]);
            $table->string('file_path');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};
