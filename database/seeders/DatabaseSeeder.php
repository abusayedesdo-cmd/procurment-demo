<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(MasterDataSeeder::class);
        $this->call(ProcurementPolicySeeder::class);
        $this->call(SequenceCounterSeeder::class);

        // NOTE: ProcurementSeeder::class and ProcurementCommitteeSeeder::class
        // were removed from here — both still target tables/columns that no
        // longer exist after the schema redesign:
        //   - ProcurementSeeder      -> App\Models\ProcurementCase (table dropped),
        //                               old Vendor columns (type/trade_license/tin/...)
        //   - ProcurementCommitteeSeeder -> App\Models\ProcurementCommitteeMember
        //                               (table `procurement_committee_members` dropped;
        //                               replaced by purchase_committees + committee_members)
        // Running either now throws "table/column not found", same as the
        // earlier `role` issue. Delete the two seeder files, or ask me to
        // rewrite them against the new schema and I'll wire them back in.
    }
}
