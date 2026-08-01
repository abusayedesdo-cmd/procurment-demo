<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_committee_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation'); // e.g. "Sr. Procurement Manager"
            $table->enum('role', ['convener', 'member', 'member_secretary']);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_committee_members');
    }
};
