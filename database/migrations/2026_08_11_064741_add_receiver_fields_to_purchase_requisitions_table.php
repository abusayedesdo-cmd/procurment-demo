<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->string('receiver_name')->nullable()->after('requestor_designation');
            $table->string('receiver_contact')->nullable()->after('receiver_name');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropColumn(['receiver_name', 'receiver_contact']);
        });
    }
};