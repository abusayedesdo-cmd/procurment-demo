<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('terms_accepted')->default(false)->after('opening_remarks');
            $table->boolean('delivery_terms_accepted')->default(false)->after('terms_accepted');
            $table->text('general_experience')->nullable()->after('delivery_terms_accepted');
            $table->text('relevant_experience')->nullable()->after('general_experience');
            $table->boolean('submitted_via_portal')->default(false)->after('relevant_experience');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted', 'delivery_terms_accepted', 'general_experience', 'relevant_experience', 'submitted_via_portal']);
        });
    }
};