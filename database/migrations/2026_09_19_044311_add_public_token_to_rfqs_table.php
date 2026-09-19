<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('public_token', 40)->nullable()->unique()->after('id');
        });

        // পুরনো RFQ-গুলোতেও টোকেন বসিয়ে দেওয়া, যাতে সেগুলোরও ভেন্ডর লিংক কাজ করে।
        DB::table('rfqs')->whereNull('public_token')->orderBy('id')->get()->each(function ($rfq) {
            DB::table('rfqs')->where('id', $rfq->id)->update(['public_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};