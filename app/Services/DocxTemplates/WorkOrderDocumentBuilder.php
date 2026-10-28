<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Builds the Work Order (kaarjaadesh-patro). Mirrors resources/views/documents/work-order.blade.php.
 *
 * Expects the array made by App\Services\WorkOrderData::for() plus 'amountWords'.
 */
class WorkOrderDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $d): PhpWord
    {
        $fd = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d F, Y') : '';
        $money = fn ($v) => $v === null ? '' : number_format((float) $v, 2);
        $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
        $lines = function ($cell, string $text, array $font = []) {
            foreach (preg_split('/\R/', $text) as $l) {
                if (trim($l) !== '') {
                    $cell->addText(trim($l), $font);
                }
            }
        };

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead($section, 'Eco-Social Development Organization (ESDO)', $d['committeeAddress']);

        $memo = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $memo->addRow();
        $memo->addCell(5800)->addText('Memo: ' . ($d['memo'] ?? ''), $this->b());
        $memo->addCell(3600)->addText('Date: ' . $fd($d['date']), $this->b(), ['alignment' => Jc::RIGHT]);

        $section->addTextBreak(1);
        $section->addText('WORK ORDER', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);

        $section->addText('To,');
        $section->addText((string) $d['vendorName'], $this->b());
        if (! empty($d['vendorAddress'])) {
            $section->addText((string) $d['vendorAddress']);
        }
        $section->addTextBreak(1);
        $section->addText('Dear Sir/Madam,');

        $quoted = $d['rfqDate'] ? 'on ' . $fd($d['rfqDate']) : 'earlier';
        $where = $d['deliveryPlaces'] ? ' for ' . implode(', ', $d['deliveryPlaces']) : '';
        $by = $d['deadline'] ? ' by ' . $fd($d['deadline']) : ' within the time agreed';
        $section->addText(
            'Eco-Social Development Organization (ESDO) invited quotations ' . $quoted . ' from interested genuine businesses/suppliers for the supply of '
            . $d['subject'] . $where . '. As per the quotations received, your organization is the lowest evaluated bidder, so this Work Order is being issued to you. '
            . 'You are requested to supply the items below' . $by . ', according to the description and terms & conditions given here.',
            [],
            ['alignment' => Jc::BOTH]
        );

        // ---- Items ----
        $cols = [['Sl. No.', 700], ['Description', 4100], ['Total Qty.', 1200], ['Unit Price', 1600], ['Total Price', 1800]];
        $t = $section->addTable($this->borderedTableStyle());
        $t->addRow();
        foreach ($cols as [$h, $w]) {
            $t->addCell($w, $this->headerCellStyle())->addText($h, $this->b(), $this->c());
        }
        foreach ($d['lines'] as $i => $l) {
            $t->addRow();
            $t->addCell(700)->addText((string) ($i + 1), [], $this->c());
            $lines($t->addCell(4100), $l['description']);
            $t->addCell(1200)->addText($qty($l['qty']) . ($l['unit'] ? ' ' . $l['unit'] : ''), [], $this->c());
            $t->addCell(1600)->addText($money($l['unit_price']), [], ['alignment' => Jc::RIGHT]);
            $t->addCell(1800)->addText($money($l['amount']), [], ['alignment' => Jc::RIGHT]);
        }
        if (! $d['lines']) {
            $t->addRow();
            $t->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => 5])->addText('No item recorded.');
        }
        $t->addRow();
        $t->addCell(700 + 4100 + 1200 + 1600, ['gridSpan' => 4])->addText('Total price including VAT & Tax (BDT)', $this->b());
        $t->addCell(1800)->addText($money($d['total']), $this->b(), ['alignment' => Jc::RIGHT]);

        $section->addText('In words: ' . $d['amountWords'], $this->b(), ['spaceBefore' => 120]);

        // ---- Terms ----
        $section->addText('Terms & Conditions applicable:', array_merge($this->b(), ['underline' => 'single']), ['spaceBefore' => 160]);
        $this->addNumberedList($section, $d['terms']);

        // ---- Delivery distribution (only when there is more than one place) ----
        if ($d['distribution']) {
            $dist = $d['distribution'];
            $section->addText('The items must be delivered to the locations below according to these quantities:', [], ['spaceBefore' => 160]);
            $first = 700 + 2100;
            $each = (int) floor((9400 - $first) / max(1, count($dist['items']) + 1));
            $dt = $section->addTable($this->borderedTableStyle());
            $dt->addRow();
            $dt->addCell(700, $this->headerCellStyle())->addText('Sl.', $this->b(), $this->c());
            $dt->addCell(2100, $this->headerCellStyle())->addText('Location', $this->b(), $this->c());
            foreach ($dist['items'] as $n) {
                $dt->addCell($each, $this->headerCellStyle())->addText(strtok($n, "\n"), $this->b(), $this->c());
            }
            $dt->addCell($each, $this->headerCellStyle())->addText('Total', $this->b(), $this->c());
            foreach ($dist['places'] as $i => $p) {
                $dt->addRow();
                $dt->addCell(700)->addText((string) ($i + 1), [], $this->c());
                $dt->addCell(2100)->addText($p);
                $sum = 0;
                foreach ($dist['items'] as $n) {
                    $v = $dist['grid'][$p][$n] ?? 0;
                    $sum += $v;
                    $dt->addCell($each)->addText($v ? $qty($v) : '-', [], $this->c());
                }
                $dt->addCell($each)->addText($qty($sum), $this->b(), $this->c());
            }
        }

        // ---- Delivery address (from the Purchase Requisition) ----
        $section->addText('Address for delivery of the items:', array_merge($this->b(), ['underline' => 'single']), ['spaceBefore' => 160]);
        $a = $section->addTable($this->borderedTableStyle());
        $a->addRow();
        $cell = $a->addCell(9400);
        if (! empty($d['receiverName'])) {
            $cell->addText($d['receiverName'], $this->b());
        }
        if (! empty($d['deliveryLocation'])) {
            $cell->addText($d['deliveryLocation']);
        } elseif ($d['deliveryPlaces']) {
            $cell->addText(implode('; ', $d['deliveryPlaces']));
        }
        if (! empty($d['receiverContact'])) {
            $cell->addText('Mobile: ' . $d['receiverContact']);
        }

        // ---- Signatures ----
        $section->addTextBreak(2);
        $sig = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sig->addRow();
        $left = $sig->addCell(5000);
        $left->addText('(' . ($d['signerName'] ?: '[Name]') . ')', $this->b());
        $left->addText($d['signerLine']);
        $left->addText('ESDO, ' . $d['committeeAddress']);
        $sig->addCell(4400)->addText('Vendor/Recipient: signature of consent & seal', [], ['alignment' => Jc::RIGHT]);

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
