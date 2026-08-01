<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\TenderOpening;
use App\Models\VendorDocument;
<<<<<<< HEAD
use App\Models\ProcurementAnnualPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
=======
use Barryvdh\DomPDF\Facade\Pdf;
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc

/**
 * Generates the RFQ, Tender Schedule, and Tender Opening documents as
 * PDF (switched from .docx/PHPWord after repeated "file won't open"
 * issues — PDF via DomPDF is a much simpler, more reliable pipeline:
 * plain HTML/Blade in, PDF out, no OOXML internals to get wrong).
 */
class DocumentDownloadController extends Controller
{
    protected const VALIDITY_DAYS = 90;
    protected const PERFORMANCE_SECURITY_PERCENT = 5;
    protected const DELAY_PENALTY_PERCENT_PER_WEEK = 1;
    protected const TECHNICAL_CRITERIA = [
        'Compliance with technical specification',
        'Vendor experience & past performance (similar contracts)',
        'Manpower & machinery capacity',
        'Delivery timeline & logistics plan',
        'Financial capacity / bank solvency',
        'Compliance documents (Trade License, VAT, TIN, PSR)',
    ];

    public function rfq(Rfq $rfq)
    {
        $rfq->loadMissing(
            'procurementPlan.purchaseRequisition.items.item',
            'procurementPlan.purchaseRequisition.items.unit'
        );
        $items = $rfq->procurementPlan?->purchaseRequisition?->items ?? collect();

        [$signatoryName, $signatoryTitle] = $this->conveningOfficer();

        $pdf = Pdf::loadView('documents.rfq', [
            'rfq' => $rfq,
            'items' => $items,
            'signatoryName' => $signatoryName,
            'signatoryTitle' => $signatoryTitle,
        ]);

        return $pdf->download("RFQ-{$this->safe($rfq->rfq_number)}.pdf");
    }

    public function tenderSchedule(Rfq $rfq)
    {
        $rfq->loadMissing(
            'procurementPlan.purchaseRequisition.items.item.chartOfAccount',
            'procurementPlan.purchaseRequisition.items.unit'
        );
        $items = $rfq->procurementPlan?->purchaseRequisition?->items ?? collect();
        $itemsByCategory = $items->groupBy(fn ($line) => $line->item->chartOfAccount->name ?? 'General');

        $pdf = Pdf::loadView('documents.tender-schedule', [
            'rfq' => $rfq,
            'itemsByCategory' => $itemsByCategory,
            'validityDays' => self::VALIDITY_DAYS,
            'performanceSecurityPercent' => self::PERFORMANCE_SECURITY_PERCENT,
            'delayPenaltyPercent' => self::DELAY_PENALTY_PERCENT_PER_WEEK,
            'technicalCriteria' => self::TECHNICAL_CRITERIA,
        ]);

        return $pdf->download("Tender-Schedule-{$this->safe($rfq->rfq_number)}.pdf");
    }

    public function tenderOpening(TenderOpening $tenderOpening)
    {
        $tenderOpening->loadMissing('rfq.procurementPlan.purchaseRequisition', 'openedBy');
        $rfq = $tenderOpening->rfq;

        $quotations = Quotation::query()->where('rfq_id', $rfq->id)->with('vendor')->get();

        $vendorDocsByVendor = VendorDocument::query()
            ->whereIn('vendor_id', $quotations->pluck('vendor_id'))
            ->where('verified', true)
            ->get()
            ->groupBy('vendor_id');

        $checkDoc = function ($vendorId, $type) use ($vendorDocsByVendor) {
            return $vendorDocsByVendor->get($vendorId, collect())->firstWhere('document_type', $type) ? 'Yes' : 'No';
        };

        $committee = CommitteeMember::query()
            ->whereHas('committee', fn ($q) => $q->where('name', 'Central Procurement Committee'))
            ->with('user')
            ->get();

        $pdf = Pdf::loadView('documents.tender-opening', [
            'opening' => $tenderOpening,
            'rfq' => $rfq,
            'committee' => $committee,
            'quotations' => $quotations,
            'checkDoc' => $checkDoc,
        ]);

        $rfqNumber = $rfq->rfq_number ?? $tenderOpening->id;

        return $pdf->download("Tender-Opening-{$this->safe($rfqNumber)}.pdf");
    }

    /** Filenames can't contain "/" or "\" — memo numbers like "126/731/2026-2027" do. */
    protected function safe(string $value): string
    {
        return str_replace(['/', '\\'], '-', $value);
    }

