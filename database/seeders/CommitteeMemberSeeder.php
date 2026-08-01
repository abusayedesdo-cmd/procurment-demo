<?php

namespace Database\Seeders;

use App\Models\CommitteeMember;
use Illuminate\Database\Seeder;

class CommitteeMemberSeeder extends Seeder
{
    /**
     * PLACEHOLDER NAMES — replace with the actual constituted Procurement
     * Committee (5–7 members per policy) before this is used for real sittings.
     */
    public function run(): void
    {
        $members = [
            ['name' => 'Md. Rafiqul Islam',  'designation' => 'Chairperson',      'is_chair' => true],
            ['name' => 'Abdul Karim',        'designation' => 'Member Secretary', 'is_chair' => false],
            ['name' => 'Sharmin Akter',      'designation' => 'Member (Finance)', 'is_chair' => false],
            ['name' => 'Tanvir Hasan',       'designation' => 'Member',           'is_chair' => false],
            ['name' => 'Nusrat Jahan',       'designation' => 'Member',           'is_chair' => false],
        ];

        foreach ($members as $m) {
            CommitteeMember::updateOrCreate(
                ['name' => $m['name']],
                ['designation' => $m['designation'], 'is_chair' => $m['is_chair'], 'active' => true]
            );
        }
    }
}
