<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_districts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        $districts = [
            'Bagerhat',
            'Bandarban',
            'Barguna',
            'Barishal',
            'Bhola',
            'Bogura',
            'Brahmanbaria',
            'Chandpur',
            'Chapainawabganj',
            'Chattogram',
            'Chuadanga',
            'Cox\'s Bazar',
            'Cumilla',
            'Dhaka',
            'Dinajpur',
            'Faridpur',
            'Feni',
            'Gaibandha',
            'Gazipur',
            'Gopalganj',
            'Habiganj',
            'Jamalpur',
            'Jashore',
            'Jhalokati',
            'Jhenaidah',
            'Joypurhat',
            'Khagrachhari',
            'Khulna',
            'Kishoreganj',
            'Kurigram',
            'Kushtia',
            'Lakshmipur',
            'Lalmonirhat',
            'Madaripur',
            'Magura',
            'Manikganj',
            'Meherpur',
            'Moulvibazar',
            'Munshiganj',
            'Mymensingh',
            'Naogaon',
            'Narail',
            'Narayanganj',
            'Narsingdi',
            'Natore',
            'Netrokona',
            'Nilphamari',
            'Noakhali',
            'Pabna',
            'Panchagarh',
            'Patuakhali',
            'Pirojpur',
            'Rajbari',
            'Rajshahi',
            'Rangamati',
            'Rangpur',
            'Satkhira',
            'Shariatpur',
            'Sherpur',
            'Sirajganj',
            'Sunamganj',
            'Sylhet',
            'Tangail',
            'Thakurgaon',
        ];

        $now = now();
        DB::table('procurement_districts')->insert(
            collect($districts)->map(fn ($name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_districts');
    }
};
