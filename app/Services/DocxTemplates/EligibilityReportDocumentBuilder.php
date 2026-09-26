<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Eligibility Report — bidder-by-bidder checklist of Trade
 * License / TIN / BIN / PSR verification and pass/fail eligibility.
 * No PDF equivalent existed for this document; laid out fresh from the
 * EligibilityReport/EligibilityReportItem schema.
 *
 * Expects: ['report' => EligibilityReport, 'rfq' => Rfq].
 */
class EligibilityReportDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $report = $data['report'];
        $rfq = $data['rfq'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead(
            $section,
            'Eco-Social Development Organization (ESDO)',
            'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh'
        );

        $section->addText('ELIGIBILITY REPORT', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);
        $section->addText('RFQ/Tender Reference: ' . ($rfq->rfq_number ?? ''), $this->b());
        $section->addText('Subject: ' . ($rfq->subject ?? ''));
        $section->addText('Prepared By: ' . ($report->preparedBy->name ?? '[Name]'));
        $section->addText('Report Date: ' . $this->fmtDate($report->created_at));

        $yn = fn ($v) => $v ? 'Yes' : 'No';

        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        $cols = [
            ['SL', 500], ['Bidder / Vendor', 2300], ['Trade License', 1200], ['TIN', 1000],
            ['BIN', 1000], ['PSR', 1000], ['Eligible', 1200], ['Remarks', 1800],
        ];
        foreach ($cols as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }

        if ($report->items->isEmpty()) {
            $table->addRow();
            $table->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => count($cols)])
                ->addText('[No eligibility items recorded yet]');
        } else {
            foreach ($report->items as $i => $item) {
                $table->addRow();
                $table->addCell(500)->addText((string) ($i + 1));
                $table->addCell(2300)->addText($item->vendor->name ?? '');
                $table->addCell(1200)->addText($yn($item->trade_license_verified), [], $this->c());
                $table->addCell(1000)->addText($yn($item->tin_verified), [], $this->c());
                $table->addCell(1000)->addText($yn($item->bin_verified), [], $this->c());
                $table->addCell(1000)->addText($yn($item->psr_verified), [], $this->c());
                $table->addCell(1200)->addText($item->eligible ? 'Eligible' : 'Not Eligible', $this->b(), $this->c());
                $table->addCell(1800)->addText((string) $item->remarks);
            }
        }

        $section->addTextBreak(2);
        $sig = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sig->addRow();
        $cell = $sig->addCell(5000);
        $cell->addText('(' . ($report->preparedBy->name ?? '') . ')', $this->b());
        $cell->addText('Prepared By');

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
