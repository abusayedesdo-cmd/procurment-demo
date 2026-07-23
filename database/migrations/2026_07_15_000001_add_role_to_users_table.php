<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // requester        -> Requester (raises PRs)
            // reviewer         -> Department Manager, review stage
            // budget_checker   -> Department Manager, accounts/budget stage
            // approver         -> Department Manager, approve stage
            // procurement_officer -> Procurement Officer (runs cases, vendors, plan)
            // admin            -> Admin (full access + policy config)
            $table->enum('role', [
                'requester',
                'reviewer',
                'budget_checker',
                'approver',
                'procurement_officer',
                'admin',
            ])->default('requester')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
