<?php

namespace App\Services;

use App\Models\ContractAward;
use App\Models\ProcurementCase;
use App\Models\Quotation;
use App\Support\MeetingCommittee;

/**
 * Gathers everything for the "Procurement Report" of ONE case (= one Purchase Requisition that was
 * procured). Read-only: nothing is saved; the report is rebuilt from the live records every time,
 * so it is always current. Used by both the Word builder and the PDF/preview Blade view.
 */
class ProcurementReportData
{
    /** @return array<string,mixed> */
    public static function for(ProcurementCase $case, ?string $preparedBy = null): array
    {
        $case->loadMissing([
            'purchaseRequisition.category', 'purchaseRequisition.procurementPlan',
            'purchaseRequisition.items.item', 'purchaseRequisition.items.unit', 'purchaseRequisition.raisedBy',
            'meetings',
        ]);
        $pr = $case->purchaseRequisition;

        $rfqs = $case->rfqs()->with([
            'tenderSchedules', 'quotations.vendor', 'tenderOpenings',
            'eligibilityReports', 'technicalEvaluationReports', 'financialEvaluationReports',
            'comparativeStatements.lowestEvaluatedVendor', 'comparativeStatements.items.vendor',
        ])->orderBy('id')->get();
        $rfq = $rfqs->last();
        $quotations = $rfq ? $rfq->quotations->sortBy('quoted_amount')->values() : collect();
        $statement = $rfq ? $rfq->comparativeStatements->sortByDesc('id')->first() : null;
        $opening = $rfq ? $rfq->tenderOpenings->sortByDesc('id')->first() : null;

        $plan = $pr?->procurementPlan;
        $awards = $plan
            ? ContractAward::where('procurement_plan_id', $plan->id)
                ->with(['vendor', 'payOrders', 'contractAgreements.workOrders.deliveryReceipts'])->orderBy('id')->get()
            : collect();
        $award = $awards->last();
        $agreement = $award?->contractAgreements->sortByDesc('id')->first();
        $workOrder = $agreement?->workOrders->sortByDesc('id')->first();
        $deliveries = $workOrder ? $workOrder->deliveryReceipts->sortBy('id')->values() : collect();
        $payOrder = $award?->payOrders->sortByDesc('id')->first();

        // Ranking: the comparative statement rows, best first.
        $ranking = $statement
            ? $statement->items->sortBy(fn ($i) => $i->rank ?? PHP_INT_MAX)->values()
            : collect();

        // What was finally awarded: pay order, else the winner's quoted amount, else their statement row.
        $awarded = null;
        if ($payOrder && (float) $payOrder->awarded_amount > 0) {
            $awarded = (float) $payOrder->awarded_amount;
        } elseif ($award && $rfq) {
            $q = Quotation::where('rfq_id', $rfq->id)->where('vendor_id', $award->vendor_id)->first();
            $awarded = $q ? (float) $q->quoted_amount : null;
            $awarded ??= optional($ranking->firstWhere('vendor_id', $award->vendor_id))->amount;
        }
        $estimated = (float) ($pr->total_estimated_amount ?? $case->amount ?? 0);

        // Committee that ran the procurement (Main, or the Sub-Committee holding the case).
        $office = $rfq?->office ?: $case->meetings->sortByDesc('id')->first()?->office;
        $letterhead = MeetingCommittee::letterhead(MeetingCommittee::forCase($case), 'Collegepara (Gobindanagar), Thakurgaon, Rangpur, Bangladesh', $office);

        // Key dates, in the order the work happened (rows without a date are left out).
        $timeline = [];
        $push = function (string $label, $date, ?string $ref = null) use (&$timeline) {
            if ($date) {
                $timeline[] = ['label' => $label, 'date' => $date, 'ref' => $ref];
            }
        };
        $push('Purchase Requisition raised', $pr?->requisition_date, $pr?->pr_number);
        foreach ($case->meetings->sortBy('id') as $m) {
            $push(($m->meeting_type === 'second' ? '2nd' : '1st') . ' committee meeting', $m->meeting_date, $m->rezulation_no ? 'Resolution ' . $m->rezulation_no : null);
        }
        $push('RFQ issued', $rfq?->issue_date, $rfq?->rfq_number);
        $push('Quotation deadline', $rfq?->closing_date);
        $push('Tender opened', $opening?->opening_date);
        $push('Notification of Award (NOA)', $award?->noa_date, $award?->noa_number);
        $push('Contract agreement signed', $agreement?->agreement_date, $agreement?->agreement_number);
        $push('Work order issued', $workOrder?->wo_date, $workOrder?->wo_number);
        foreach ($deliveries as $d) {
            $push('Delivery received', $d->delivery_date, $d->received_by ? 'Received by ' . $d->received_by : null);
        }

        // Which records exist (the same steps the case page tracks).
        $count = fn (string $rel) => $rfqs->sum(fn ($r) => $r->{$rel}->count());
        $checklist = [
            ['Request for Quotation (RFQ)', $rfqs->count()],
            ['Tender Schedule', $count('tenderSchedules')],
            ['Quotations received', $count('quotations')],
            ['Tender Opening Report', $count('tenderOpenings')],
            ['Eligibility Report', $count('eligibilityReports')],
            ['Technical Evaluation Report', $count('technicalEvaluationReports')],
            ['Financial Evaluation Report', $count('financialEvaluationReports')],
            ['Comparative Statement', $count('comparativeStatements')],
            ['Notification of Award (NOA)', $awards->count()],
            ['Pay Order', $awards->sum(fn ($a) => $a->payOrders->count())],
            ['Contract Agreement', $awards->sum(fn ($a) => $a->contractAgreements->count())],
            ['Work Order', $awards->flatMap->contractAgreements->flatMap->workOrders->count()],
            ['Delivery Received (Challan)', $deliveries->count()],
        ];

        return [
            'case' => $case,
            'pr' => $pr,
            'items' => $pr?->items ?? collect(),
            'meetings' => $case->meetings->sortBy('id')->values(),
            'rfq' => $rfq,
            'rfq_count' => $rfqs->count(),
            'opening' => $opening,
            'quotations' => $quotations,
            'statement' => $statement,
            'ranking' => $ranking,
            'has_sample' => $ranking->contains(fn ($i) => $i->sample_marks !== null),
            'recommended' => $statement?->lowestEvaluatedVendor?->name,
            'award' => $award,
            'payOrder' => $payOrder,
            'agreement' => $agreement,
            'workOrder' => $workOrder,
            'deliveries' => $deliveries,
            'estimated' => $estimated,
            'awarded' => $awarded,
            'saving' => ($awarded !== null && $estimated > 0) ? round($estimated - $awarded, 2) : null,
            'timeline' => $timeline,
            'checklist' => $checklist,
            'committeeName' => $letterhead['name'],
            'committeeAddress' => $letterhead['address'],
            'preparedBy' => $preparedBy,
            'generatedAt' => now(),
        ];
    }
}
