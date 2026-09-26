<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Comparative Statement of Bids — technical + financial marks
 * side by side per bidder, ranked, with the lowest-evaluated vendor called
 * out. No PDF equivalent existed for this document.
 *
 * Expects: ['statement' => ComparativeStatement, 'rfq' => Rfq].
 */
class ComparativeStatementDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $statement = $data['statement'];
        $rfq = $data['rfq'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead(
            $section,
            'Eco-Social Development Organization (ESDO)',
            'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh'
        );

        $section->addText('COMPARATIVE STATEMENT OF BIDS', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);
        $section->addText('RFQ/Tender Reference: ' . ($rfq->rfq_number ?? ''), $this->b());
        $section->addText('Subject: ' . ($rfq->subject ?? ''));
        $section->addText('Prepared By: ' . ($statement->preparedBy->name ?? '[Name]'));
        $section->addText('Report Date: ' . $this->fmtDate($statement->created_at));

        $items = $statement->items->sortBy('rank')->values();

        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        $cols = [
            ['Rank', 700], ['Bidder / Vendor', 2400], ['Amount (BDT)', 1500],
            ['Technical Marks', 1400], ['Financial Marks', 1400], ['Total Marks', 1400], ['Remarks', 1400],
        ];
        foreach ($cols as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }

        if ($items->isEmpty()) {
            $table->addRow();
            $table->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => count($cols)])
                ->addText('[No comparative items recorded yet]');
        } else {
            foreach ($items as $item) {
                $table->addRow();
                $isLowest = $statement->lowest_evaluated_vendor_id && $item->vendor_id === $statement->lowest_evaluated_vendor_id;
                $rowStyle = $isLowest ? $this->b() : [];
                $table->addCell(700)->addText((string) $item->rank, $rowStyle, $this->c());
                $vendorLabel = ($item->vendor->name ?? '') . ($isLowest ? ' (Lowest Evaluated)' : '');
                $table->addCell(2400)->addText($vendorLabel, $rowStyle);
                $table->addCell(1500)->addText($this->fmtMoney($item->amount), $rowStyle, $this->c());
                $table->addCell(1400)->addText((string) $item->technical_marks, $rowStyle, $this->c());
                $table->addCell(1400)->addText((string) $item->financial_marks, $rowStyle, $this->c());
                $table->addCell(1400)->addText((string) $item->total_marks, $rowStyle, $this->c());
                $table->addCell(1400)->addText('');
            }
        }

        if ($statement->lowestEvaluatedVendor) {
            $section->addTextBreak(1);
            $section->addText('Recommended for Award: ' . $statement->lowestEvaluatedVendor->name, $this->b());
        }

        $section->addTextBreak(2);
        $sig = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sig->addRow();
        $cell = $sig->addCell(5000);
        $cell->addText('(' . ($statement->preparedBy->name ?? '') . ')', $this->b());
        $cell->addText('Prepared By');

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
