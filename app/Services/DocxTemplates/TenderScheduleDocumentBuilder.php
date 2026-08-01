<?php

namespace App\Services\DocxTemplates;

use App\Models\Rfq;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the Tender Schedule document, matching the sample:
 * "5_Tender_Schedule_of_FRRP-Noakhali_for_Livelihood_Materials(In-kind).docx"
 *
 * Covers: Annex I (reference/schedule info), Annex II (price schedule,
 * auto-grouped by Chart of Account from the linked PR's items), Annex III
 * (technical evaluation sheet — standard 60/40 split, ESDO's fixed
 * criteria set), Annex IV (terms & conditions boilerplate).
 *
 * NOT auto-generated here (vendor-facing / signature-only forms with no
 * system data to fill): Annex V Submission Letter, Annex VI Contract
 * Agreement draft, Annex VII Declaration Form. Say the word if you want
 * these added as blank attachments too.
 *
 * Data gaps (same as the RFQ builder) — Project name, District, and a
 * few policy numbers (validity days, performance security %, delay
 * penalty %) aren't stored fields yet, so they're editable placeholders
 * or ESDO-standard defaults below. Move them into `procurement_policies`
 * if you want them configurable per-tender without editing this file.
 */
class TenderScheduleDocumentBuilder
{
    protected const VALIDITY_DAYS = 90;
    protected const PERFORMANCE_SECURITY_PERCENT = 5;
    protected const DELAY_PENALTY_PERCENT_PER_WEEK = 1;

    public function build(Rfq $rfq): PhpWord
    {
        $rfq->loadMissing(
            'procurementPlan.purchaseRequisition.items.item.chartOfAccount',
            'procurementPlan.purchaseRequisition.items.unit'
        );

        $pr = $rfq->procurementPlan?->purchaseRequisition;
        $items = $pr?->items ?? collect();
        $itemsByCategory = $items->groupBy(fn ($line) => $line->item->chartOfAccount->name ?? 'General');

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $bold = ['bold' => true];
        $center = ['alignment' => Jc::CENTER];
        $sectionStyle = ['marginLeft' => 1000, 'marginRight' => 1000, 'marginTop' => 1000, 'marginBottom' => 1000];

        // ================= ANNEX I — Schedule Info =================
        $section = $phpWord->addSection($sectionStyle);
        $section->addText("Reference: {$rfq->rfq_number}", $bold);
        $section->addText('Date: ' . optional($rfq->issue_date)->format('d.m.Y'));
        $section->addTextBreak(1);
        $section->addText('TENDER SCHEDULE', array_merge($bold, ['underline' => 'single', 'size' => 14]), $center);
        $section->addText('(' . $rfq->type . ')', $center);
        $section->addTextBreak(1);

        $infoTable = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
        $infoRows = [
            ['Procurement Reference', $rfq->rfq_number],
            ['Procurement Nature', $rfq->type],
            ['Date of Publication/Issue', optional($rfq->issue_date)->format('d F, Y')],
            ['Submission Deadline', optional($rfq->closing_date)->format('d F, Y, h:i A')],
            ['Tender Validity', self::VALIDITY_DAYS . ' days from the submission deadline'],
            ['Delivery Location', '[Project Office / District — fill in]'],
            ['Expected Delivery Date', optional($rfq->procurementPlan?->est_delivery_date)->format('d F, Y') ?: '[TBD]'],
            ['Performance Security', self::PERFORMANCE_SECURITY_PERCENT . '% of contract value (Works/Goods contracts above threshold)'],
            ['Delay Penalty', self::DELAY_PENALTY_PERCENT_PER_WEEK . '% of contract value per week of delay, max 10%'],
        ];
        foreach ($infoRows as [$label, $value]) {
            $infoTable->addRow();
            $infoTable->addCell(3000)->addText($label, $bold);
            $infoTable->addCell(6000)->addText((string) $value);
        }

        // ================= ANNEX II — Price Schedule (by category) =================
        $section->addTextBreak(2);
        $section->addText('ANNEX-II: PRICE SCHEDULE', array_merge($bold, ['underline' => 'single']));
        $section->addTextBreak(1);

        if ($itemsByCategory->isEmpty()) {
            $section->addText('[No linked PR items found — link a Procurement Plan with PR items to auto-fill this table.]');
        }

        foreach ($itemsByCategory as $categoryName => $lines) {
            $section->addText("Category: {$categoryName}", $bold);
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
            $widths = [700, 4000, 1200, 1200, 1900];
            $table->addRow();
            foreach (['SL', 'Item Details', 'Qty', 'Unit', 'Unit Price (BDT)'] as $i => $h) {
                $table->addCell($widths[$i])->addText($h, $bold, $center);
            }
            $sl = 1;
            foreach ($lines as $line) {
                $table->addRow();
                $table->addCell($widths[0])->addText((string) $sl);
                $table->addCell($widths[1])->addText(
                    ($line->item->name ?? '') . ($line->item->specification ? ': ' . $line->item->specification : '')
                );
                $table->addCell($widths[2])->addText((string) $line->quantity);
                $table->addCell($widths[3])->addText($line->unit->symbol ?? $line->unit->name ?? '');
                $table->addCell($widths[4])->addText('');
                $sl++;
            }
            $section->addTextBreak(1);
        }

        // ================= ANNEX III — Technical Evaluation Sheet =================
        $section->addTextBreak(1);
        $section->addText('ANNEX-III: TECHNICAL EVALUATION SHEET', array_merge($bold, ['underline' => 'single']));
        $section->addText('Total Marks: 100 (Technical: 60, Financial: 40). Minimum 60% required on Technical to qualify for financial evaluation.');
        $section->addTextBreak(1);

        $evalTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $evalTable->addRow();
        foreach (['SL', 'Evaluation Criteria', 'Marks'] as $h) {
            $evalTable->addCell(3000)->addText($h, $bold, $center);
        }
        $criteria = [
            'Compliance with technical specification',
            'Vendor experience & past performance (similar contracts)',
            'Manpower & machinery capacity',
            'Delivery timeline & logistics plan',
            'Financial capacity / bank solvency',
            'Compliance documents (Trade License, VAT, TIN, PSR)',
        ];
        foreach ($criteria as $i => $c) {
            $evalTable->addRow();
            $evalTable->addCell(700)->addText((string) ($i + 1));
            $evalTable->addCell(6300)->addText($c);
            $evalTable->addCell(1500)->addText('10');
        }
        $evalTable->addRow();
        $evalTable->addCell(7000, ['gridSpan' => 2])->addText('Total', $bold);
        $evalTable->addCell(1500)->addText('60', $bold);

        // ================= ANNEX IV — Terms & Conditions =================
        $section->addTextBreak(2);
        $section->addText('ANNEX-IV: TERMS & CONDITIONS', array_merge($bold, ['underline' => 'single']));
        $terms = [
            'Bidders must submit valid Trade License, VAT Registration Certificate, TIN Certificate, and Proof of Return Submission (PSR) with the bid.',
            'Bids must be submitted in a sealed envelope, addressed to the Convener, Central Procurement Committee, ESDO, on or before the submission deadline above.',
            'Bids received after the deadline will not be accepted under any circumstances.',
            'Prices quoted must be inclusive of VAT & Tax, and must remain valid for the period stated above.',
            'The successful bidder will be required to submit a Performance Security as stated above, within 7 days of receiving the Notification of Award.',
            'ESDO reserves the right to accept or reject any or all bids without assigning any reason.',
            'Any form of collusion, bribery, or fraudulent practice will result in immediate disqualification and may be reported to the appropriate authorities.',
        ];
        foreach ($terms as $i => $t) {
            $section->addText(($i + 1) . '. ' . $t);
        }

        return $phpWord;
    }
}
