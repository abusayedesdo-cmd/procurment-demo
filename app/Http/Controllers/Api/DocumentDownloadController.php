<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\ProcurementCommitteeMember;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\TenderOpening;
use App\Models\VendorDocument;
use App\Models\ProcurementAnnualPlan;
use App\Models\PurchaseRequisition;
use App\Models\EligibilityReport;
use App\Models\TechnicalEvaluationReport;
use App\Models\FinancialEvaluationReport;
use App\Models\ComparativeStatement;
use App\Models\CommitteeMember;
use App\Services\NumberGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
            'procurementCase.purchaseRequisition.items.item',
            'procurementCase.purchaseRequisition.items.unit',
            'procurementCase.purchaseRequisition.category'
        );
        $items = $rfq->procurementCase?->purchaseRequisition?->items ?? collect();

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
            'procurementCase.purchaseRequisition.items.item.chartOfAccount',
            'procurementCase.purchaseRequisition.items.unit',
            'procurementCase.purchaseRequisition.procurementPlan'
        );
        $items = $rfq->procurementCase?->purchaseRequisition?->items ?? collect();
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

    /**
     * Same document as tenderSchedule(), but streamed with an inline
     * Content-Disposition so the "Preview" button opens it in the
     * browser's own PDF viewer instead of forcing a download.
     */
    public function tenderSchedulePreview(Rfq $rfq)
    {
        $rfq->loadMissing(
            'procurementCase.purchaseRequisition.items.item.chartOfAccount',
            'procurementCase.purchaseRequisition.items.unit',
            'procurementCase.purchaseRequisition.procurementPlan'
        );
        $items = $rfq->procurementCase?->purchaseRequisition?->items ?? collect();
        $itemsByCategory = $items->groupBy(fn ($line) => $line->item->chartOfAccount->name ?? 'General');

        $pdf = Pdf::loadView('documents.tender-schedule', [
            'rfq' => $rfq,
            'itemsByCategory' => $itemsByCategory,
            'validityDays' => self::VALIDITY_DAYS,
            'performanceSecurityPercent' => self::PERFORMANCE_SECURITY_PERCENT,
            'delayPenaltyPercent' => self::DELAY_PENALTY_PERCENT_PER_WEEK,
            'technicalCriteria' => self::TECHNICAL_CRITERIA,
        ]);

        return $pdf->stream("Tender-Schedule-{$this->safe($rfq->rfq_number)}.pdf");
    }

    public function eligibilityReport(EligibilityReport $eligibilityReport)
    {
        $eligibilityReport->loadMissing('rfq', 'preparedBy', 'items.vendor');

        $pdf = Pdf::loadView('documents.eligibility-report', [
            'report' => $eligibilityReport,
            'rfq' => $eligibilityReport->rfq,
        ]);

        return $pdf->download("Eligibility-Report-{$this->safe($eligibilityReport->rfq->rfq_number)}.pdf");
    }

    public function eligibilityReportPreview(EligibilityReport $eligibilityReport)
    {
        $eligibilityReport->loadMissing('rfq', 'preparedBy', 'items.vendor');

        $pdf = Pdf::loadView('documents.eligibility-report', [
            'report' => $eligibilityReport,
            'rfq' => $eligibilityReport->rfq,
        ]);

        return $pdf->stream("Eligibility-Report-{$this->safe($eligibilityReport->rfq->rfq_number)}.pdf");
    }

    public function technicalEvaluationReport(TechnicalEvaluationReport $technicalEvaluationReport)
    {
        $technicalEvaluationReport->loadMissing('rfq', 'preparedBy', 'items.vendor');

        $pdf = Pdf::loadView('documents.technical-evaluation-report', [
            'report' => $technicalEvaluationReport,
            'rfq' => $technicalEvaluationReport->rfq,
        ]);

        return $pdf->download("Technical-Evaluation-Report-{$this->safe($technicalEvaluationReport->rfq->rfq_number)}.pdf");
    }

    public function technicalEvaluationReportPreview(TechnicalEvaluationReport $technicalEvaluationReport)
    {
        $technicalEvaluationReport->loadMissing('rfq', 'preparedBy', 'items.vendor');

        $pdf = Pdf::loadView('documents.technical-evaluation-report', [
            'report' => $technicalEvaluationReport,
            'rfq' => $technicalEvaluationReport->rfq,
        ]);

        return $pdf->stream("Technical-Evaluation-Report-{$this->safe($technicalEvaluationReport->rfq->rfq_number)}.pdf");
    }

    public function financialEvaluationReport(FinancialEvaluationReport $financialEvaluationReport)
    {
        $financialEvaluationReport->loadMissing('rfq', 'preparedBy', 'items.vendor');

        $pdf = Pdf::loadView('documents.financial-evaluation-report', [
            'report' => $financialEvaluationReport,
            'rfq' => $financialEvaluationReport->rfq,
        ]);

        return $pdf->download("Financial-Evaluation-Report-{$this->safe($financialEvaluationReport->rfq->rfq_number)}.pdf");
    }

    public function financialEvaluationReportPreview(FinancialEvaluationReport $financialEvaluationReport)
    {
        $financialEvaluationReport->loadMissing('rfq', 'preparedBy', 'items.vendor');

        $pdf = Pdf::loadView('documents.financial-evaluation-report', [
            'report' => $financialEvaluationReport,
            'rfq' => $financialEvaluationReport->rfq,
        ]);

        return $pdf->stream("Financial-Evaluation-Report-{$this->safe($financialEvaluationReport->rfq->rfq_number)}.pdf");
    }

    public function comparativeStatement(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->loadMissing('rfq', 'preparedBy', 'lowestEvaluatedVendor', 'items.vendor');

        $pdf = Pdf::loadView('documents.comparative-statement', [
            'statement' => $comparativeStatement,
            'rfq' => $comparativeStatement->rfq,
        ]);

        return $pdf->download("Comparative-Statement-{$this->safe($comparativeStatement->rfq->rfq_number)}.pdf");
    }

    public function comparativeStatementPreview(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->loadMissing('rfq', 'preparedBy', 'lowestEvaluatedVendor', 'items.vendor');

        $pdf = Pdf::loadView('documents.comparative-statement', [
            'statement' => $comparativeStatement,
            'rfq' => $comparativeStatement->rfq,
        ]);

        return $pdf->stream("Comparative-Statement-{$this->safe($comparativeStatement->rfq->rfq_number)}.pdf");
    }

    public function tenderOpening(TenderOpening $tenderOpening)
    {
        $tenderOpening->loadMissing('rfq.procurementCase.purchaseRequisition', 'openedBy');
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

    /**
     * The paper "Purchase Requisition" form — Sl.No/Item/Specification/
     * Unit/Qty/Unit Price/Total/A-C Code table, delivery block, amount
     * in words, Budgetary Check block, and the signature lines. Once an
     * Accountant has recorded a budget check (pr_budget_checks), those
     * figures print in the Budgetary Check box; otherwise it prints
     * blank for hand signing, as before.
     */
    public function purchaseRequisitionPdf(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->loadMissing('items.item', 'items.unit', 'raisedBy', 'approvals.user');

        $budgetCheck = $purchaseRequisition->budgetChecks()
            ->with(['budgetLine', 'checkedBy'])
            ->latest('checked_at')
            ->latest('id')
            ->first();

        // Signature-line lookups: latest "approved" action recorded for
        // each role in the chain, so the printed PR shows who actually
        // endorsed/recommended/approved it instead of a blank line.
        // "Finance Requested by" reuses the Budget Checker's approval —
        // same person as "Name of Accountant" above, just a second
        // signature line on the paper form.
        $approvalByRole = fn (string $role) => $purchaseRequisition->approvals
            ->where('role_at_action', $role)
            ->where('action', 'approved')
            ->sortBy([['acted_at', 'desc'], ['id', 'desc']])
            ->first();

        $pdf = Pdf::loadView('documents.purchase-requisition', [
            'pr' => $purchaseRequisition,
            'amountInWords' => $this->amountInWords((float) $purchaseRequisition->total_estimated_amount),
            'budgetCheck' => $budgetCheck,
            'endorsedBy' => $approvalByRole('Reviewer'),
            'financeRequestedBy' => $approvalByRole('Budget Checker'),
            'recommendedBy' => $approvalByRole('Focal Person'),
            'approvedBy' => $approvalByRole('Executive Director'),
        ]);

        return $pdf->download("PR-{$this->safe($purchaseRequisition->pr_number)}.pdf");
    }

    /** Bangladeshi grouping (Crore / Lakh / Thousand), for the "In-word" line. */
    protected function amountInWords(float $amount): string
    {
        $num = (int) round($amount);
        if ($num === 0) {
            return 'Zero Taka Only';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $twoDigits = function (int $x) use ($ones, $tens) {
            if ($x < 20) {
                return $ones[$x];
            }

            return trim($tens[intdiv($x, 10)].($x % 10 ? ' '.$ones[$x % 10] : ''));
        };
        $threeDigits = function (int $x) use ($ones, $twoDigits) {
            if ($x < 100) {
                return $twoDigits($x);
            }

            return trim($ones[intdiv($x, 100)].' Hundred'.($x % 100 ? ' '.$twoDigits($x % 100) : ''));
        };

        $rem = $num;
        $crore = intdiv($rem, 10000000); $rem %= 10000000;
        $lakh = intdiv($rem, 100000); $rem %= 100000;
        $thousand = intdiv($rem, 1000); $rem %= 1000;
        $hundred = $rem;

        $parts = [];
        if ($crore) $parts[] = $threeDigits($crore).' Crore';
        if ($lakh) $parts[] = $threeDigits($lakh).' Lakh';
        if ($thousand) $parts[] = $threeDigits($thousand).' Thousand';
        if ($hundred) $parts[] = $threeDigits($hundred);

        return implode(' ', $parts).' Taka Only';
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
    protected function conveningOfficer(string $committeeType = ProcurementCommitteeMember::CENTRAL_PROCUREMENT): array
    {
        $member = ProcurementCommitteeMember::where('active', true)
            ->where('committee_type', $committeeType)
            ->where('role', 'member_secretary')
            ->orderBy('sort_order')
            ->first();

        if ($member) {
            return [$member->name, $member->roleLabel()];
        }

        return ['[Member Secretary Name]', 'Member Secretary/Convener'];
    }

    /** The committee Convener specifically (for meeting/RFQ minutes/notice sign-off). */
    protected function committeeConvener(string $committeeType = ProcurementCommitteeMember::CENTRAL_PROCUREMENT): ?ProcurementCommitteeMember
    {
        return ProcurementCommitteeMember::where('active', true)
            ->where('committee_type', $committeeType)
            ->where('role', 'convener')
            ->orderBy('sort_order')
            ->first();
    }

    public function meetingNotice(Meeting $meeting, NumberGeneratorService $numbers)
    {
        $meeting->load('procurementCase.purchaseRequisition');

        if (! $meeting->notice_number) {
            $meeting->update([
                'notice_number' => $numbers->nextDocMemo('Procurement', 'Notice'),
                'notice_date' => now(),
            ]);
        }

        $pdf = Pdf::loadView('documents.meeting-notice', [
            'meeting' => $meeting,
            'case' => $meeting->procurementCase,
            'convener' => $this->committeeConvener(),
        ]);

        return $pdf->download("Meeting-Notice-{$this->safe($meeting->notice_number)}.pdf");
    }

    public function meetingAttendance(Meeting $meeting, NumberGeneratorService $numbers)
    {
        $meeting->load('procurementCase.purchaseRequisition', 'attendees');

        if (! $meeting->attendance_number) {
            $meeting->update([
                'attendance_number' => $numbers->nextDocMemo('Procurement', 'Attendence'),
            ]);
        }

        $pdf = Pdf::loadView('documents.meeting-attendance', [
            'meeting' => $meeting,
            'case' => $meeting->procurementCase,
            'roster' => ProcurementCommitteeMember::activeRoster(),
            'convener' => $this->committeeConvener(),
        ]);

        return $pdf->download("Meeting-Attendance-{$this->safe($meeting->attendance_number)}.pdf");
    }

    public function meetingMinutes(Meeting $meeting)
    {
        $meeting->load('procurementCase.purchaseRequisition', 'attendees', 'awards.vendor');

        $pdf = Pdf::loadView('documents.meeting-minutes', [
            'meeting' => $meeting,
            'case' => $meeting->procurementCase,
            'convener' => $this->committeeConvener(),
        ]);

        return $pdf->download("Rezulation-Minutes-{$meeting->rezulation_no}.pdf");
    }

    public function annualPlanPdf(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->load('packages.periods.entries', 'packages.category', 'packages.chartOfAccount', 'packages.item.chartOfAccount');
        $layout = $this->buildAnnualPlanLayout($procurementAnnualPlan);

        $pdf = Pdf::loadView('documents.annual-plan-pdf', ['plan' => $procurementAnnualPlan, 'layout' => $layout])
            ->setPaper('a3', 'landscape');

        return $pdf->download('annual-plan-' . $procurementAnnualPlan->id . '.pdf');
    }

    /**
     * Same document as annualPlanPdf(), but streamed with an inline
     * Content-Disposition so the "Preview" button opens it in the
     * browser's own PDF viewer instead of forcing a download.
     */
    public function annualPlanPdfPreview(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->load('packages.periods.entries', 'packages.category', 'packages.chartOfAccount', 'packages.item.chartOfAccount');
        $layout = $this->buildAnnualPlanLayout($procurementAnnualPlan);

        $pdf = Pdf::loadView('documents.annual-plan-pdf', ['plan' => $procurementAnnualPlan, 'layout' => $layout])
            ->setPaper('a3', 'landscape');

        return $pdf->stream('annual-plan-' . $procurementAnnualPlan->id . '.pdf');
    }

    public function annualPlanExcel(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->load('packages.periods.entries', 'packages.category', 'packages.chartOfAccount', 'packages.item.chartOfAccount');
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
        $sheet->setCellValue("C{$r1}", 'Sub Category');
        $sheet->setCellValue("D{$r1}", 'Item Name');
        $sheet->setCellValue("E{$r1}", 'Specification');
        $sheet->setCellValue("F{$r1}", 'Unit');
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $letter) {
            $sheet->mergeCells("{$letter}{$r1}:{$letter}{$r3}");
        }

        $colIndex = 7;
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

        $rowNumber = 0;
        foreach ($procurementAnnualPlan->packages as $pkg) {
            $rowNumber++;
            $colIndex = 1;
            $setCell($colIndex++, $row, $pkg->sl_no ?? $rowNumber);
            $setCell($colIndex++, $row, $pkg->category->name);
            $setCell($colIndex++, $row, $pkg->chartOfAccount->name ?? $pkg->item?->chartOfAccount?->name ?? '');
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
        $sheet->mergeCells("A{$row}:F{$row}");
        $colIndex = 7;
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

        // Mirrors updateYearSlotLabels() in the live view exactly, so the PDF/Excel FY
        // labels always match what the "Select Year" dropdown showed when the data was
        // entered: y = fiscal_year_start.year + 2, then Prev2=y, Prev1=y-1, Current=y-2,
        // Year2=y+1, Year3=y+2 (yes, "Previous 2nd Year" lands on the LATER year number —
        // that's the existing convention, kept as-is for consistency with the live view).
        $y = $plan->fiscal_year_start ? $plan->fiscal_year_start->year + 2 : now()->year;
        $shortYear = fn (int $year): string => substr((string) $year, -2);
        $fyStartYear = [
            'previous_2nd_year' => $y,
            'previous_1st_year' => $y - 1,
            'current_year' => $y - 2,
            'year_2_total' => $y + 1,
            'year_3_total' => $y + 2,
        ];
        // Only "Current Year" is actually entered July-first (FISCAL_MONTH_NAMES in the
        // live form, matching Quarter-1 "July-October"); every other breakable slot is
        // entered plain January-first (MONTH_NAMES) as a single calendar year. Column
        // order here must match how entries were actually saved (ordered by slot_number),
        // or values will land under the wrong month header.
        $fiscalMonths = ['July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May', 'June'];
        $calendarMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        $groups = [];

        // Same rule as the live view's matrix (computeVisiblePeriods): a period slot
        // only gets a column if at least one package actually has a value in it.
        // An empty plan (no packages yet) still shows every column, same as the live view.
        $hasAnyValue = fn (string $key): bool => $plan->packages->isEmpty()
            || $plan->packages->contains(function ($pkg) use ($key) {
                $period = $pkg->periodBySlotKey($key);

                return $period && ((float) $period->no_of_unit !== 0.0 || (float) $period->rate !== 0.0 || (float) $period->total !== 0.0);
            });

        foreach ($fixedOrder as $key) {
                if (! $hasAnyValue($key)) {
                    continue;
                }

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

                if ($granularity === 'month') {
                    $yStart = $fyStartYear[$key];
                    if ($key === 'current_year') {
                        // July-December -> yStart, January-June -> yStart + 1
                        $sublabels = [];
                        foreach ($fiscalMonths as $i => $monthName) {
                            $sublabels[] = $monthName . '-' . $shortYear($i < 6 ? $yStart : $yStart + 1);
                        }
                    } else {
                        // Plain calendar Jan-Dec, all within the same single year.
                        $sublabels = array_map(fn ($monthName) => $monthName . '-' . $shortYear($yStart), $calendarMonths);
                    }
                } else {
                    $sublabels = [''];
                }

                $groups[] = ['key' => $key, 'title' => $breakableTitles[$key], 'sublabels' => $sublabels];
            } else {
                $groups[] = ['key' => $key, 'title' => $fixedTitles[$key], 'sublabels' => ['']];
            }
        }

        return $groups;
    }
}