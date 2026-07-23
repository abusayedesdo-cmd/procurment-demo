<?php

namespace App\Services\DocxTemplates;

use App\Models\TenderOpening;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the Tender Opening Report, matching the sample:
 * "Tender_Opening_Requerd_from.docx"
 *
 * Data sources:
 *  - Committee members: CommitteeMember rows under the "Central
 *    Procurement Committee" (same lookup as the RFQ builder).
 *  - Bidder rows: Quotation records for this RFQ (vendor + quoted amount).
 *  - Document checklist (Trade License / TIN / BIN): derived from
 *    VendorDocument rows per bidder, mapped as:
 *      Trade License -> document_type 'trade_license'
 *      TIN           -> document_type 'tax_certificate'
 *      BIN           -> document_type 'vat_certificate'
 *    (The schema doesn't have distinct TIN/BIN types; this mapping is
 *    the closest fit. Adjust in code if your team labels these
 *    differently.)
 *  - Attendance sheet: same bidder list, signature column left blank
 *    for physical signing on the printed copy.
 */
class TenderOpeningDocumentBuilder
{
    public function build(TenderOpening $opening): PhpWord
    {
        $opening->loadMissing('rfq.procurementPlan.purchaseRequisition', 'openedBy');
        $rfq = $opening->rfq;

        $quotations = \App\Models\Quotation::query()
            ->where('rfq_id', $rfq->id)
            ->with('vendor')
            ->get();

        $vendorDocsByVendor = \App\Models\VendorDocument::query()
            ->whereIn('vendor_id', $quotations->pluck('vendor_id'))
            ->where('verified', true)
            ->get()
            ->groupBy('vendor_id');

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $bold = ['bold' => true];
        $center = ['alignment' => Jc::CENTER];
        $section = $phpWord->addSection(['marginLeft' => 1000, 'marginRight' => 1000, 'marginTop' => 1000, 'marginBottom' => 1000]);

        $section->addText('TENDER OPENING REPORT', array_merge($bold, ['underline' => 'single', 'size' => 14]), $center);
        $section->addTextBreak(1);
        $section->addText("RFQ/Tender Reference: {$rfq->rfq_number}", $bold);
        $section->addText('Opening Date: ' . optional($opening->opening_date)->format('d F, Y'));
        $section->addText('Opened By: ' . ($opening->openedBy->name ?? '[Name]'));
        $section->addTextBreak(1);

        // ---- Opening Committee ----
        $section->addText('Tender Opening Committee', array_merge($bold, ['underline' => 'single']));
        $committee = \App\Models\CommitteeMember::query()
            ->whereHas('committee', fn ($q) => $q->where('name', 'Central Procurement Committee'))
            ->with('user')
            ->get();

        $committeeTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $committeeTable->addRow();
        foreach (['SL', 'Name', 'Designation', 'Signature'] as $h) {
            $committeeTable->addCell(2250)->addText($h, $bold, $center);
        }
        if ($committee->isEmpty()) {
            $committeeTable->addRow();
            $committeeTable->addCell(2250)->addText('1');
            $committeeTable->addCell(2250)->addText('[No committee members seeded]');
            $committeeTable->addCell(2250)->addText('');
            $committeeTable->addCell(2250)->addText('');
        }
        foreach ($committee as $i => $member) {
            $committeeTable->addRow();
            $committeeTable->addCell(2250)->addText((string) ($i + 1));
            $committeeTable->addCell(2250)->addText($member->user->name ?? '');
            $committeeTable->addCell(2250)->addText($member->designation_in_committee ?? '');
            $committeeTable->addCell(2250)->addText('');
        }

        // ---- Bidder List + Document Checklist ----
        $section->addTextBreak(2);
        $section->addText('Bidder List & Document Checklist', array_merge($bold, ['underline' => 'single']));

        $bidTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $widths = [500, 2500, 1600, 1300, 1200, 1200, 1200];
        $bidTable->addRow();
        foreach (['SL', 'Bidder / Vendor', 'Bid Price (BDT)', 'Trade License', 'TIN', 'BIN', 'Remarks'] as $i => $h) {
            $bidTable->addCell($widths[$i])->addText($h, $bold, $center);
        }

        $mapCheck = fn ($vendorId, $type) => ($vendorDocsByVendor->get($vendorId, collect())->firstWhere('document_type', $type)) ? 'Yes' : 'No';

        foreach ($quotations as $i => $q) {
            $bidTable->addRow();
            $bidTable->addCell($widths[0])->addText((string) ($i + 1));
            $bidTable->addCell($widths[1])->addText($q->vendor->name ?? '');
            $bidTable->addCell($widths[2])->addText(number_format((float) $q->quoted_amount, 2));
            $bidTable->addCell($widths[3])->addText($mapCheck($q->vendor_id, 'trade_license'), [], $center);
            $bidTable->addCell($widths[4])->addText($mapCheck($q->vendor_id, 'tax_certificate'), [], $center);
            $bidTable->addCell($widths[5])->addText($mapCheck($q->vendor_id, 'vat_certificate'), [], $center);
            $bidTable->addCell($widths[6])->addText('');
        }

        if ($quotations->isEmpty()) {
            $bidTable->addRow();
            $bidTable->addCell(array_sum($widths), ['gridSpan' => 7])->addText('[No quotations recorded against this RFQ yet]');
        }

        // ---- Bidder Attendance Sheet ----
        $section->addTextBreak(2);
        $section->addText('Bidder Representative Attendance', array_merge($bold, ['underline' => 'single']));
        $attTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $attTable->addRow();
        foreach (['SL', 'Vendor', 'Representative Name', 'Contact No.', 'Signature'] as $h) {
            $attTable->addCell(1800)->addText($h, $bold, $center);
        }
        foreach ($quotations as $i => $q) {
            $attTable->addRow();
            $attTable->addCell(1800)->addText((string) ($i + 1));
            $attTable->addCell(1800)->addText($q->vendor->name ?? '');
            $attTable->addCell(1800)->addText('');
            $attTable->addCell(1800)->addText('');
            $attTable->addCell(1800)->addText('');
        }
        if ($quotations->isEmpty()) {
            $attTable->addRow();
            $attTable->addCell(9000, ['gridSpan' => 5])->addText('[No bidders to list]');
        }

        $section->addTextBreak(2);
        $section->addText('Remarks: ' . ($opening->remarks ?: '__________________________________________________'));

        return $phpWord;
    }
}
