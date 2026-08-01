<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementCase extends Model
{
    protected $guarded = [];

    /** The 23-step ESDO procurement process. */
    public const STEPS = [
        ['no' => 1,  'name' => 'Approve Procurement Plan',                        'phase' => 'Planning'],
        ['no' => 2,  'name' => 'Approve Purchase Requisition',                    'phase' => 'Planning',              'detail' => 'Category A. Goods / B. Works / C. Services'],
        ['no' => 3,  'name' => 'RFQ / RFP / RFT issuing regulation',              'phase' => 'Solicitation',          'detail' => 'Goods → RFQ · Services → RFP · Works → RFT + BOQ'],
        ['no' => 4,  'name' => 'Main Tender Schedule / RFQ / RFP',                'phase' => 'Solicitation'],
        ['no' => 5,  'name' => 'Distribution list & e-mail document',             'phase' => 'Solicitation',          'detail' => 'RFQ: specification · RFP: TOR · RFT: BOQ, drawing & design'],
        ['no' => 6,  'name' => 'All quotations received',                         'phase' => 'Solicitation'],
        ['no' => 7,  'name' => 'Tender Opening Form',                             'phase' => 'Opening & eligibility'],
        ['no' => 8,  'name' => 'Conflict of Interest declaration',                'phase' => 'Opening & eligibility', 'detail' => 'Signed by all procurement committee members'],
        ['no' => 9,  'name' => 'Document check — responsive / non-responsive',    'phase' => 'Opening & eligibility', 'detail' => 'Trade license, TIN, PSR, BIN, experience'],
        ['no' => 10, 'name' => 'Technical Evaluation score',                      'phase' => 'Evaluation'],
        ['no' => 11, 'name' => 'Regulation of Technical Evaluation score',        'phase' => 'Evaluation'],
        ['no' => 12, 'name' => 'Financial Evaluation score',                      'phase' => 'Evaluation'],
        ['no' => 13, 'name' => 'Regulation of Financial Evaluation score',        'phase' => 'Evaluation'],
        ['no' => 14, 'name' => 'Comparative Statement (technical + financial)',   'phase' => 'Evaluation'],
        ['no' => 15, 'name' => 'Regulation of Comparative Statement',             'phase' => 'Evaluation'],
        ['no' => 16, 'name' => 'NOA / Work Order regulation',                     'phase' => 'Award'],
        ['no' => 17, 'name' => 'Notification of Award (NOA)',                     'phase' => 'Award'],
        ['no' => 18, 'name' => 'Pay Order',                                       'phase' => 'Award'],
        ['no' => 19, 'name' => 'Agreement',                                       'phase' => 'Contract & delivery'],
        ['no' => 20, 'name' => 'Work Order',                                      'phase' => 'Contract & delivery'],
        ['no' => 21, 'name' => 'Challan',                                         'phase' => 'Contract & delivery'],
        ['no' => 22, 'name' => 'Bill',                                            'phase' => 'Contract & delivery'],
        ['no' => 23, 'name' => 'Procurement Report',                              'phase' => 'Reporting'],
    ];

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    /** "Quotation" (RFQ/RFP/RFT) vs "Open Tender Method" — recorded at case creation. */
    public function natureLabel(): string
    {
        return $this->is_otm ? 'Open Tender Method (OTM)' : 'Quotation (' . $this->method . ')';
    }

    public function steps(): HasMany
    {
        return $this->hasMany(CaseStep::class)->orderBy('step_no');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function progressPct(): int
    {
        return (int) round($this->current_step / 23 * 100);
    }

    public function currentStepName(): string
    {
        return self::STEPS[min($this->current_step, 22)]['name'];
    }

    /** Seed the 23 step rows for a new case. */
    public function seedSteps(): void
    {
        foreach (self::STEPS as $s) {
            $this->steps()->create([
                'step_no' => $s['no'],
                'name'    => $s['name'],
                'phase'   => $s['phase'],
                'detail'  => $s['detail'] ?? null,
            ]);
        }
    }
}
