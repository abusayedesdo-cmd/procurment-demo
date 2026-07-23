<?php

namespace Database\Seeders;

use App\Models\ProcurementCase;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Vendors ----
        $vendors = [
            ['name' => 'M/S Rahman Traders',           'type' => 'Goods supplier',   'address' => 'Tejgaon, Dhaka',  'docs' => [1,1,1,1,1], 'awards' => 6],
            ['name' => 'Dhaka Engineering Works Ltd.', 'type' => 'Works contractor', 'address' => 'Mirpur, Dhaka',   'docs' => [1,1,1,1,1], 'awards' => 3],
            ['name' => 'Green Bangla Enterprise',      'type' => 'Goods supplier',   'address' => 'Rangpur',         'docs' => [1,1,0,1,1], 'awards' => 2],
            ['name' => 'Innovision Consulting Ltd.',   'type' => 'Consultancy',      'address' => 'Banani, Dhaka',   'docs' => [1,1,1,1,1], 'awards' => 4],
            ['name' => 'Karim & Sons',                 'type' => 'Goods supplier',   'address' => 'Tangail',         'docs' => [1,0,1,0,1], 'awards' => 1],
            ['name' => 'Bhuiyan Construction',         'type' => 'Works contractor', 'address' => 'Kurigram',        'docs' => [1,1,1,1,0], 'awards' => 2],
        ];
        foreach ($vendors as $v) {
            Vendor::create([
                'name' => $v['name'], 'type' => $v['type'], 'address' => $v['address'],
                'trade_license' => $v['docs'][0], 'tin' => $v['docs'][1], 'psr' => $v['docs'][2],
                'bin' => $v['docs'][3], 'experience' => $v['docs'][4],
                'awards' => $v['awards'], 'last_participation' => now()->subDays(rand(10, 120)),
            ]);
        }

        // ---- Purchase requisitions ----
        $prs = [
            ['no' => 'PR-2026-041', 'title' => 'Office furniture for Tangail field office', 'project' => 'SHOUHARDO IV – Tangail', 'requestor' => 'Md. Rafiqul Islam', 'designation' => 'Project Coordinator', 'category' => 'Goods', 'stage' => 3,
             'items' => [['Executive desk (wooden, 5×3 ft)', 'Pcs', 6, 18500], ['Office chair (high-back)', 'Pcs', 12, 7200], ['Steel filing cabinet, 4-drawer', 'Pcs', 4, 14800]]],
            ['no' => 'PR-2026-042', 'title' => 'Deep tube-well installation, 4 sites', 'project' => 'WASH Project – Kurigram', 'requestor' => 'Sharmin Akter', 'designation' => 'WASH Engineer', 'category' => 'Works', 'stage' => 4,
             'items' => [['Deep tube-well installation incl. platform (per BOQ)', 'Site', 4, 185000]]],
            ['no' => 'PR-2026-043', 'title' => 'Baseline survey consultant', 'project' => 'Climate Resilience – Rangpur', 'requestor' => 'Tanvir Hasan', 'designation' => 'M&E Officer', 'category' => 'Services', 'stage' => 2,
             'items' => [['Baseline survey & report (per TOR)', 'LS', 1, 650000]]],
            ['no' => 'PR-2026-044', 'title' => 'Laptops & printers for MIS unit', 'project' => 'Head Office – MIS', 'requestor' => 'Nusrat Jahan', 'designation' => 'MIS Officer', 'category' => 'Goods', 'stage' => 1,
             'items' => [['Laptop, Core i5 / 16GB / 512GB SSD', 'Pcs', 8, 92000], ['Laser printer (duplex, network)', 'Pcs', 3, 38500]]],
            ['no' => 'PR-2026-045', 'title' => 'Training venue & catering, 3 batches', 'project' => 'Youth Skills – Dinajpur', 'requestor' => 'Abdul Karim', 'designation' => 'Training Officer', 'category' => 'Services', 'stage' => 0,
             'items' => [['Venue rent with logistics (per batch, 3 days)', 'Batch', 3, 45000], ['Catering (40 pax × 3 days per batch)', 'Batch', 3, 54000]]],
        ];
        foreach ($prs as $i => $p) {
            $pr = PurchaseRequisition::create([
                'pr_no' => $p['no'], 'title' => $p['title'], 'project' => $p['project'],
                'requestor' => $p['requestor'], 'designation' => $p['designation'],
                'category' => $p['category'], 'stage' => $p['stage'],
                'pr_date' => now()->subDays(26 - $i * 5), 'delivery_date' => now()->addDays(30 + $i * 15),
                'allocated_budget' => 1500000,
            ]);
            foreach ($p['items'] as $it) {
                $pr->items()->create(['name' => $it[0], 'unit' => $it[1], 'qty' => $it[2], 'rate' => $it[3]]);
            }
        }

        // ---- Procurement cases (23-step process) ----
        $cases = [
            ['ref' => 'ESDO/RFQ/26-018', 'title' => 'Motorcycles for field monitoring (5 units)',      'category' => 'Goods',    'method' => 'RFQ', 'amount' => 1125000, 'step' => 14],
            ['ref' => 'ESDO/RFT/26-007', 'title' => 'Cyclone shelter repair works – Bhola',            'category' => 'Works',    'method' => 'RFT', 'amount' => 4850000, 'step' => 8],
            ['ref' => 'ESDO/RFP/26-011', 'title' => 'Endline evaluation – Food Security Programme',    'category' => 'Services', 'method' => 'RFP', 'amount' => 980000,  'step' => 20],
            ['ref' => 'ESDO/RFQ/26-021', 'title' => 'Agricultural inputs (seed & fertilizer kits)',    'category' => 'Goods',    'method' => 'RFQ', 'amount' => 2340000, 'step' => 4],
        ];
        foreach ($cases as $c) {
            $case = ProcurementCase::create([
                'ref' => $c['ref'], 'title' => $c['title'], 'category' => $c['category'],
                'method' => $c['method'], 'amount' => $c['amount'], 'current_step' => $c['step'],
            ]);
            $case->seedSteps();
            $case->steps()->where('step_no', '<=', $c['step'])
                 ->update(['completed_at' => now()->subDays(rand(1, 30))]);
        }
    }
}
