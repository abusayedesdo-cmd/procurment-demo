<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the RFQ memo document. Mirrors resources/views/documents/rfq.blade.php
 * exactly (same wording, same item table, same terms), just rendered as an
 * editable .docx instead of a PDF.
 *
 * Expects the same $data array DocumentDownloadController::rfqViewData()
 * already builds for the PDF view: ['rfq', 'items', 'signatoryName', 'signatoryTitle'].
 */
class RfqDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $rfq = $data['rfq'];
        $items = $data['items'];
        $signatoryName = $data['signatoryName'];
        $signatoryTitle = $data['signatoryTitle'];

        $pr = $rfq->procurementCase?->purchaseRequisition;
        $categoryName = $pr?->category?->name ?? '[Category Name]';

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead(
            $section,
            'Eco-Social Development Organization (ESDO)',
            'House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207'
        );

        $memoTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $memoTable->addRow();
        $memoTable->addCell(5000)->addText('Memo: ' . $rfq->rfq_number, $this->b());
        $memoTable->addCell(4000)->addText('Date: ' . $this->fmtDate($rfq->issue_date), $this->b(), ['alignment' => Jc::END]);
        $section->addTextBreak(1);

        $typeHeadings = [
            'RFQ' => 'Request for Quotation (RFQ)',
            'OTM' => 'Open Tender Method (OTM)',
            'RFP' => 'Request for Proposal / Expression of Interest / Vendor-Consultant Hiring',
        ];
        $section->addText($typeHeadings[$rfq->type] ?? $typeHeadings['RFQ'], array_merge($this->b(), ['size' => 13, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);

        $closing = $this->fmtDate($rfq->closing_date);
        if ($rfq->type === 'RFP') {
            $section->addText(
                "Eco-Social Development Organization (ESDO) is hereby inviting proposals/expressions of interest from "
                . "qualified {$categoryName} vendors/consultants for \"" . ($rfq->subject ?: '[Project Name]') . '" in '
                . ($pr?->delivery_location ?? '[Project Location]') . '. Interested Vendors/Consultants are requested to submit '
                . 'their proposal through courier or directly according to the below mentioned terms & conditions by or before on '
                . "{$closing} at 04:00 PM addressing to \"Convener, Central Procurement Committee, Eco-Social Development "
                . 'Organization (ESDO), House # 748, Road# 8 Adabor, Dhaka".'
            );
        } else {
            $section->addText(
                "Eco-Social Development Organization (ESDO) is hereby requesting quotation as per following {$categoryName} "
                . "vendors/suppliers for supplying {$categoryName} equipment under \"" . ($rfq->subject ?: '[Project Name]') . '" in '
                . ($pr?->delivery_location ?? '[Project Location]') . '. Interested Vendors/Suppliers are requested to submit '
                . 'quotation through courier or directly according to the below mentioned terms & conditions by or before on '
                . "{$closing} at 04:00 PM addressing to \"Convener, Central Procurement Committee, Eco-Social Development "
                . 'Organization (ESDO), House # 748, Road# 8 Adabor, Dhaka".'
            );
        }

        $section->addText('Items Details:', $this->b());

        $table = $section->addTable($this->borderedTableStyle());
        $widths = [700, 2400, 3800, 1100, 900, 1600, 1600];
        $headers = ['Sl. No.', 'Item Name', 'Item Specification', 'Unit', 'Qty.', 'Unit Price', 'Total Price'];
        $table->addRow();
        foreach ($headers as $i => $h) {
            $table->addCell($widths[$i], $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }

        if ($items->isEmpty()) {
            $table->addRow();
            $table->addCell($widths[0])->addText('1');
            $table->addCell($widths[1] + $widths[2], ['gridSpan' => 2])->addText('[No linked PR items found]');
            foreach (array_slice($widths, 3) as $w) {
                $table->addCell($w)->addText('');
            }
        } else {
            foreach ($items as $i => $line) {
                $table->addRow();
                $table->addCell($widths[0])->addText((string) ($i + 1));
                $table->addCell($widths[1])->addText($line->item->name ?? '');
                $table->addCell($widths[2])->addText((string) $line->specification);
                $table->addCell($widths[3])->addText($line->unit->symbol ?? $line->unit->name ?? '');
                $table->addCell($widths[4])->addText((string) $line->quantity);
                $table->addCell($widths[5])->addText('');
                $table->addCell($widths[6])->addText('');
            }
        }

        $table->addRow();
        $table->addCell($widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4], ['gridSpan' => 5])
            ->addText('Total Amount with VAT & Tax', $this->b());
        $table->addCell($widths[5])->addText('');
        $table->addCell($widths[6])->addText('');

        // The old fixed 11-point list, used only as a fallback for RFQs
        // that don't have any item picked from the editable master list
        // below (e.g. RFQs created before this feature existed).
        $fallbackTerms = [
            'Legal Document PDF Copy must be Submitted with Quotation: (Trade License, VAT Registration, TIN Certificate, PSR)',
            'Relevant Experience Certificate PDF Copy must be Submitted with Quotation.',
            'General Experience Certificate PDF Copy must be Submitted with Quotation.',
            'RFQ Receiving PDF Copy Need to Attach with the Quotation.',
            'As per govt. rules and regulation vat & tax will be deducted at the time of payment.',
            'The given price of the product must be valid for at least 15 days, and within this time frame the supplier is bound to supply products at the given price.',
            'Mode of payment: Payment will be made through Account Payee cheque/Pay order/RTGS/BEFTN or DD in favour of the supplying vendor after successful delivery of goods.',
            'ESDO reserves the authority to cancel — partially or fully — any quotation with or without explanation.',
            'ESDO never allows any harassment to women and children, and never allows child labour. Any institution or organization associated with such practices is strongly discouraged from participating in the bid.',
        ];

        /** @var \Illuminate\Support\Collection $selectedTerms */
        $selectedTerms = $data['termsConditions'] ?? collect();
        $pickedTerms = $selectedTerms->isNotEmpty()
            ? $selectedTerms->pluck('text')->all()
            : $fallbackTerms;

        $section->addTextBreak(1);
        $section->addText('Terms & Conditions', array_merge($this->b(), ['size' => 12, 'underline' => 'single']));
        $this->addNumberedList($section, array_merge([
            // Per-RFQ facts — always generated fresh, not editable from the list.
            'Quotation will be Opened on ' . ($closing ?: '[date]') . ' at 04:00 PM (Those who will submit the quotation are invited to present at the opening time).',
            'Delivery Location: equipment must be delivered to "[Delivery Schedule]" office in ' . ($pr?->delivery_location ?? '[Project Location]') . '.',
        ], $pickedTerms));

        if ($rfq->distribution_process) {
            $section->addTextBreak(1);
            $section->addText('Distribution Process: ' . $rfq->distribution_process, $this->b());
        }

        $section->addTextBreak(1);
        $section->addText('Thanks, with best regards');
        $section->addTextBreak(1);

        $sigTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sigTable->addRow();
        $left = $sigTable->addCell(5000);
        $left->addText("({$signatoryName})", $this->b());
        $left->addText($signatoryTitle);
        $left->addText('Central Procurement Committee, ESDO, Dhaka-1207.');
        $sigTable->addCell(4000, ['valign' => 'bottom'])->addText('Receivers Signature & Seal', [], ['alignment' => Jc::END]);

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
