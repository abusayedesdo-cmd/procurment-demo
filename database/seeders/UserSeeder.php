<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * One demo login per role. Password for all: "password"
     * (change these before deploying anywhere real).
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Nusrat Jahan',      'email' => 'requester@esdo.net.bd',   'role' => User::REQUESTER],
            ['name' => 'Tanvir Hasan',      'email' => 'reviewer@esdo.net.bd',    'role' => User::REVIEWER],
            ['name' => 'Sharmin Akter',     'email' => 'budget@esdo.net.bd',      'role' => User::BUDGET_CHECKER],
            ['name' => 'Md. Rafiqul Islam', 'email' => 'approver@esdo.net.bd',    'role' => User::APPROVER],
            ['name' => 'Farzana Yasmin',    'email' => 'focal@esdo.net.bd',       'role' => User::FOCAL_PERSON],
            ['name' => 'Dr. Shafiqur Rahman', 'email' => 'ed@esdo.net.bd',        'role' => User::EXECUTIVE_DIRECTOR],
            ['name' => 'Abdul Karim',       'email' => 'procurement@esdo.net.bd', 'role' => User::PROCUREMENT_OFFICER],
            ['name' => 'System Admin',      'email' => 'admin@esdo.net.bd',       'role' => User::ADMIN],
        ];

        foreach ($users as $u) {
            $roleId = Role::where('name', $u['role'])->value('id');

            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role_id' => $roleId,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }
    }
}