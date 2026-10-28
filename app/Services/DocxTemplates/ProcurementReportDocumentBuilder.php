<?php

namespace App\Services\DocxTemplates;

use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Word version of the Procurement Report. Mirrors resources/views/documents/procurement-report.blade.php.
 *
 * Expects the array made by App\Services\ProcurementReportData::for().
 */
class ProcurementReportDocumentBuilder
{
    use BuildsEsdoDocx;

    public function build(array $d): PhpWord
    {
        $case = $d['case'];
        $pr = $d['pr'];
        // Dates may be Carbon objects or plain strings depending on the model casts.
        $fd = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d F, Y') : '-';
        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord);

        $this->addLetterhead($section, 'Eco-Social Development Organization (ESDO)', $d['committeeName'] . ', ' . $d['committeeAddress']);

        $section->addText('PROCUREMENT REPORT', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addText('Report date: ' . $fd($d['generatedAt']), ['size' => 9, 'color' => '555555'], $this->c());

        $heading = fn (string $t) => $section->addText($t, array_merge($this->b(), ['size' => 12, 'underline' => 'single']), ['spaceBefore' => 200]);
        $money = fn ($v) => $v === null ? '-' : number_format((float) $v, 2);
        $mark = fn ($v) => $v === null ? '-' : number_format((float) $v, 2);
        $ord = function ($n): string {
            if (! $n) {
                return '-';
            }
            $s = ['th', 'st', 'nd', 'rd'];
            $v = $n % 100;

            return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
        };
        $table = function (array $cols, array $rows, array $alignRight = []) use ($section) {
            $t = $section->addTable($this->borderedTableStyle());
            $t->addRow();
            foreach ($cols as [$label, $width]) {
                $t->addCell($width, $this->headerCellStyle())->addText($label, $this->b(), $this->c());
            }
            if (! $rows) {
                $t->addRow();
                $t->addCell(array_sum(array_column($cols, 1)), ['gridSpan' => count($cols)])->addText('No record yet.');

                return;
            }
            foreach ($rows as $r) {
                $t->addRow();
                foreach ($cols as $i => [$label, $width]) {
                    $t->addCell($width)->addText((string) ($r[$i] ?? ''), [], in_array($i, $alignRight, true) ? ['alignment' => Jc::RIGHT] : []);
                }
            }
        };

        // 1. Summary
        $heading('1. Procurement Summary');
        $saving = $d['saving'];
        $summary = [
            ['Case reference', (string) $case->ref],
            ['Subject', (string) $case->title],
            ['Purchase Requisition No.', ($pr->pr_number ?? '-') . ' (dated ' . $fd($pr->requisition_date ?? null) . ')'],
            ['Project', (string) ($pr->project_name ?? '-')],
            ['Category / Method', $case->category . ' — ' . $case->natureLabel()],
            ['Requested by', trim(($pr->requestor_name ?? '-') . (! empty($pr->requestor_designation) ? ', ' . $pr->requestor_designation : ''))],
            ['Delivery location', (string) ($pr->delivery_location ?? '-')],
            ['Procurement committee', $d['committeeName']],
            ['Estimated cost (BDT)', $money($d['estimated'])],
            ['Awarded amount (BDT)', $money($d['awarded'])],
            ['Difference (estimate − award)', $saving === null ? '-' : $money($saving) . ($saving >= 0 ? ' (below estimate)' : ' (above estimate)')],
            ['Selected vendor', $d['award']->vendor->name ?? ($d['recommended'] ? $d['recommended'] . ' (recommended)' : 'Not awarded yet')],
        ];
        $t = $section->addTable($this->borderedTableStyle());
        foreach ($summary as [$k, $v]) {
            $t->addRow();
            $t->addCell(3200)->addText($k, $this->b());
            $t->addCell(6200)->addText($v);
        }

        // 2. Items
        if ($d['items']->isNotEmpty()) {
            $heading('2. Items Procured');
            $rows = [];
            foreach ($d['items'] as $i => $it) {
                $rows[] = [$i + 1, $it->item->name ?? '', (string) $it->specification, $it->unit->name ?? '',
                    rtrim(rtrim(number_format((float) $it->quantity, 2), '0'), '.'), $money($it->rate_bdt), $money($it->total_amount)];
            }
            $table([['SL', 500], ['Item', 2200], ['Specification', 2400], ['Unit', 800], ['Qty', 700], ['Rate (BDT)', 1300], ['Total (BDT)', 1400]], $rows, [4, 5, 6]);
        }

        // 3. Key dates
        $heading('3. Key Dates');
        $rows = array_map(fn ($t) => [$t['label'], $fd($t['date']), (string) $t['ref']], $d['timeline']);
        $table([['Step', 3800], ['Date', 2000], ['Reference', 3600]], $rows);

        // 4. Meetings
        if ($d['meetings']->isNotEmpty()) {
            $heading('4. Committee Meetings');
            $rows = [];
            foreach ($d['meetings'] as $i => $mt) {
                $rows[] = [$i + 1, $mt->meeting_type === 'second' ? '2nd meeting' : '1st meeting', $fd($mt->meeting_date),
                    (string) ($mt->rezulation_no ?? '-'), \Illuminate\Support\Str::limit(trim((string) ($mt->decisions ?: $mt->agenda)), 220)];
            }
            $table([['SL', 500], ['Meeting', 1500], ['Date', 1800], ['Resolution No.', 1500], ['Agenda / Decision', 4100]], $rows);
        }

        // 5. Quotations
        $rfq = $d['rfq'];
        $heading('5. Quotations Received' . ($rfq ? ' — RFQ ' . $rfq->rfq_number . ($d['rfq_count'] > 1 ? ' (latest of ' . $d['rfq_count'] . ' RFQs)' : '') . ', closing ' . $fd($rfq->closing_date) : ''));
        $rows = [];
        foreach ($d['quotations'] as $i => $q) {
            $rows[] = [$i + 1, $q->vendor->name ?? '', $money($q->quoted_amount), ucfirst((string) $q->status)];
        }
        $table([['SL', 500], ['Vendor', 4600], ['Quoted amount (BDT)', 2400], ['Status', 1900]], $rows, [2]);

        // 6. Evaluation
        if ($d['ranking']->isNotEmpty()) {
            $heading('6. Evaluation Result');
            $hasSample = $d['has_sample'];
            $cols = [['SL', 500], ['Vendor', $hasSample ? 2800 : 3300], ['Bid (BDT)', 1500], ['Technical', 1000], ['Financial', 1000]];
            if ($hasSample) {
                $cols[] = ['Sample', 900];
            }
            $cols[] = ['Total', 900];
            $cols[] = ['Rank', 700];
            $rows = [];
            foreach ($d['ranking'] as $i => $r) {
                $row = [$i + 1, $r->vendor->name ?? '', $money($r->amount), $mark($r->technical_marks), $mark($r->financial_marks)];
                if ($hasSample) {
                    $row[] = $mark($r->sample_marks);
                }
                $row[] = $mark($r->total_marks);
                $row[] = $ord($r->rank);
                $rows[] = $row;
            }
            $table($cols, $rows, [2]);
            if ($d['recommended']) {
                $section->addText('Recommended vendor: ' . $d['recommended'], $this->b());
            }
        }

        // 7. Award, contract, delivery
        $heading('7. Award, Contract & Delivery');
        $award = $d['award'];
        $po = $d['payOrder'];
        $deliv = $d['deliveries']->map(fn ($x) => $fd($x->delivery_date) . ($x->received_by ? ', received by ' . $x->received_by : ''))->implode('; ');
        $rows = [
            ['Notification of Award', $award ? ($award->noa_number . ' — ' . $fd($award->noa_date) . ' — ' . ($award->vendor->name ?? '')) : 'Not issued yet'],
            ['Pay Order', $po ? ('Pay order ' . $money($po->pay_order_amount) . ' BDT; received ' . $money($po->received_amount) . ' BDT on ' . $fd($po->received_date)) : '-'],
            ['Contract Agreement', $d['agreement'] ? ($d['agreement']->agreement_number . ' — ' . $fd($d['agreement']->agreement_date)) : '-'],
            ['Work Order', $d['workOrder'] ? ($d['workOrder']->wo_number . ' — ' . $fd($d['workOrder']->wo_date)) : '-'],
            ['Delivery', $deliv !== '' ? $deliv : '-'],
        ];
        $t = $section->addTable($this->borderedTableStyle());
        foreach ($rows as [$k, $v]) {
            $t->addRow();
            $t->addCell(3200)->addText($k, $this->b());
            $t->addCell(6200)->addText($v);
        }

        // 8. Checklist
        $heading('8. Document Checklist');
        $rows = [];
        foreach ($d['checklist'] as $i => [$label, $n]) {
            $rows[] = [$i + 1, $label, $n, $n > 0 ? 'Available' : 'Not yet'];
        }
        $table([['SL', 700], ['Document', 5200], ['Records', 1500], ['Status', 2000]], $rows);

        // Signatures
        $section->addTextBreak(2);
        $sig = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sig->addRow();
        $sig->addCell(3100)->addText('Prepared by');
        $sig->addCell(3100)->addText('Member Secretary', [], $this->c());
        $sig->addCell(3100)->addText('Convener', [], ['alignment' => Jc::RIGHT]);
        $sig->addRow(900);
        $sig->addCell(3100)->addText((string) ($d['preparedBy'] ?? ''));
        $sig->addCell(3100)->addText($d['committeeName'], [], $this->c());
        $sig->addCell(3100)->addText($d['committeeName'], [], ['alignment' => Jc::RIGHT]);

        $this->addFooterDisclaimer($section);

        return $phpWord;
    }
}