    /**
     * Pulls the Convener/Member Secretary of the "Central Procurement
     * Committee" for the RFQ signature block, if seeded. Falls back to
     * a placeholder if not found.
     */
    protected function conveningOfficer(): array
    {
        $member = CommitteeMember::query()
            ->whereHas('committee', fn ($q) => $q->where('name', 'Central Procurement Committee'))
            ->where('designation_in_committee', 'like', '%Secretary%')
            ->with('user')
            ->first();

        if ($member && $member->user) {
            return [$member->user->name, $member->designation_in_committee];
        }

        return ['[Member Secretary Name]', 'Member Secretary/Convener'];
    }
<<<<<<< HEAD

    public function annualPlanPdf(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->load('packages.periods.entries', 'packages.category');
        $layout = $this->buildAnnualPlanLayout($procurementAnnualPlan);

        $pdf = Pdf::loadView('documents.annual-plan-pdf', ['plan' => $procurementAnnualPlan, 'layout' => $layout])
            ->setPaper('a3', 'landscape');

        return $pdf->download('annual-plan-' . $procurementAnnualPlan->id . '.pdf');
    }

    public function annualPlanExcel(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->load('packages.periods.entries', 'packages.category');
        $layout = $this->buildAnnualPlanLayout($procurementAnnualPlan);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Annual Plan');
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $col = fn (int $index): string => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
        $setCell = fn (int $colIndex, int $rowIndex, $value) => $sheet->setCellValue($col($colIndex) . $rowIndex, $value);

        // --- Project info block ---
        $row = 1;
        $sheet->setCellValue("A{$row}", $procurementAnnualPlan->title);
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        $infoRows = [
            ['Project Name/Title', $procurementAnnualPlan->project_name ?? $procurementAnnualPlan->title],
            ['Project Location (Office)', $procurementAnnualPlan->project_location],
            ['Project Working Area', $procurementAnnualPlan->working_area],
            ['Project Duration', $procurementAnnualPlan->project_duration ?? (optional($procurementAnnualPlan->fiscal_year_start)->format('d M Y') . ' to ' . optional($procurementAnnualPlan->fiscal_year_end)->format('d M Y'))],
            ['Date of Agreement/Awarded', optional($procurementAnnualPlan->agreement_date)->format('d M Y')],
            ['Donor Name', $procurementAnnualPlan->donor_name],
            ['Activity Summary', $procurementAnnualPlan->activity_summary],
        ];

        foreach ($infoRows as [$label, $value]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$row}", $value);
            $sheet->mergeCells("B{$row}:F{$row}");
            $row++;
        }

        $row += 1;
        $tableStartRow = $row;
        $r1 = $row;
        $r2 = $row + 1;
        $r3 = $row + 2;

