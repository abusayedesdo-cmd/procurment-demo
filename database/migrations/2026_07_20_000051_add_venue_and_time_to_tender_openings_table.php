<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tender_openings', function (Blueprint $table) {
            $table->string('venue')->nullable()->after('opening_date');
            $table->time('opening_time')->nullable()->after('venue');
        });
    }

    public function down(): void
    {
        Schema::table('tender_openings', function (Blueprint $table) {
            $table->dropColumn(['venue', 'opening_time']);
        });
    }
};
