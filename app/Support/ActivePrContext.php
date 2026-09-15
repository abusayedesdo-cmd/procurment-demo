<?php

namespace App\Support;

use App\Models\ProcurementCase;
use App\Models\ProcurementPlan;
use App\Models\PurchaseRequisition;
use App\Models\Rfq;

/**
 * Resolves the "active PR" (see ProcessStepPageController's PR Receive
 * design — an officer picks a PR and every step stays scoped to it for the
 * rest of the session) and its Plan/Case/RFQ chain from the session.
 *
 * ProcessStepPageController resolves this chain inline for its own pages.
 * This class exists so anything else — right now, ModulePageController,
 * so a plain /modules/{slug} visit (bookmark, refresh, sidebar link, not
 * just a Process Steps link carrying `?field_x=y`) is scoped the same way
 * — can get the same chain without duplicating the query logic.
 */
class ActivePrContext
{
    public static function resolve(): array
    {
        $pr = null;
        $prId = session('active_pr_id');
        if ($prId) {
            $pr = PurchaseRequisition::find($prId);
        }

        $planId = null;
        $caseId = null;
        $rfqId = null;

        if ($pr) {
            $planId = ProcurementPlan::where('pr_id', $pr->id)->latest('id')->value('id');
            $caseId = ProcurementCase::where('purchase_requisition_id', $pr->id)->latest('id')->value('id');
            $rfqId = $caseId ? Rfq::where('procurement_case_id', $caseId)->latest('id')->value('id') : null;
        }

        return compact('pr', 'planId', 'caseId', 'rfqId');
    }


    public static function valueForField(string $field, array $context): ?int
    {
        return match ($field) {
            'pr_id' => $context['pr']?->id,
            'procurement_plan_id' => $context['planId'],
            'procurement_case_id' => $context['caseId'],
            'rfq_id' => $context['rfqId'],
            default => null,
        };
    }
}