        // --- Matrix header (3 rows: group title / sublabel / No.-Rate-Total) ---
        $sheet->setCellValue("A{$r1}", 'Sl.No');
        $sheet->setCellValue("B{$r1}", 'Category');
        $sheet->setCellValue("C{$r1}", 'Budgeted Head');
        $sheet->setCellValue("D{$r1}", 'Specification');
        $sheet->setCellValue("E{$r1}", 'Unit');
        foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
            $sheet->mergeCells("{$letter}{$r1}:{$letter}{$r3}");
        }

        $colIndex = 6;
        foreach ($layout as $group) {
            $groupWidth = count($group['sublabels']) * 3;
            $startLetter = $col($colIndex);
            $endLetter = $col($colIndex + $groupWidth - 1);
            $sheet->setCellValue("{$startLetter}{$r1}", $group['title']);
            $sheet->mergeCells("{$startLetter}{$r1}:{$endLetter}{$r1}");

            $subCol = $colIndex;
            foreach ($group['sublabels'] as $sub) {
                if ($sub !== '') {
                    $subStart = $col($subCol);
                    $subEnd = $col($subCol + 2);
                    $sheet->setCellValue("{$subStart}{$r2}", $sub);
                    $sheet->mergeCells("{$subStart}{$r2}:{$subEnd}{$r2}");
                }
                $setCell($subCol, $r3, 'No. of Unit');
                $setCell($subCol + 1, $r3, 'Rate');
                $setCell($subCol + 2, $r3, 'Total');
                $subCol += 3;
            }

            $colIndex += $groupWidth;
        }

        $setCell($colIndex, $r1, 'Already Procured');
        $sheet->mergeCells($col($colIndex) . "{$r1}:" . $col($colIndex) . $r3);
        $colIndex++;
        $setCell($colIndex, $r1, 'Remaining Balance');
        $sheet->mergeCells($col($colIndex) . "{$r1}:" . $col($colIndex) . $r3);
        $colIndex++;
        $setCell($colIndex, $r1, 'Remarks');
        $sheet->mergeCells($col($colIndex) . "{$r1}:" . $col($colIndex) . $r3);
        $lastCol = $colIndex;

        $headerRange = "A{$tableStartRow}:" . $col($lastCol) . $r3;
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $row = $r3 + 1;

        // --- Data rows ---
        $groupSums = [];
        foreach ($layout as $g) {
            $groupSums[$g['key']] = array_fill(0, count($g['sublabels']), 0.0);
        }
        $alreadyProcuredSum = 0.0;
        $remainingBalanceSum = 0.0;

        foreach ($procurementAnnualPlan->packages as $pkg) {
            $colIndex = 1;
            $setCell($colIndex++, $row, $pkg->sl_no);
            $setCell($colIndex++, $row, $pkg->category->name);
            $setCell($colIndex++, $row, $pkg->budgeted_head);
            $setCell($colIndex++, $row, $pkg->specification);
            $setCell($colIndex++, $row, $pkg->unit);

            foreach ($layout as $group) {
                $values = $pkg->alignedValuesFor($group['key'], $group['sublabels']);
                foreach ($values as $i => $v) {
                    $setCell($colIndex, $row, $v['no_of_unit']);
                    $setCell($colIndex + 1, $row, $v['rate']);
                    $setCell($colIndex + 2, $row, $v['total']);
                    $groupSums[$group['key']][$i] += $v['total'] ?? 0;
                    $colIndex += 3;
                }
            }

            $setCell($colIndex++, $row, (float) $pkg->already_procured);
            $setCell($colIndex++, $row, (float) $pkg->remaining_balance);
            $setCell($colIndex, $row, $pkg->remarks);

            $alreadyProcuredSum += (float) $pkg->already_procured;
            $remainingBalanceSum += (float) $pkg->remaining_balance;

            $row++;
        }

        // --- Total row (Total column per sublabel only — No. of Unit/Rate left blank, same as before) ---
        $sheet->setCellValue("A{$row}", 'Total');
        $sheet->mergeCells("A{$row}:E{$row}");
        $colIndex = 6;
        foreach ($layout as $group) {
            foreach ($groupSums[$group['key']] as $sum) {
                $setCell($colIndex + 2, $row, $sum);
                $colIndex += 3;
            }
        }
        $setCell($colIndex++, $row, $alreadyProcuredSum);
        $setCell($colIndex, $row, $remainingBalanceSum);

        $totalRange = "A{$row}:" . $col($lastCol) . $row;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');

        foreach (range('A', $col($lastCol)) as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'annual-plan-' . $procurementAnnualPlan->id . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

  private function buildAnnualPlanLayout(ProcurementAnnualPlan $plan): array
    {
        $fixedOrder = ['previous_2nd_year', 'previous_1st_year', 'current_year', 'quarter_1', 'quarter_2', 'quarter_3', 'year_1_total', 'year_2_total', 'year_3_total', 'grand_total'];
        $breakableTitles = ['previous_2nd_year' => 'Previous 2nd Year', 'previous_1st_year' => 'Previous 1st Year', 'current_year' => 'Current Year', 'year_2_total' => 'Total Year-2', 'year_3_total' => 'Total Year-3'];
        $fixedTitles = ['quarter_1' => 'Quarter-1', 'quarter_2' => 'Quarter-2', 'quarter_3' => 'Quarter-3', 'year_1_total' => 'Total Year-1', 'grand_total' => 'Grand Total'];

        $groups = [];

        foreach ($fixedOrder as $key) {
                if (isset($breakableTitles[$key])) {
                // If any package in this plan used Monthly for this slot, the whole plan's
                // column layout uses Monthly — it's the finer granularity, so a Quarterly
                // package's own total still lands correctly (just collapsed to one column)
                // instead of a Quarterly package accidentally dumping its whole total into
                // someone else's real "January" figure.
                $granularities = $plan->packages
                    ->map(fn ($pkg) => $pkg->periodBySlotKey($key)?->breakdown_granularity)
                    ->filter()
                    ->unique();

                $granularity = $granularities->contains('month') ? 'month' : $granularities->first();

                $sublabels = $granularity === 'month'
                    ? ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
                    : [''];

                $groups[] = ['key' => $key, 'title' => $breakableTitles[$key], 'sublabels' => $sublabels];
            } else {
                $groups[] = ['key' => $key, 'title' => $fixedTitles[$key], 'sublabels' => ['']];
            }
        }

        return $groups;
    }
=======
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc
}
