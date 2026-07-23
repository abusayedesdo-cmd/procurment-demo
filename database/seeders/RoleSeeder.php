<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::ROLE_LABELS as $name => $label) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}