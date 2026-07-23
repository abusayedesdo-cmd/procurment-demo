<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Item;
use App\Models\ProcurementCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Units ----
        $units = [
            ['name' => 'Piece', 'symbol' => 'pcs'],
            ['name' => 'Set', 'symbol' => 'set'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Litre', 'symbol' => 'ltr'],
            ['name' => 'Ream', 'symbol' => 'ream'],
            ['name' => 'Lump Sum', 'symbol' => 'LS'],
            ['name' => 'Man-Day', 'symbol' => 'MD'],
            ['name' => 'Square Feet', 'symbol' => 'sft'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name']], $u);
        }

        // ---- Categories -> Chart of Accounts -> Items ----
        $structure = [
            'Goods' => [
                'IT & Office Equipment' => [
                    ['code' => 'COA-1001', 'items' => ['Laptop (Core i5, 16GB RAM, 512GB SSD)', 'Desktop Computer', 'Laser Printer (Duplex, Network)', 'External Hard Drive 1TB', 'UPS 650VA']],
                    ['code' => 'COA-1002', 'items' => ['Executive Desk (Wooden, 5x3 ft)', 'Office Chair (High-back)', 'Steel Filing Cabinet, 4-drawer', 'Conference Table']],
                ],
                'Vehicles' => [
                    ['code' => 'COA-1101', 'items' => ['Motorcycle (100cc)', 'Pickup Van', 'Bicycle']],
                ],
                'Stationery & Supplies' => [
                    ['code' => 'COA-1201', 'items' => ['A4 Paper (Ream)', 'Printer Toner Cartridge', 'File Folder', 'Whiteboard Marker (Box)']],
                ],
            ],
            'Works' => [
                'Civil Construction' => [
                    ['code' => 'COA-2001', 'items' => ['Deep Tube-well Installation (incl. platform)', 'Cyclone Shelter Repair (per BOQ)', 'Latrine Construction (per unit)', 'Road/Culvert Repair (per BOQ)']],
                ],
                'Renovation' => [
                    ['code' => 'COA-2101', 'items' => ['Office Renovation (per sft)', 'Electrical Rewiring (per point)', 'Plumbing Work (per BOQ)']],
                ],
            ],
            'Service' => [
                'Consultancy' => [
                    ['code' => 'COA-3001', 'items' => ['Baseline Survey & Report (per TOR)', 'Endline Evaluation (per TOR)', 'Financial Audit Service', 'Training Facilitation (per batch)']],
                ],
                'Event & Logistics' => [
                    ['code' => 'COA-3101', 'items' => ['Venue Rent with Logistics (per batch, 3 days)', 'Catering Service (per pax per day)', 'Transport Rental (per day)']],
                ],
            ],
        ];

        foreach ($structure as $categoryName => $accounts) {
            $category = ProcurementCategory::firstOrCreate(['name' => $categoryName]);

            foreach ($accounts as $accountName => $codedGroups) {
                foreach ($codedGroups as $group) {
                    $coa = ChartOfAccount::firstOrCreate(
                        ['code' => $group['code']],
                        ['category_id' => $category->id, 'name' => $accountName]
                    );

                    foreach ($group['items'] as $itemName) {
                        Item::firstOrCreate([
                            'chart_of_account_id' => $coa->id,
                            'name' => $itemName,
                        ]);
                    }
                }
            }
        }
    }
}
