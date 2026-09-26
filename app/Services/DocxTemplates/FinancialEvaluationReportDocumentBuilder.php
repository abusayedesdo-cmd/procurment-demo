<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Financial Evaluation Report — per-bidder quoted amount and
 * financial marks. No PDF equivalent existed for this document.
 *
 * Expects: ['report' => FinancialEvaluationReport, 'rfq' => Rfq].
 */
class FinancialEvaluationReportDocumentBuilder
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

        $section->addText('FINANCIAL EVALUATION REPORT', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);
        $section->addText('RFQ/Tender Reference: ' . ($rfq->rfq_number ?? ''), $this->b());
        $section->addText('Subject: ' . ($rfq->subject ?? ''));
        $section->addText('Prepared By: ' . ($report->preparedBy->name ?? '[Name]'));
        $section->addText('Report Date: ' . $this->fmtDate($report->created_at));

        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        $cols = [['SL', 700], ['Bidder / Vendor', 3300], ['Quoted Amount (BDT)', 2000], ['Financial Marks', 1800], ['Remarks', 1400]];
        foreach ($cols as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }

        if ($report->items->isEmpty()) {
            $table->addRow();
            $table->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => count($cols)])
                ->addText('[No financial evaluation items recorded yet]');
        } else {
            foreach ($report->items as $i => $item) {
                $table->addRow();
                $table->addCell(700)->addText((string) ($i + 1));
                $table->addCell(3300)->addText($item->vendor->name ?? '');
                $table->addCell(2000)->addText($this->fmtMoney($item->quoted_amount), [], $this->c());
                $table->addCell(1800)->addText((string) $item->financial_marks, [], $this->c());
                $table->addCell(1400)->addText((string) $item->remarks);
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
