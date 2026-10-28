<?php

namespace App\Services;

use App\Models\ProcurementCase;
use App\Models\Quotation;
use App\Models\WorkOrder;
use App\Models\Meeting;
use App\Support\CommitteeScope;
use App\Support\MeetingCommittee;

/**
 * Everything printed on a Work Order, pulled from the records it belongs to — nothing is typed into
 * the document by hand, so it works the same for any Purchase Requisition:
 *
 *   Work Order -> Agreement -> Award (vendor) -> Plan -> PR -> Case -> RFQ -> the vendor's Quotation
 *
 *  - vendor name / address ........ the awarded vendor
 *  - items, quantities, prices .... the RFQ items with the prices THIS vendor quoted
 *  - delivery address & receiver .. the Purchase Requisition (and each RFQ item's own delivery address)
 *  - terms & conditions ........... the RFQ's terms (those that only concern submitting a bid are left out)
 *  - dates ........................ RFQ issue date, PR delivery date
 */
class WorkOrderData
{
    /** Terms that are only about submitting/opening a quotation, so they make no sense on a Work Order. */
    protected const BID_ONLY_TERMS = '/(must be Submitted with Quotation|Need to Attach with the Quotation|Quotation will be Opened|Delivery Location:|cancel .{0,40}any quotation)/i';

    /** @return array<string,mixed> */
    public static function for(WorkOrder $wo, array $signer = [], string $defaultAddress = 'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh'): array
    {
        $wo->loadMissing('contractAgreement.contractAward.vendor', 'contractAgreement.contractAward.procurementPlan.purchaseRequisition');
        $agreement = $wo->contractAgreement;
        $award = $agreement?->contractAward;
        $plan = $award?->procurementPlan;
        $pr = $plan?->purchaseRequisition;

        $case = $plan?->pr_id
            ? ProcurementCase::where('purchase_requisition_id', $plan->pr_id)->with('rfqs')->first()
            : null;

        // The RFQ this vendor actually quoted on (a case can have several).
        $rfq = null;
        $quotation = null;
        foreach (($case?->rfqs ?? collect())->sortByDesc('id') as $candidate) {
            $q = Quotation::with('items')
                ->where('rfq_id', $candidate->id)
                ->where('vendor_id', $award?->vendor_id)
                ->whereNotIn('status', ['rejected', 'disqualified'])
                ->latest('id')->first();
            if ($q) {
                $rfq = $candidate;
                $quotation = $q;
                break;
            }
        }
        $rfq ??= $case?->rfqs?->sortByDesc('id')->first();
        $rfq?->loadMissing('items.unit');

        // ---- Items with the vendor's own prices (falls back to the PR's rate when there is no quotation line) ----
        $prices = $quotation ? $quotation->items->keyBy('rfq_item_id') : collect();
        $rows = [];     // one per RFQ item (an item can be listed once per delivery location)
        foreach (($rfq?->items ?? collect())->sortBy('serial_no') as $it) {
            $qi = $prices->get($it->id);
            $qty = (float) $it->quantity;
            $unitPrice = $qi ? (float) $qi->unit_price : null;
            $amount = $qi ? (float) $qi->amount : null;
            $rows[] = [
                'key' => mb_strtolower(trim((string) $it->description)),
                'description' => trim((string) $it->description),
                'unit' => $it->unit->name ?? '',
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'amount' => $amount ?? ($unitPrice !== null ? round($unitPrice * $qty, 2) : null),
                'where' => trim((string) ($it->delivery_address ?: $it->scheme_name)),
            ];
        }

        // Group by item: one line per item with the total quantity (like the paper work order).
        $lines = [];
        foreach ($rows as $r) {
            $k = $r['key'];
            if (! isset($lines[$k])) {
                $lines[$k] = ['description' => $r['description'], 'unit' => $r['unit'], 'qty' => 0.0, 'unit_price' => $r['unit_price'], 'amount' => 0.0];
            }
            $lines[$k]['qty'] += $r['qty'];
            $lines[$k]['amount'] += (float) $r['amount'];
        }
        $lines = array_values($lines);
        $total = array_sum(array_column($lines, 'amount'));
        if ($total <= 0 && $quotation && $quotation->quoted_amount !== null) {
            $total = (float) $quotation->quoted_amount;
        }

        // Delivery distribution (only when the items go to more than one place).
        $places = array_values(array_unique(array_filter(array_column($rows, 'where'))));
        $distribution = null;
        if (count($places) > 1) {
            $itemNames = array_values(array_unique(array_column($rows, 'description')));
            $grid = [];
            foreach ($places as $p) {
                foreach ($itemNames as $n) {
                    $grid[$p][$n] = 0.0;
                }
            }
            foreach ($rows as $r) {
                if ($r['where'] !== '') {
                    $grid[$r['where']][$r['description']] += $r['qty'];
                }
            }
            $distribution = ['places' => $places, 'items' => $itemNames, 'grid' => $grid];
        }

        // Delivery address & receiver come from the Purchase Requisition.
        $deliveryLocation = trim((string) ($pr->delivery_location ?? ''));
        $deliveryPlaces = $places ?: ($deliveryLocation !== '' ? [$deliveryLocation] : []);
        $deadline = $pr->estimated_delivery_date ?? null;

        // ---- Terms: delivery clause + the RFQ's own terms (without the bid-only ones) ----
        $terms = [];
        $terms[] = 'The items must be supplied at your own cost'
            . ($deliveryPlaces ? ' to ' . implode(', ', $deliveryPlaces) : '')
            . ' as per the attached description' . ($deadline ? ' by ' . \Illuminate\Support\Carbon::parse($deadline)->format('d F, Y') : '') . '.';
        foreach (($rfq?->termsList() ?? []) as $t) {
            if (! preg_match(self::BID_ONLY_TERMS, $t)) {
                $terms[] = $t;
            }
        }
        
        $defaultTerms = $terms;
        if (is_array($wo->terms) && $wo->terms !== []) {
            $terms = $wo->terms;
        }

        $committee = $plan ? CommitteeScope::currentCommitteeForPlanId($plan->id) : null;
        $firstMeetingOffice = $case
            ? Meeting::withoutGlobalScopes()
                ->where('procurement_case_id', $case->id)
                ->where('meeting_type', 'first')
                ->value('office')
            : null;
        $office = $firstMeetingOffice ?: $rfq?->office;
        $letterhead = MeetingCommittee::letterhead($committee, $defaultAddress, $office);

        return [
            'wo' => $wo,
            'memo' => $wo->wo_number,
            'date' => $wo->wo_date,
            'vendorName' => $award?->vendor?->name,
            'vendorAddress' => $award?->vendor?->address,
            'subject' => $rfq?->subject ?: ($case?->title ?: ($pr->project_name ?? 'goods/services')),
            'rfqDate' => $rfq?->issue_date,
            'rfqNumber' => $rfq?->rfq_number,
            'deadline' => $deadline,
            'lines' => $lines,
            'total' => round($total, 2),
            'distribution' => $distribution,
            'terms' => $terms,
            'defaultTerms' => $defaultTerms,
            'deliveryPlaces' => $deliveryPlaces,
            'receiverName' => $pr->receiver_name ?? null,
            'receiverContact' => $pr->receiver_contact ?? null,
            'deliveryLocation' => $deliveryLocation,
            'committeeName' => $letterhead['name'],
            'committeeAddress' => $letterhead['address'],
            'signerName' => $signer[0] ?? '',
            'signerTitle' => $signer[1] ?? '',
            'signerLine' => trim((($signer[1] ?? '') !== '' ? $signer[1] . ', ' : '') . $letterhead['name']),
        ];
    }
}
