<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Table;

/**
 * Builds the Annual/Project Procurement Plan matrix. Mirrors
 * resources/views/documents/annual-plan-pdf.blade.php, landscape A3-ish
 * layout replaced by a landscape A4 Word table (same data, same grouping —
 * Word doesn't need a fixed physical page size the way a PDF export does,
 * since the table can simply continue onto further pages).
 *
 * Expects the same ['plan', 'layout'] array
 * DocumentDownloadController::annualPlanPdf() already builds
 * ($layout comes from buildAnnualPlanLayout()).
 */
class AnnualPlanDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $plan = $data['plan'];
        $layout = $data['layout'];

        $phpWord = $this->newPhpWord();
        $phpWord->setDefaultFontSize(7.5);
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginLeft' => 500, 'marginRight' => 500, 'marginTop' => 500, 'marginBottom' => 500,
        ]);

        $this->addLetterhead(
            $section,
            'Eco-Social Development Organization (ESDO)',
            'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh'
        );

        $info = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        foreach ([
            ['Project Name/Title', $plan->project_name ?? $plan->title],
            ['Project Location (Office)', $plan->project_location],
            ['Project Working Area', $plan->working_area],
            ['Project Duration', $plan->project_duration ?? (optional($plan->fiscal_year_start)->format('d M Y') . ' to ' . optional($plan->fiscal_year_end)->format('d M Y'))],
            ['Date of Agreement/Awarded', optional($plan->agreement_date)->format('d M Y')],
            ['Donor Name', $plan->donor_name],
            ['Activity Summary', $plan->activity_summary],
        ] as [$label, $value]) {
            $info->addRow();
            $info->addCell(2600)->addText($label, $this->b(), ['size' => 9]);
            $info->addCell(6000)->addText((string) $value, [], ['size' => 9]);
        }
        $section->addTextBreak(1);

        $fixedHeads = [['Sl', 500], ['Category', 1200], ['Sub Category', 1200], ['Item Name', 1200], ['Specification', 1400], ['Unit', 700]];
        $tailHeads = [['Already Procured', 1100], ['Remaining Balance', 1100], ['Remarks', 1100]];
        $groupColWidth = 550;

        $table = $section->addTable(array_merge($this->borderedTableStyle(), ['layout' => Table::LAYOUT_FIXED]));

        // --- Header row 1: fixed cols (vMerge restart) + group titles (gridSpan) + tail cols (vMerge restart)
        $table->addRow();
        foreach ($fixedHeads as [$h, $w]) {
            $table->addCell($w, array_merge($this->headerCellStyle(), ['vMerge' => 'restart']))->addText($h, $this->b(), $this->c());
        }
        foreach ($layout as $group) {
            $span = count($group['sublabels']);
            $cell = $table->addCell($groupColWidth * $span * 3, array_merge($this->headerCellStyle(), ['gridSpan' => $span * 3]));
            $cell->addText($group['title'], $this->b(), $this->c());
        }
        foreach ($tailHeads as [$h, $w]) {
            $table->addCell($w, array_merge($this->headerCellStyle(), ['vMerge' => 'restart']))->addText($h, $this->b(), $this->c());
        }

        // --- Header row 2: fixed/tail cols continue vMerge; sublabels (gridSpan 3)
        $table->addRow();
        foreach ($fixedHeads as [$h, $w]) {
            $table->addCell($w, array_merge($this->headerCellStyle(), ['vMerge' => 'continue']));
        }
        foreach ($layout as $group) {
            foreach ($group['sublabels'] as $sub) {
                $cell = $table->addCell($groupColWidth * 3, array_merge($this->headerCellStyle(), ['gridSpan' => 3]));
                $cell->addText((string) $sub, $this->b(), $this->c());
            }
        }
        foreach ($tailHeads as [$h, $w]) {
            $table->addCell($w, array_merge($this->headerCellStyle(), ['vMerge' => 'continue']));
        }

        // --- Header row 3: fixed/tail cols continue vMerge; Unit/Rate/Total per sublabel
        $table->addRow();
        foreach ($fixedHeads as [$h, $w]) {
            $table->addCell($w, array_merge($this->headerCellStyle(), ['vMerge' => 'continue']));
        }
        foreach ($layout as $group) {
            foreach ($group['sublabels'] as $sub) {
                foreach (['No. of Unit', 'Rate', 'Total'] as $label) {
                    $table->addCell($groupColWidth, $this->headerCellStyle())->addText($label, $this->b(), $this->c());
                }
            }
        }
        foreach ($tailHeads as [$h, $w]) {
            $table->addCell($w, array_merge($this->headerCellStyle(), ['vMerge' => 'continue']));
        }

        // --- Data rows ---
        $groupSums = [];
        foreach ($layout as $g) {
            $groupSums[$g['key']] = array_fill(0, count($g['sublabels']), 0.0);
        }
        $alreadyProcuredSum = 0.0;
        $remainingBalanceSum = 0.0;
        $rowNumber = 0;

        foreach ($plan->packages as $pkg) {
            $rowNumber++;
            $table->addRow();
            $table->addCell($fixedHeads[0][1])->addText((string) ($pkg->sl_no ?? $rowNumber), [], $this->c());
            $table->addCell($fixedHeads[1][1])->addText((string) $pkg->category->name);
            $table->addCell($fixedHeads[2][1])->addText((string) ($pkg->chartOfAccount->name ?? $pkg->item?->chartOfAccount?->name ?? ''));
            $table->addCell($fixedHeads[3][1])->addText((string) $pkg->budgeted_head);
            $table->addCell($fixedHeads[4][1])->addText((string) $pkg->specification);
            $table->addCell($fixedHeads[5][1])->addText((string) $pkg->unit, [], $this->c());

            foreach ($layout as $group) {
                $values = $pkg->alignedValuesFor($group['key'], $group['sublabels']);
                foreach ($values as $i => $v) {
                    $table->addCell($groupColWidth)->addText($v['no_of_unit'] ? number_format($v['no_of_unit'], 0) : '', [], $this->c());
                    $table->addCell($groupColWidth)->addText($v['rate'] ? number_format($v['rate'], 2) : '', [], $this->c());
                    $table->addCell($groupColWidth)->addText($v['total'] ? number_format($v['total'], 2) : '', [], $this->c());
                    $groupSums[$group['key']][$i] += $v['total'] ?? 0;
                }
            }

            $table->addCell($tailHeads[0][1])->addText(number_format((float) $pkg->already_procured, 2), [], $this->c());
            $table->addCell($tailHeads[1][1])->addText(number_format((float) $pkg->remaining_balance, 2), [], $this->c());
            $table->addCell($tailHeads[2][1])->addText((string) $pkg->remarks);

            $alreadyProcuredSum += (float) $pkg->already_procured;
            $remainingBalanceSum += (float) $pkg->remaining_balance;
        }

        // --- Total row ---
        if ($plan->packages->count()) {
            $table->addRow();
            $fixedTotalWidth = array_sum(array_column($fixedHeads, 1));
            $table->addCell($fixedTotalWidth, array_merge($this->headerCellStyle(), ['gridSpan' => count($fixedHeads)]))
                ->addText('Total', $this->b());
            foreach ($layout as $group) {
                foreach ($groupSums[$group['key']] as $sum) {
                    $table->addCell($groupColWidth, $this->headerCellStyle())->addText('');
                    $table->addCell($groupColWidth, $this->headerCellStyle())->addText('');
                    $table->addCell($groupColWidth, $this->headerCellStyle())->addText(number_format($sum, 2), $this->b(), $this->c());
                }
            }
            $table->addCell($tailHeads[0][1], $this->headerCellStyle())->addText(number_format($alreadyProcuredSum, 2), $this->b(), $this->c());
            $table->addCell($tailHeads[1][1], $this->headerCellStyle())->addText(number_format($remainingBalanceSum, 2), $this->b(), $this->c());
            $table->addCell($tailHeads[2][1], $this->headerCellStyle())->addText('');
        }

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
