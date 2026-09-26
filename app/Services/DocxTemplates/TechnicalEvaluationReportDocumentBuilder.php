<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;

/**
 * Builds the Technical Evaluation Report — per-bidder technical score
 * summary, plus a per-criterion breakdown when criteria/scores are loaded.
 * No PDF equivalent existed for this document.
 *
 * Expects: ['report' => TechnicalEvaluationReport, 'rfq' => Rfq].
 * `report` should have `items.vendor` loaded, and `criteria`/`items.scores`
 * loaded when available, for the per-criterion table.
 */
class TechnicalEvaluationReportDocumentBuilder
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

        $section->addText('TECHNICAL EVALUATION REPORT', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);
        $section->addText('RFQ/Tender Reference: ' . ($rfq->rfq_number ?? ''), $this->b());
        $section->addText('Subject: ' . ($rfq->subject ?? ''));
        $section->addText('Prepared By: ' . ($report->preparedBy->name ?? '[Name]'));
        $section->addText('Report Date: ' . $this->fmtDate($report->created_at));

        $criteria = $report->relationLoaded('criteria') ? $report->criteria : collect();

        if ($criteria->isNotEmpty()) {
            $section->addText('Evaluation Criteria', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
            $critTable = $section->addTable($this->borderedTableStyle());
            $critTable->addRow();
            foreach ([['SL', 700], ['Criterion', 6300], ['Max Marks', 2000]] as [$h, $w]) {
                $critTable->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
            }
            foreach ($criteria as $i => $criterion) {
                $critTable->addRow();
                $critTable->addCell(700)->addText((string) ($i + 1));
                $critTable->addCell(6300)->addText($criterion->name);
                $critTable->addCell(2000)->addText((string) $criterion->max_marks, [], $this->c());
            }
        }

        $section->addText('Bidder Scores', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $table = $section->addTable($this->borderedTableStyle());
        $table->addRow();
        $cols = [['SL', 700], ['Bidder / Vendor', 4300], ['Score', 1500], ['Remarks', 2500]];
        foreach ($cols as [$h, $w]) {
            $table->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }

        if ($report->items->isEmpty()) {
            $table->addRow();
            $table->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => count($cols)])
                ->addText('[No technical evaluation items recorded yet]');
        } else {
            foreach ($report->items as $i => $item) {
                $table->addRow();
                $table->addCell(700)->addText((string) ($i + 1));
                $table->addCell(4300)->addText($item->vendor->name ?? '');
                $table->addCell(1500)->addText((string) $item->score, [], $this->c());
                $table->addCell(2500)->addText((string) $item->remarks);

                if ($criteria->isNotEmpty() && $item->relationLoaded('scores')) {
                    $breakdown = $item->scores->map(function ($score) use ($criteria) {
                        $name = $criteria->firstWhere('id', $score->criterion_id)?->name ?? '';

                        return "{$name}: {$score->score}";
                    })->implode('; ');
                    if ($breakdown) {
                        $table->addRow();
                        $table->addCell(700)->addText('');
                        $table->addCell(4300 + 1500 + 2500, ['gridSpan' => 3])->addText($breakdown, ['italic' => true, 'size' => 9]);
                    }
                }
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
