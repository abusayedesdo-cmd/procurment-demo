<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Tender Opening Report. Mirrors resources/views/documents/tender-opening.blade.php.
 *
 * Expects: ['opening', 'rfq', 'committee', 'quotations', 'checkDoc'].
 */
class TenderOpeningDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $opening = $data['opening'];
        $rfq = $data['rfq'];
        $committee = $data['committee'];
        $quotations = $data['quotations'];
        $checkDoc = $data['checkDoc'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead(
            $section,
            'Eco-Social Development Organization (ESDO)',
            'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh'
        );

        $section->addText('TENDER OPENING REPORT', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);
        $section->addText('RFQ/Tender Reference: ' . ($rfq->rfq_number ?? ''), $this->b());
        $section->addText('Opening Date: ' . $this->fmtDate($opening->opening_date));
        $section->addText('Opened By: ' . ($opening->openedBy->name ?? '[Name]'));

        $section->addText('Tender Opening Committee', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        foreach ([['SL', 700], ['Name', 2800], ['Designation', 2800], ['Signature', 2500]] as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }
        if ($committee->isEmpty()) {
            $table->addRow();
            $table->addCell(700)->addText('1');
            $table->addCell(2800 + 2800 + 2500, ['gridSpan' => 3])->addText('[No committee members seeded]');
        } else {
            foreach ($committee as $i => $member) {
                $table->addRow();
                $table->addCell(700)->addText((string) ($i + 1));
                $table->addCell(2800)->addText($member->user->name ?? '');
                $table->addCell(2800)->addText($member->designation_in_committee ?? '');
                $table->addCell(2500)->addText('');
            }
        }

        $section->addText('Bidder List & Document Checklist', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $bidTable = $section->addTable($this->borderedTableStyle());
        $bidTable->addRow();
        $cols = [['SL', 500], ['Bidder / Vendor', 2300], ['Bid Price (BDT)', 1500], ['Trade License', 1300], ['TIN', 1100], ['BIN', 1100], ['Remarks', 1900]];
        foreach ($cols as [$h, $w]) {
            $bidTable->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }
        if ($quotations->isEmpty()) {
            $bidTable->addRow();
            $bidTable->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => count($cols)])
                ->addText('[No quotations recorded against this RFQ yet]');
        } else {
            foreach ($quotations as $i => $q) {
                $bidTable->addRow();
                $bidTable->addCell(500)->addText((string) ($i + 1));
                $bidTable->addCell(2300)->addText($q->vendor->name ?? '');
                $bidTable->addCell(1500)->addText($this->fmtMoney($q->quoted_amount));
                $bidTable->addCell(1300)->addText($checkDoc($q->vendor_id, 'trade_license'), [], $this->c());
                $bidTable->addCell(1100)->addText($checkDoc($q->vendor_id, 'tax_certificate'), [], $this->c());
                $bidTable->addCell(1100)->addText($checkDoc($q->vendor_id, 'vat_certificate'), [], $this->c());
                $bidTable->addCell(1900)->addText('');
            }
        }

        $section->addText('Bidder Representative Attendance', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $attTable = $section->addTable($this->borderedTableStyle());
        $attTable->addRow();
        foreach ([['SL', 700], ['Vendor', 2300], ['Representative Name', 2300], ['Contact No.', 2300], ['Signature', 2300]] as [$h, $w]) {
            $attTable->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }
        if ($quotations->isEmpty()) {
            $attTable->addRow();
            $attTable->addCell(700 + 2300 * 4, ['gridSpan' => 5])->addText('[No bidders to list]');
        } else {
            foreach ($quotations as $i => $q) {
                $attTable->addRow();
                $attTable->addCell(700)->addText((string) ($i + 1));
                $attTable->addCell(2300)->addText($q->vendor->name ?? '');
                $attTable->addCell(2300)->addText('');
                $attTable->addCell(2300)->addText('');
                $attTable->addCell(2300)->addText('');
            }
        }

        $section->addTextBreak(1);
        $section->addText('Remarks: ' . ($opening->remarks ?: '__________________________________________________'));

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
