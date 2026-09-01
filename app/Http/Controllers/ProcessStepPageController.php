<?php

namespace App\Http\Controllers;

class ProcessStepPageController extends Controller
{
    /**
     * Step -> Subject -> module mapping, derived from
     * "7. Process Action Windows Mapping.docx". Steps 1st/2nd are handled
     * by the existing Purchase Requisition / Case-creation pages, not
     * shown here. Each module slug/title pair links to the existing
     * generic /modules/{slug} page; 'route' overrides that for steps
     * whose real UI lives elsewhere (the Case-based meeting flow).
     */
    public const STEPS = [
        'sub-committee' => [
            'step_no' => '3rd',
            'subject' => 'Sub-Committee',
            'modules' => [
                ['slug' => 'purchase-committees', 'title' => 'Committees (create Dhaka, Thakurgaon, etc.)'],
                ['slug' => 'committee-members', 'title' => 'Committee Members'],
                ['slug' => 'sub-committee-transfers', 'title' => 'Sub-Committee Transfer'],
            ],
        ],
        'meeting-notice' => [
            'step_no' => '4th',
            'subject' => '1st Meeting Notice',
            'modules' => [
                ['route' => 'cases.index', 'title' => 'Open a Case \u2192 1st/2nd Meeting Notice'],
            ],
        ],
        'meeting-attendance' => [
            'step_no' => '5th',
            'subject' => '1st Meeting Attendance',
            'modules' => [
                ['route' => 'cases.index', 'title' => 'Open a Case \u2192 Meeting Attendance'],
            ],
        ],
        'meeting-resolution' => [
            'step_no' => '6th',
            'subject' => '1st Meeting Resolution',
            'modules' => [
                ['route' => 'cases.index', 'title' => 'Open a Case \u2192 Meeting Resolution'],
            ],
        ],
        'rfq' => [
            'step_no' => '7th',
            'subject' => 'Request for Quotation (RFQ)',
            'modules' => [
                ['slug' => 'rfqs', 'title' => 'RFQ / OTM'],
                ['slug' => 'tender-schedules', 'title' => 'Tender Schedule (Goods/Works)'],
                ['slug' => 'tender-proposals', 'title' => 'Tender Proposal (Professional Service)'],
            ],
        ],
        'rfp-rfi' => [
            'step_no' => '8th',
            'subject' => 'RFP/RFI/Hiring Vendor/Consultant',
            'modules' => [],
            'coming_soon' => true,
        ],
        'tender-otm' => [
            'step_no' => '9th',
            'subject' => 'Tender Schedule/OTM/Press Tender/STD',
            'modules' => [
                ['slug' => 'tender-advertisements', 'title' => 'Tender Advertisement'],
            ],
        ],
        'quotations-drop' => [
            'step_no' => '10th',
            'subject' => 'Quotations Drop by Vendor',
            'modules' => [
                ['slug' => 'quotations', 'title' => 'Quotations Received'],
                ['slug' => 'vendors', 'title' => 'Vendors'],
            ],
        ],
        'quotations-opening' => [
            'step_no' => '11th',
            'subject' => 'Quotations Receiving/Opening Report',
            'modules' => [
                ['slug' => 'tender-openings', 'title' => 'Tender Opening Report'],
            ],
        ],
        'quotations-evaluation' => [
            'step_no' => '12th',
            'subject' => 'Quotations Evaluation',
            'modules' => [
                ['slug' => 'eligibility-reports', 'title' => 'Eligibility Report (ER)'],
                ['slug' => 'eligibility-report-items', 'title' => 'Eligibility Report — Vendor Result'],
                ['slug' => 'technical-evaluation-reports', 'title' => 'Technical Evaluation Report (TER)'],
                ['slug' => 'technical-evaluation-items', 'title' => 'Technical Evaluation — Vendor Score'],
                ['slug' => 'financial-evaluation-reports', 'title' => 'Financial Evaluation Report (FER)'],
                ['slug' => 'financial-evaluation-items', 'title' => 'Financial Evaluation — Vendor Amount'],
                ['slug' => 'comparative-statements', 'title' => 'Comparative Statement (CS)'],
                ['slug' => 'comparative-statement-items', 'title' => 'Comparative Statement — Vendor Ranking'],
                ['slug' => 'contract-awards', 'title' => 'Notification of Contract Award (NOA)'],
                ['slug' => 'pay-orders', 'title' => 'Pay Order'],
                ['slug' => 'contract-agreements', 'title' => 'Contract Agreement'],
                ['slug' => 'work-orders', 'title' => 'Work Order'],
                ['slug' => 'delivery-receipts', 'title' => 'Delivery Received'],
            ],
        ],
    ];

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::STEPS), 404);

        $step = self::STEPS[$slug];
        $cases = null;

        $usesCasesFlow = collect($step['modules'])->contains(fn ($m) => ($m['route'] ?? null) === 'cases.index');
        if ($usesCasesFlow) {
            $cases = \App\Models\ProcurementCase::latest()->get();
        }

        return view('process-steps.show', [
            'slug' => $slug,
            'step' => $step,
            'cases' => $cases,
        ]);
    }
}