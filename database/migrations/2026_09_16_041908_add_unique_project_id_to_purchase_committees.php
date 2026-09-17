<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // এক প্রজেক্টে একটাই কমিটি — Main/Central কমিটির project_id সবসময় null
        // থাকে (MySQL unique index-এ একাধিক NULL সমস্যা করে না), তাই এটা কার্যত
        // শুধু Sub-Committee-কেই কনস্ট্রেইন করে — একই প্রজেক্টে দুইটা তৈরি হবে না।
        Schema::table('purchase_committees', function (Blueprint $table) {
            $table->unique('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_committees', function (Blueprint $table) {
            $table->dropUnique(['project_id']);
        });
    }
};