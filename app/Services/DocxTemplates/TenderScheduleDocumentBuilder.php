<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the Tender Schedule / Tender Document. Mirrors
 * resources/views/documents/tender-schedule.blade.php.
 *
 * Expects DocumentDownloadController::tenderScheduleViewData()'s array:
 * ['rfq', 'case', 'pr', 'itemsByCategory', 'validityDays',
 *  'performanceSecurityPercent', 'delayPenaltyPercent', 'technicalCriteria',
 *  'eligibilityDocuments', 'signatoryName', 'signatoryTitle', 'signatoryEmail',
 *  'convenerName'].
 */
class TenderScheduleDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $rfq = $data['rfq'];
        $case = $data['case'];
        $pr = $data['pr'];
        $itemsByCategory = $data['itemsByCategory'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead(
            $section,
            'Eco-Social Development Organization (ESDO)',
            'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh'
        );

        $section->addText($case?->natureLabel() ?? 'Tender Schedule', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addText('(Tender Document/Schedule — ' . $rfq->type . ')', [], $this->c());
        $section->addTextBreak(1);

        $section->addText('Description of Works: ' . ($rfq->subject ?: '[Description of Works]'));

        $meta = $section->addTable($this->borderedTableStyle());
        foreach ([
            ['DATE:', $this->fmtDate($rfq->issue_date, 'd.m.Y')],
            ['REFERENCE:', $rfq->rfq_number],
            ['Address:', 'Collegepara (Gobindanagar), Thakurgaon-5100'],
        ] as [$label, $value]) {
            $meta->addRow();
            $meta->addCell(2500)->addText($label, $this->b());
            $meta->addCell(6500)->addText((string) $value);
        }

        $section->addTextBreak(1);
        $section->addText('To');
        $section->addText('Bidder Name: .............................................................');
        $section->addText('Address: .....................................................................');
        $section->addTextBreak(1);
        $section->addText('Dear Respected Bidder,');
        $section->addText(
            'The Eco-Social Development Organization (ESDO) is hereby requesting you to submit your bid proposal of '
            . ($rfq->subject ?: '[Description of Works]') . ' as per the annexes of this Tender Document.'
        );
        $section->addText(
            'Tender must be submitted on or before ' . $this->fmtDate($rfq->closing_date, 'd.m.Y') . '; '
            . $this->fmtDate($rfq->closing_date, 'h:i A') . ' via courier/post office or directly to the address below:'
        );
        $section->addText(
            ($data['convenerName'] ?? 'Convener') . ", Central Procurement Committee\n"
            . "Eco-Social Development Organization (ESDO)\n"
            . 'Collegepara (Gobindanagar), Thakurgaon-5100',
            $this->b(),
            $this->c()
        );
        $section->addText('Tender should be submitted in a sealed envelope marked "Quotation for ' . ($rfq->subject ?: '[Description of Works]') . '".');
        $section->addText(
            'It shall remain your responsibility to ensure that your tender reaches the address above on or before the '
            . 'deadline. Tenders received by ESDO after the deadline indicated above, for whatever reason, shall not be '
            . 'considered for evaluation.'
        );

        $section->addText('Please take note of the following requirements and conditions:', $this->b());

        $reqTable = $section->addTable($this->borderedTableStyle());
        $eligibilityDocsText = "Bidders must have legal capacity to enter the Contract. In support of its qualification, the bidder must submit:\n"
            . implode("\n", array_map(fn ($d) => '- ' . $d, $data['eligibilityDocuments']))
            . "\nFailure to submit the above shall result in disqualification.";

        $rows = [
            ['Exact Address of Delivery Locations', 'As per Annex-I' . ($pr?->delivery_location ? ' — ' . $pr->delivery_location : '')],
            ['Latest Expected Delivery Date and Time', optional($pr?->procurementPlan?->est_delivery_date)->format('d F, Y') ?: '[TBD]'],
            ['Packing Requirements', 'Secure, safe packing as necessary to avoid any damage or defects.'],
            ['Preferred Currency of Tender', 'Local Currency: BDT (Taka)'],
            ['Value Added Tax on Tender Price', 'Must be inclusive of Tax and other applicable indirect taxes'],
            ['After-sales Services', 'Replace the sub-standard items within possible short time. Any defect in manufacture will not be accepted.'],
            ['Deadline for the Submission of Tender', $this->fmtDate($rfq->closing_date, 'd.m.Y') . ' (Those who submit the tender are invited to present at the time of tender opening). Opening Time: ' . $this->fmtDate($rfq->closing_date, 'h:i A')],
            ['Price Tender / Bill / Invoice Language', 'English (Technical Specification and other correspondence from/to Suppliers may be in Bangla).'],
            ['Documents to be Submitted for Eligibility Criteria', $eligibilityDocsText],
            ['Period of Validity of Quotes starting the Submission Date', $data['validityDays'] . ' days from the submission deadline'],
            ['Partial Bid', 'Not Permitted.'],
            ['Payment Terms', 'Payment will be made after satisfactory delivery as per Terms and Conditions.'],
            ['Performance Security', "Selected vendor should deposit {$data['performanceSecurityPercent']}% of the total awarded amount in the form of a pay order. The Performance Security will be returned to the supplier after successful completion of the contract, 90 (ninety) days after award."],
            ['Liquidated Damages', "{$data['delayPenaltyPercent']}% per week on the total value of delayed delivery. In case the delay is more than 1 (one) week without any approval, the goods Order/PO might be cancelled."],
            ['Evaluation Criteria', 'Full compliance with eligibility requirements, technical responsiveness, lowest price and goodwill; full acceptance of the Purchase Order (PO)/Terms and Conditions of the Contract; and Bid Validity (see Annex-III for the detailed evaluation sheet, where applicable).'],
            ['Procuring Entity will Award to', 'One Supplier.'],
            ['Type of Contract to be Signed', 'Purchase Order (PO) / Another Type(s) of Contract, as applicable.'],
            ['Special Conditions of Contract', 'Poor quality/unacceptable delivery and failure to make necessary corrections/replacements as requested by the procuring entity will result in cancellation of the PO.'],
            ['Conditions for Release of Payment', 'Written acceptance of goods based on full compliance with PO/Contract requirements after agreed delivery and (where applicable) successful installation at the delivery point.'],
            ['Annexes to this Tender Document', "Annex-I: Address of Delivery Locations\nAnnex-II: Price Schedule for Goods and Related Services\nAnnex-III: Description/Specifications and Rate Sheet\nAnnex-IV: Terms and Conditions for Supply of Goods and Payment\nAnnex-V: Tender Submission Letter\nAnnex-VI: Contract Agreement"],
            ['Contact Person for Inquiries (Written inquiries only)', $data['signatoryName'] . "\n" . $data['signatoryTitle'] . ", Central Procurement Committee\nCollegepara (Gobindanagar), Thakurgaon-5100\nEmail: " . ($data['signatoryEmail'] ?: '[email not on file]')],
        ];

        foreach ($rows as [$label, $value]) {
            $reqTable->addRow();
            $reqTable->addCell(3200)->addText($label, $this->b());
            $cell = $reqTable->addCell(6800);
            foreach (explode("\n", $value) as $line) {
                $cell->addText($line);
            }
        }

        $section->addPageBreak();
        $section->addText('ANNEX-II: PRICE SCHEDULE', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));

        if ($itemsByCategory->isEmpty()) {
            $section->addText('[No linked PR items found — link a Procurement Plan with PR items to auto-fill this table.]');
        } else {
            foreach ($itemsByCategory as $categoryName => $lines) {
                $section->addText('Category: ' . $categoryName, $this->b());
                $table = $section->addTable($this->borderedTableStyle());
                $table->addRow();
                foreach ([['SL', 700], ['Item Details', 4400], ['Qty', 1200], ['Unit', 1200], ['Unit Price (BDT)', 2500]] as [$h, $w]) {
                    $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
                }
                foreach ($lines as $i => $line) {
                    $table->addRow();
                    $table->addCell(700)->addText((string) ($i + 1));
                    $desc = ($line->item->name ?? '') . ($line->item->specification ? ': ' . $line->item->specification : '');
                    $table->addCell(4400)->addText($desc);
                    $table->addCell(1200)->addText((string) $line->quantity);
                    $table->addCell(1200)->addText($line->unit->symbol ?? $line->unit->name ?? '');
                    $table->addCell(2500)->addText('');
                }
            }
        }

        $section->addText('ANNEX-III: TECHNICAL EVALUATION SHEET', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $section->addText('Total Marks: 100 (Technical: 60, Financial: 40). Minimum 60% required on Technical to qualify for financial evaluation.');

        $techTable = $section->addTable($this->borderedTableStyle());
        $techTable->addRow();
        foreach ([['SL', 700], ['Evaluation Criteria', 7000], ['Marks', 2000]] as [$h, $w]) {
            $techTable->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }
        foreach ($data['technicalCriteria'] as $i => $criterion) {
            $techTable->addRow();
            $techTable->addCell(700)->addText((string) ($i + 1));
            $techTable->addCell(7000)->addText($criterion);
            $techTable->addCell(2000)->addText('10', [], $this->c());
        }
        $techTable->addRow();
        $techTable->addCell(700 + 7000, ['gridSpan' => 2])->addText('Total', $this->b());
        $techTable->addCell(2000)->addText('60', $this->b(), $this->c());

        $section->addText('ANNEX-IV: TERMS & CONDITIONS', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $this->addNumberedList($section, [
            'Bidders must submit valid Trade License, VAT Registration Certificate, TIN Certificate, and Proof of Return Submission (PSR) with the bid.',
            'Bids must be submitted in a sealed envelope, addressed to the Convener, Central Procurement Committee, ESDO, on or before the submission deadline above.',
            'Bids received after the deadline will not be accepted under any circumstances.',
            'Prices quoted must be inclusive of VAT & Tax, and must remain valid for the period stated above.',
            'The successful bidder will be required to submit a Performance Security as stated above, within 7 days of receiving the Notification of Award.',
            'ESDO reserves the right to accept or reject any or all bids without assigning any reason.',
            'Any form of collusion, bribery, or fraudulent practice will result in immediate disqualification and may be reported to the appropriate authorities.',
        ]);

        $section->addTextBreak(1);
        $section->addText('Sincerely yours,', $this->b());
        $section->addText('(' . $data['signatoryName'] . ')');
        $section->addText($data['signatoryTitle']);
        $section->addText('Central Procurement Committee, ESDO');

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
