<?php

namespace App\Services\DocxTemplates;

use App\Services\CommitteeDocumentText;
use App\Services\DocxTemplates\Support\BuildsEsdoDocx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Comparative Report of Quotations (Goods) — mirrors ESDO's paper format:
 * one row per requested item, and for every accepted bidder a Unit Price /
 * Total Price pair, a Total row, a decision paragraph and the Procurement
 * Committee signature block (Approved / Convener / Member / Member Secretary).
 *
 * Expects: ['statement' => ComparativeStatement, 'rfq' => Rfq].
 */
class ComparativeStatementDocumentBuilder
{
    use BuildsEsdoDocx;

    // Quotation statuses that mean the committee did not accept the bid.
    private const NOT_ACCEPTED = ['rejected', 'disqualified'];

    public function build(array $data): PhpWord
    {
        $statement = $data['statement'];
        $rfq = $data['rfq'];

        $rfq->loadMissing(['items.unit', 'quotations.vendor', 'quotations.items', 'tenderOpenings', 'procurementCase.purchaseRequisition']);
        $statement->loadMissing('lowestEvaluatedVendor');

        $items = $rfq->items;
        $received = $rfq->quotations;
        $bidders = $received
            ->reject(fn ($q) => in_array($q->status, self::NOT_ACCEPTED, true))
            ->groupBy('vendor_id')
            ->map(fn ($group) => $group->sortByDesc('id')->sortByDesc(fn ($q) => $q->items->isNotEmpty() ? 1 : 0)->first())
            ->values();

        // Per bidder: item_id => [unit, total], and the grand total.
        $figures = [];
        foreach ($bidders as $q) {
            $byItem = $q->items->keyBy('rfq_item_id');
            $rows = [];
            $sum = 0.0;
            foreach ($items as $item) {
                $qi = $byItem->get($item->id);
                $unit = $qi?->unit_price;
                $total = $qi?->amount ?? (($unit !== null) ? (float) $unit * (float) $item->quantity : null);
                $rows[$item->id] = ['unit' => $unit, 'total' => $total];
                $sum += (float) $total;
            }
            // A quotation recorded without item lines still has its lump-sum amount.
            $figures[$q->id] = ['rows' => $rows, 'sum' => $q->items->isEmpty() ? (float) $q->quoted_amount : $sum];
        }

        // Winner: the vendor the statement recommends, else the lowest total.
        $lowest = collect($figures)->filter(fn ($f) => $f['sum'] > 0)->sortBy('sum')->keys()->first();
        $lowestQuotation = $bidders->firstWhere('id', $lowest);
        $winner = $bidders->firstWhere('vendor_id', $statement->lowest_evaluated_vendor_id) ?? $lowestQuotation;

        $phpWord = $this->newPhpWord();
        $section = $this->addSection($phpWord, ['orientation' => 'landscape']);

        // Borderless letterhead (no table, so the PDF renderer cannot draw a box around it).
        $logo = public_path('img/esdo-logo.png');
        if (file_exists($logo)) {
            $section->addImage($logo, ['width' => 55, 'height' => 55, 'alignment' => Jc::CENTER]);
        }
        $section->addText('Eco-Social Development Organization (ESDO)', array_merge($this->b(), ['size' => 14]), $this->c());
        $section->addText('Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh', ['size' => 9, 'color' => '555555'], $this->c());
        $section->addTextBreak(1);

        $section->addText('Comparative Report of Quotations', array_merge($this->b(), ['size' => 14, 'underline' => 'single']), $this->c());
        $section->addTextBreak(1);

        $case = $rfq->procurementCase;
        $project = $case ? CommitteeDocumentText::projectName($case) : null;
        $section->addText('Project Name: ' . ($project ?: ($rfq->subject ?? '')), $this->b());
        $section->addText('Date: ' . $this->fmtDate($statement->created_at, 'd/m/Y'), [], ['alignment' => Jc::RIGHT]);

        // ---- Item-wise price table ----
        $nBid = max($bidders->count(), 1);
        $fixed = 600 + 3500 + 1000;                 // Sl, Description, Total Qty
        $usable = 14400;                            // landscape A4 minus margins (twips)
        $pair = (int) floor(($usable - $fixed) / $nBid);
        $unitW = (int) floor($pair * 0.4);
        $totW = $pair - $unitW;

        $table = $section->addTable($this->borderedTableStyle());

        $table->addRow();
        $table->addCell(600, $this->headerCellStyle() + ['vMerge' => 'restart'])->addText('Sl. No.', $this->b(), $this->c());
        $table->addCell(3500, $this->headerCellStyle() + ['vMerge' => 'restart'])->addText('Description of Goods', $this->b(), $this->c());
        $table->addCell(1000, $this->headerCellStyle() + ['vMerge' => 'restart'])->addText('Total Quantity', $this->b(), $this->c());
        if ($bidders->isEmpty()) {
            $table->addCell($usable - $fixed, $this->headerCellStyle())->addText('Name and Address of Quotation Submitting Firms', $this->b(), $this->c());
        }
        foreach ($bidders as $q) {
            $cell = $table->addCell($pair, $this->headerCellStyle() + ['gridSpan' => 2]);
            $cell->addText($q->vendor->name ?? '', $this->b(), $this->c());
            if (! empty($q->vendor->address)) {
                $cell->addText($q->vendor->address, ['size' => 9], $this->c());
            }
        }

        $table->addRow();
        $table->addCell(600, ['vMerge' => 'continue']);
        $table->addCell(3500, ['vMerge' => 'continue']);
        $table->addCell(1000, ['vMerge' => 'continue']);
        foreach ($bidders as $q) {
            $table->addCell($unitW, $this->headerCellStyle())->addText('Unit Price', $this->b(), $this->c());
            $table->addCell($totW, $this->headerCellStyle())->addText('Total Price', $this->b(), $this->c());
        }

        if ($items->isEmpty()) {
            $table->addRow();
            $table->addCell($usable, ['gridSpan' => 3 + 2 * $nBid])->addText('[No items recorded for this RFQ]');
        }

        foreach ($items as $i => $item) {
            $table->addRow();
            $table->addCell(600)->addText((string) ($item->serial_no ?: $i + 1), [], $this->c());
            $table->addCell(3500)->addText((string) $item->description);
            $qty = rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.');
            $table->addCell(1000)->addText(trim($qty . ' ' . ($item->unit->name ?? '')), [], $this->c());
            foreach ($bidders as $q) {
                $row = $figures[$q->id]['rows'][$item->id];
                $table->addCell($unitW)->addText($row['unit'] !== null ? $this->fmtMoney($row['unit']) : '-', [], ['alignment' => Jc::RIGHT]);
                $table->addCell($totW)->addText($row['total'] !== null ? $this->fmtMoney($row['total']) : '-', [], ['alignment' => Jc::RIGHT]);
            }
        }

        // Total row
        $table->addRow();
        $table->addCell(600 + 3500 + 1000, ['gridSpan' => 3, 'bgColor' => 'EEEEEE'])->addText('Total', $this->b(), ['alignment' => Jc::RIGHT]);
        foreach ($bidders as $q) {
            $isWinner = $winner && $q->id === $winner->id;
            $table->addCell($pair, ['gridSpan' => 2, 'bgColor' => 'EEEEEE'])
                ->addText($this->fmtMoney($figures[$q->id]['sum']), ['bold' => true, 'underline' => $isWinner ? 'single' : 'none'], ['alignment' => Jc::RIGHT]);
        }

        // ---- Decision notes (3 bullets, as on ESDO's Excel/paper statements) ----
        $section->addTextBreak(1);
        $fmt = fn ($d) => $d ? $d->format('d/m/Y') : '..../..../........';
        $opening = $rfq->tenderOpenings->sortByDesc('opening_date')->first();
        $subject = trim((string) ($rfq->subject ?? '')) ?: 'the requested items';

        // Eligibility as recorded on the Eligibility Report (if one exists for this RFQ).
        $eligibility = \App\Models\EligibilityReportItem::whereHas('report', fn ($q) => $q->where('rfq_id', $rfq->id))
            ->get()->groupBy('vendor_id')->map(fn ($g) => (bool) $g->sortByDesc('id')->first()->eligible);
        $participants = $received->pluck('vendor_id')->unique()->count();

        $notes = [];
        $notes[] = 'Request for quotation published on ' . $fmt($rfq->issue_date)
            . ' and opened on ' . $fmt($opening?->opening_date) . '.';

        $line = "{$participants} bidder(s) participated for {$subject} in the request for quotation process";
        if ($eligibility->isNotEmpty()) {
            $eligibleCount = $eligibility->filter()->count();
            $line .= $eligibleCount === $eligibility->count()
                ? ' and all are eligible.'
                : " and {$eligibleCount} of {$eligibility->count()} are eligible.";
        } else {
            $line .= " and {$bidders->count()} quotation(s) were accepted by the Committee.";
        }
        $notes[] = $line;

        if ($winner) {
            $name = $winner->vendor->name ?? '';
            $addr = $winner->vendor->address ?? '';
            $label = trim($name . ($addr ? ', ' . $addr : ''));
            $isLowest = $lowestQuotation && $winner->id === $lowestQuotation->id;
            $meetsSpecs = $eligibility->get($winner->vendor_id) === true;

            $line = "From {$participants} bidder(s), {$label} "
                . ($isLowest ? 'submitted the lowest price' : 'was ranked first on evaluation')
                . ($meetsSpecs ? ' and fulfilled all requirements/specifications as mentioned in the RFQ' : '')
                . '. So the Committee members recommend providing the work order to ' . $label
                . ' to supply ' . $subject . ($project && $project !== $subject ? ' under ' . $project : '') . '.';
        } else {
            $line = 'No bidder could be recommended yet; the decision of the Committee is pending.';
        }
        $notes[] = $line;

        foreach ($notes as $note) {
            $section->addText('* ' . $note, [], ['alignment' => Jc::BOTH, 'spaceAfter' => 80]);
        }

        // ---- Procurement Committee signatures ----
        $section->addTextBreak(1);
        $section->addText('Procurement Committee:', $this->b());
        $section->addText('Approved');
        $section->addTextBreak(2);

        // The HTML/PDF renderer gives every cell a default black border, so switch each side off on the cell itself.
        $noBorder = ['borderTopStyle' => 'none', 'borderLeftStyle' => 'none', 'borderBottomStyle' => 'none', 'borderRightStyle' => 'none'];
        $sig = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $sig->addRow();
        foreach ([
            ['Convener', 'Procurement Committee'],
            ['Member', 'Procurement Committee'],
            ['Member Secretary', 'Procurement Committee'],
        ] as [$role, $body]) {
            $cell = $sig->addCell(5000, $noBorder);
            $cell->addText('................................');
            $cell->addText($role, $this->b());
            $cell->addText($body);
            $cell->addText('ESDO');
        }

        return $phpWord;
    }
}