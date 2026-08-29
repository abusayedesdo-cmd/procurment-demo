<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_committee_members', function (Blueprint $table) {
            $table->string('committee_type')->default('central_procurement')->after('id');
            $table->string('branch')->nullable()->after('committee_type');
        });

        // The 3 existing rows (Delwar Islam / Kamal Hossain / Siraj Uddin)
        // already default to 'central_procurement' via the column default
        // above — nothing else to backfill there.

        // Seed the real Purchase Committee roster from the resolution
        // letter (ESDO ক্রয় কমিটি, resolution no. ৩১৪).
        DB::table('procurement_committee_members')->insert([
            ['name' => 'Md. Ainul Hoque', 'designation' => 'Convener, Purchase Committee', 'role' => 'convener', 'committee_type' => 'purchase_committee', 'branch' => null, 'sort_order' => 0, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Md. Majedul Islam', 'designation' => 'Member, Purchase Committee', 'role' => 'member', 'committee_type' => 'purchase_committee', 'branch' => null, 'sort_order' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Syed Mahbubul Alam', 'designation' => 'Member Secretary, Purchase Committee', 'role' => 'member_secretary', 'committee_type' => 'purchase_committee', 'branch' => null, 'sort_order' => 2, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('procurement_committee_members')->where('committee_type', 'purchase_committee')->delete();

        Schema::table('procurement_committee_members', function (Blueprint $table) {
            $table->dropColumn(['committee_type', 'branch']);
        });
    }
};
