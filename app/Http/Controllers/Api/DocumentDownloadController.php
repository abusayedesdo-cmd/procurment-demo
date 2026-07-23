<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\TenderOpening;
use App\Models\VendorDocument;
use Barryvdh\DomPDF\Facade\Pdf;

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
}
