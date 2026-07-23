<?php

namespace App\Services\DocxTemplates;

use App\Models\Rfq;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

/**
 * Builds the RFQ memo document, matching the sample:
 * "2_Laptop_RFQ_Sample.doc"
 *
 * NB — data gaps vs. the sample (flagged with [placeholder] text so
 * they're obvious to fill in Word afterwards):
 *  - Project name & District aren't fields anywhere in the current
 *    schema (PurchaseRequisition/ProcurementPlan). Left as editable
 *    placeholders. Add `project_name`/`district` columns if you want
 *    these to auto-fill later.
 *  - Unit Price / Total Price columns are intentionally left BLANK —
 *    this document is the blank template sent OUT to vendors for them
 *    to quote against, not a record of prices we already have.
 */
class RfqDocumentBuilder
{
    public function build(Rfq $rfq): PhpWord
    {
        $rfq->loadMissing('procurementPlan.purchaseRequisition.items.item', 'procurementPlan.purchaseRequisition.items.unit');

        $pr = $rfq->procurementPlan?->purchaseRequisition;
        $items = $pr?->items ?? collect();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginLeft' => 1000, 'marginRight' => 1000, 'marginTop' => 1000, 'marginBottom' => 1000,
        ]);

        $bold = ['bold' => true];
        $center = ['alignment' => Jc::CENTER];

        // ---- Header ----
        $section->addText(
            "Memo: {$rfq->rfq_number}          Date: " . optional($rfq->issue_date)->format('d.m.Y'),
            $bold
        );
        $section->addTextBreak(1);
        $section->addText('Request for Quotation (RFQ)', array_merge($bold, ['underline' => 'single', 'size' => 13]), $center);
        $section->addTextBreak(1);

        $closing = optional($rfq->closing_date)->format('d F, Y \a\t 04:00 PM');
        $section->addText(
            'Eco-Social Development Organization (ESDO) is hereby requesting quotation from original '
            . 'vendors/Suppliers for supplying items under ESDO- "[Project Name]" in [District]. '
            . 'Interested Vendors/Suppliers are requested to submit Quotation through Courier or directly '
            . "according to the below mentioned terms & conditions by or before {$closing} addressing to "
            . '"Convener, Central Procurement Committee, Eco-Social Development Organization (ESDO), '
            . 'House # 748, Road# 8 Adabor, Dhaka".'
        );
        $section->addTextBreak(1);

        // ---- Item table ----
        $table = $section->addTable([
            'borderSize' => 6, 'borderColor' => '000000',
        ]);
        $widths = [700, 4500, 1200, 1400, 1400]; // SL, Item Details, Qty, Unit Price, Total Price

        $table->addRow();
        foreach (['SL No', 'Item Details', 'Qty', 'Unit Price', 'Total Price'] as $i => $h) {
            $table->addCell($widths[$i])->addText($h, $bold, $center);
        }

        $sl = 1;
        foreach ($items as $line) {
            $table->addRow();
            $table->addCell($widths[0])->addText((string) $sl);
            $table->addCell($widths[1])->addText(
                ($line->item->name ?? '') . ($line->item->specification ? ': ' . $line->item->specification : '')
            );
            $table->addCell($widths[2])->addText($line->quantity . ' ' . ($line->unit->symbol ?? $line->unit->name ?? ''));
            $table->addCell($widths[3])->addText('');
            $table->addCell($widths[4])->addText('');
            $sl++;
        }

        if ($items->isEmpty()) {
            $table->addRow();
            $table->addCell($widths[0])->addText('1');
            $table->addCell($widths[1])->addText('[No linked PR items found]');
            $table->addCell($widths[2])->addText('');
            $table->addCell($widths[3])->addText('');
            $table->addCell($widths[4])->addText('');
        }

        $table->addRow();
        $totalCell = $table->addCell($widths[0] + $widths[1] + $widths[2], ['gridSpan' => 3]);
        $totalCell->addText('Total with Vat-Tax', $bold);
        $table->addCell($widths[3])->addText('');
        $table->addCell($widths[4])->addText('');

        $section->addTextBreak(1);

        // ---- Terms & Conditions ----
        $section->addText('Terms & Conditions:', array_merge($bold, ['underline' => 'single']));
        $terms = [
            'Photocopy of Valid Trade license, TIN certificate, PSR Copy, BIN Certificate, RFQ receiving copy need to attach with the Quotation.',
            'Quotation will be opened at ' . (optional($rfq->closing_date)->format('h:i A') ?: '[time]') . ' on ' . (optional($rfq->closing_date)->format('d F, Y') ?: '[date]') . ' (Those who will submit the quotation are invited to present at the time of opening).',
            'Items must be delivered to the project office location as specified in the Procurement Plan.',
            'As per govt. rules and regulation vat & tax will be deducted at the time of payment.',
            'The given price of the product must be valid for at least 15 days, and within this time frame the supplier is bound to supply products at the given price.',
            'Mode of payment: Payment will be made through Account Payee cheque/Pay order/RTGS/BEFTN or DD in favour of the supplying vendor after successful delivery of goods.',
            'ESDO reserves the authority to cancel — partially or fully — any quotation with or without explanation.',
            'ESDO never allows any harassment to women and children, and never allows child labour. Any institution or organization associated with such practices is strongly discouraged from participating in the bid.',
        ];
        foreach ($terms as $i => $t) {
            $section->addText(($i + 1) . '. ' . $t);
        }

        $section->addTextBreak(2);
        $section->addText('Thanks, with best regards');
        $section->addTextBreak(1);

        // ---- Signature block — pulled from the Central Procurement Committee ----
        [$name, $designation] = $this->conveningOfficer();
        $section->addText("({$name}) Receiver's Signature & Seal", $bold);
        $section->addText($designation);
        $section->addText('Central Procurement Committee, ESDO, Dhaka-1207.');

        return $phpWord;
    }

    /**
     * Pulls the Convener/Member Secretary of the "Central Procurement
     * Committee" for the signature block, if that committee + members
     * are seeded. Falls back to a placeholder if not found.
     */
    protected function conveningOfficer(): array
    {
        $member = \App\Models\CommitteeMember::query()
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
