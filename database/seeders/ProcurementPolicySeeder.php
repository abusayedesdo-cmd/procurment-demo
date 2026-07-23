<?php

namespace Database\Seeders;

use App\Models\ProcurementPolicy;
use Illuminate\Database\Seeder;

class ProcurementPolicySeeder extends Seeder
{
    /**
     * PLACEHOLDER VALUES — these are not confirmed ESDO policy figures.
     * They only preserve the day-offsets that were previously hardcoded in
     * ProcurementCaseController::plan(), plus reasonable round-number amount
     * thresholds so the RFQ/OTM split has something to compute against.
     * An Admin should update these to ESDO's actual procurement policy via
     * Settings → Procurement Policy before this is used for real decisions.
     */
    public function run(): void
    {
        $rows = [
            // Amount thresholds: below = Quotation (RFQ/RFP), at/above = Open Tender Method (OTM)
            [ProcurementPolicy::THRESHOLD_GOODS,    'Goods: RFQ below this amount, OTM at/above',    'Threshold', 300000, 'BDT'],
            [ProcurementPolicy::THRESHOLD_WORKS,    'Works: RFQ below this amount, OTM at/above',    'Threshold', 500000, 'BDT'],
            [ProcurementPolicy::THRESHOLD_SERVICES, 'Services: RFP below this amount, OTM at/above', 'Threshold', 300000, 'BDT'],

            // Milestone day-offsets, counted from PR date
            [ProcurementPolicy::OFFSET_PUBLISH,     'Advertise / publish',   'Milestone offset', 7,  'days'],
            [ProcurementPolicy::OFFSET_CLOSING,     'Submission closing',    'Milestone offset', 21, 'days'],
            [ProcurementPolicy::OFFSET_OPENING,     'Opening',               'Milestone offset', 22, 'days'],
            [ProcurementPolicy::OFFSET_EVALUATION,  'Evaluation',            'Milestone offset', 29, 'days'],
            [ProcurementPolicy::OFFSET_NOA,         'NOA issued',            'Milestone offset', 33, 'days'],
            [ProcurementPolicy::OFFSET_CONTRACT,    'Contract signing',      'Milestone offset', 40, 'days'],
            [ProcurementPolicy::OFFSET_WORK_ORDER,  'Work order',            'Milestone offset', 42, 'days'],
            [ProcurementPolicy::OFFSET_DELIVERY,    'Delivery (fallback, if no PR delivery date)', 'Milestone offset', 60, 'days'],
        ];

        foreach ($rows as [$key, $label, $group, $value, $unit]) {
            ProcurementPolicy::updateOrCreate(
                ['key' => $key],
                ['label' => $label, 'group' => $group, 'value' => $value, 'unit' => $unit]
            );
        }
    }
}
