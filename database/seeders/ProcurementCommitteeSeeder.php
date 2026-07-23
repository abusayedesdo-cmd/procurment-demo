<?php

namespace Database\Seeders;

use App\Models\ProcurementCommitteeMember;
use Illuminate\Database\Seeder;

class ProcurementCommitteeSeeder extends Seeder
{
    /**
     * Roster as it appears in real ESDO meeting minutes / NOA / Work Order
     * documents (2026). Update via Settings → Committee if membership changes.
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'Md. Delwar Islam', 'designation' => 'Convener',                       'role' => 'convener',         'sort_order' => 1],
            ['name' => 'Md. Kamal Hossain', 'designation' => 'Member',                         'role' => 'member',           'sort_order' => 2],
            ['name' => 'Md. Siraj Uddin',   'designation' => 'Sr. Procurement Manager',        'role' => 'member_secretary', 'sort_order' => 3],
        ];

        foreach ($rows as $r) {
            ProcurementCommitteeMember::updateOrCreate(
                ['name' => $r['name'], 'role' => $r['role']],
                $r + ['active' => true]
            );
        }
    }
}
