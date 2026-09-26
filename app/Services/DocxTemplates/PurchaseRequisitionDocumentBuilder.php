<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the paper Purchase Requisition form. Mirrors
 * resources/views/documents/purchase-requisition.blade.php.
 *
 * Expects: ['pr', 'amountInWords', 'budgetCheck', 'endorsedBy',
 * 'financeRequestedBy', 'recommendedBy', 'approvedBy'].
 */
class PurchaseRequisitionDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $data): PhpWord
    {
        $pr = $data['pr'];
        $budgetCheck = $data['budgetCheck'];
        $endorsedBy = $data['endorsedBy'];
        $financeRequestedBy = $data['financeRequestedBy'];
        $recommendedBy = $data['recommendedBy'];
        $approvedBy = $data['approvedBy'];

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $head = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $head->addRow();
        $logo = public_path('img/esdo-logo.png');
        $logoCell = $head->addCell(1600, ['valign' => 'top']);
        if (file_exists($logo)) {
            $logoCell->addImage($logo, ['width' => 45, 'height' => 45]);
        } else {
            $logoCell->addText('');
        }
        $mid = $head->addCell(4800, ['valign' => 'top']);
        $mid->addText('Eso-Social Development Organization (ESDO)', array_merge($this->b(), ['size' => 12]), $this->c());
        $mid->addText('Collegepara(Gobindanagar), Thakurgaon-5100, Bangladesh', ['size' => 8], $this->c());
        $mid->addText('Purchase Requisition', array_merge($this->b(), ['size' => 11]), $this->c());
        $right = $head->addCell(2600, ['valign' => 'top']);
        $right->addText('PR NO. ' . $pr->pr_number, $this->b(), ['alignment' => Jc::END]);
        $right->addText('Date: ' . $this->fmtDate($pr->requisition_date, 'd.m.Y'), [], ['alignment' => Jc::END]);

        $section->addTextBreak(1);

        $meta = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $meta->addRow();
        $meta->addCell(3000)->addText('Project: ' . ($pr->project_name ?? ''));
        $meta->addCell(3000)->addText('Requestor: ' . ($pr->requestor_name ?? ''));
        $meta->addCell(3000)->addText('Designation: ' . ($pr->requestor_designation ?? ''));

        $itemsTable = $section->addTable($this->borderedTableStyle());
        $itemsTable->addRow();
        $cols = [
            ['Sl.No', 500], ['Item Name', 1800], ['Specification', 2200], ['Unit', 700],
            ['Quantity', 900], ['Unit Price', 1100], ['Total Price', 1100], ['A/C Code/Remarks', 1400],
        ];
        foreach ($cols as [$h, $w]) {
            $itemsTable->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }
        foreach ($pr->items as $i => $line) {
            $itemsTable->addRow();
            $itemsTable->addCell(500)->addText((string) ($i + 1));
            $itemsTable->addCell(1800)->addText($line->item->name ?? '');
            $itemsTable->addCell(2200)->addText($line->specification ?? $line->item->specification ?? '');
            $itemsTable->addCell(700)->addText($line->unit->name ?? '');
            $itemsTable->addCell(900)->addText((string) (int) $line->quantity);
            $itemsTable->addCell(1100)->addText($this->fmtMoney($line->rate_bdt));
            $itemsTable->addCell(1100)->addText($this->fmtMoney($line->total_amount));
            $itemsTable->addCell(1400)->addText($line->ac_code ?? '');
        }
        for ($i = count($pr->items); $i < 6; $i++) {
            $itemsTable->addRow();
            foreach ($cols as $w) {
                $itemsTable->addCell($w[1])->addText('');
            }
        }
        $itemsTable->addRow();
        $itemsTable->addCell(500 + 1800 + 2200 + 700 + 900 + 1100, ['gridSpan' => 6])
            ->addText('Total Tk', $this->b(), ['alignment' => Jc::END]);
        $itemsTable->addCell(1100)->addText($this->fmtMoney($pr->total_estimated_amount), $this->b());
        $itemsTable->addCell(1400)->addText('');

        $section->addText('In-word : ৳ ' . $data['amountInWords'], array_merge($this->b(), ['size' => 11]));

        $deliveryTable = $section->addTable($this->borderedTableStyle());
        foreach ([
            ['Delivery Locations:', $pr->delivery_location ?? ''],
            ['Estimated Delivery Date:', $this->fmtDate($pr->estimated_delivery_date, 'd.m.Y')],
            ['Estimated Delivery Time:', $pr->estimated_delivery_time ?? ''],
        ] as [$label, $value]) {
            $deliveryTable->addRow();
            $deliveryTable->addCell(4500)->addText($label);
            $deliveryTable->addCell(4500)->addText((string) $value);
        }
        $deliveryTable->addRow();
        $deliveryTable->addCell(9000, ['gridSpan' => 2])->addText('Receiver Name: ' . ($pr->receiver_name ?? ''));
        $deliveryTable->addRow();
        $deliveryTable->addCell(9000, ['gridSpan' => 2])->addText('Receiver Contact: ' . ($pr->receiver_contact ?? ''));

        $budgetTable = $section->addTable($this->borderedTableStyle());
        $budgetTable->addRow();
        $budgetTable->addCell(9000, ['gridSpan' => 2])->addText('Budgetary Check: (by Accounts personnel)', $this->b());
        $budgetTable->addRow();
        $left = $budgetTable->addCell(5400);
        $left->addText('Total allocated Budget : ' . ($budgetCheck ? $this->fmtMoney($budgetCheck->allocated_budget) : '...............................................................................'));
        $left->addText('Remaining Budget B/F : ' . ($budgetCheck ? $this->fmtMoney($budgetCheck->remaining_budget_bf) : '..............................................................................'));
        $left->addText('Amount of PR : ' . $this->fmtMoney($pr->total_estimated_amount));
        $left->addText('Remaining Budget C/F: ' . ($budgetCheck ? $this->fmtMoney($budgetCheck->remaining_budget_cf) : '..............................................................................'));
        $left->addText('Name of Accountant : ' . ($budgetCheck?->checkedBy?->name ?? '..................') . '    Signature.................................');
        $budgetTable->addCell(3600)->addText('Remarks' . ($budgetCheck?->remarks ? ': ' . $budgetCheck->remarks : ''));

        $section->addTextBreak(1);
        $sig = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sig->addRow();
        $sig->addCell(3000)->addText('Requested by: ' . ($pr->requestor_name ?? '.................'));
        $sig->addCell(3000)->addText('Designation: ' . ($pr->requestor_designation ?? '.................'));
        $sig->addCell(3000)->addText('Signature: .................');
        $sig->addRow();
        $sig->addCell(3000)->addText('');
        $sig->addCell(3000)->addText('');
        $sig->addCell(3000)->addText('');
        $sig->addRow();
        $sig->addCell(3000)->addText('Endorsed by: ' . ($endorsedBy?->user?->name ?? '.................'));
        $sig->addCell(3000)->addText('Designation: ' . ($endorsedBy?->user?->designation ?? '.................'));
        $sig->addCell(3000)->addText('Signature: .................');
        $sig->addRow();
        $sig->addCell(3000)->addText('');
        $sig->addCell(3000)->addText('');
        $sig->addCell(3000)->addText('');
        $sig->addRow();
        $sig->addCell(3000)->addText('Finance Requested by: ' . ($financeRequestedBy?->user?->name ?? '.................'));
        $sig->addCell(3000)->addText('Designation: ' . ($financeRequestedBy?->user?->designation ?? '.................'));
        $sig->addCell(3000)->addText('Signature: .................');

        $section->addTextBreak(2);
        $final = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $final->addRow();
        $rec = $final->addCell(4500);
        $rec->addText('Recommend by: ' . ($recommendedBy?->user?->name ?? '.................'));
        $rec->addText('PC/DPC/APC/Focal Person');
        $final->addCell(4500)->addText('Approved by: ' . ($approvedBy?->user?->name ?? '.................'));

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
