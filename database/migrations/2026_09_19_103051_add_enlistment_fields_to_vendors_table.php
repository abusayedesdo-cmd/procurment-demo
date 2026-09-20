<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('owner_name')->nullable()->after('contact_person');
            $table->string('bank_account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_address')->nullable();
            $table->string('group_subcategory')->nullable();
            // ESDO Procurement Policy §12 — Vendor Enlistment status.
            $table->enum('enlistment_status', ['not_enlisted', 'applied', 'enlisted'])->default('not_enlisted');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name', 'bank_account_name', 'bank_name', 'bank_account_number',
                'bank_address', 'group_subcategory', 'enlistment_status',
            ]);
        });
    }
};