<?php

namespace App\Http\Controllers\Api;

use App\Models\ContractAgreement;
use App\Models\ContractAward;
use App\Models\ProcurementCase;
use App\Models\ProcurementCommitteeMember;
use App\Models\Quotation;
use App\Services\DocxTemplates\ContractAgreementDocumentBuilder;
use App\Services\DocxTemplates\NoaDocumentBuilder;
use App\Services\CommitteeDocumentText;
use App\Support\CommitteeScope;

/**
 * Word / PDF documents for the post-award steps:
 *   - Notification of Award (NoA)            <- a ContractAward
 *   - Draft Contract Agreement (sent with it) <- the same ContractAward
 *   - Contract Agreement                      <- a ContractAgreement
 *
 * Extends DocumentDownloadController only to reuse its docx / pdf response
 * helpers (and the ESDO letterhead-pad hook for the NoA Word file).
 */
class ContractDocumentController extends DocumentDownloadController
{
    private const OFFICE_ADDRESS = 'ESDO House, Plot # 748, Road # 8, Baitul Aman Housing Society, Adabar, Mohammadpur, Dhaka-1207.';

    // ------------------------------------------------------------------ NoA

    public function noa(ContractAward $contractAward)
    {
        $this->assertCanAct($contractAward);
        $d = $this->awardData($contractAward);

        // The pad (Word) already carries the letterhead; the PDF has none, so print one.
        return $this->docxResponse((new NoaDocumentBuilder())->build($d + ['letterhead' => false]), $this->noaName($contractAward), true);
    }

    public function noaPdf(ContractAward $contractAward)
    {
        $this->assertCanAct($contractAward);

        return $this->pdfResponse((new NoaDocumentBuilder())->build($this->awardData($contractAward) + ['letterhead' => true]), $this->noaName($contractAward));
    }

    public function noaPreview(ContractAward $contractAward)
    {
        $this->assertCanAct($contractAward);

        return $this->pdfResponse((new NoaDocumentBuilder())->build($this->awardData($contractAward) + ['letterhead' => true]), $this->noaName($contractAward), inline: true);
    }

    // ------------------------------------------- Draft agreement (from the NoA)

    public function agreementDraft(ContractAward $contractAward)
    {
        $this->assertCanAct($contractAward);

        return $this->docxResponse(
            (new ContractAgreementDocumentBuilder())->build($this->agreementData($contractAward, null)),
            'Draft-Contract-Agreement-' . $this->safe((string) $contractAward->noa_number)
        );
    }

    public function agreementDraftPreview(ContractAward $contractAward)
    {
        $this->assertCanAct($contractAward);

        return $this->pdfResponse(
            (new ContractAgreementDocumentBuilder())->build($this->agreementData($contractAward, null)),
            'Draft-Contract-Agreement-' . $this->safe((string) $contractAward->noa_number),
            inline: true
        );
    }

    // ------------------------------------------------------ Contract Agreement

    public function agreement(ContractAgreement $contractAgreement)
    {
        $award = $this->awardOf($contractAgreement);

        return $this->docxResponse(
            (new ContractAgreementDocumentBuilder())->build($this->agreementData($award, $contractAgreement)),
            'Contract-Agreement-' . $this->safe((string) $contractAgreement->agreement_number)
        );
    }

    public function agreementPdf(ContractAgreement $contractAgreement)
    {
        $award = $this->awardOf($contractAgreement);

        return $this->pdfResponse(
            (new ContractAgreementDocumentBuilder())->build($this->agreementData($award, $contractAgreement)),
            'Contract-Agreement-' . $this->safe((string) $contractAgreement->agreement_number)
        );
    }

    public function agreementPreview(ContractAgreement $contractAgreement)
    {
        $award = $this->awardOf($contractAgreement);

        return $this->pdfResponse(
            (new ContractAgreementDocumentBuilder())->build($this->agreementData($award, $contractAgreement)),
            'Contract-Agreement-' . $this->safe((string) $contractAgreement->agreement_number),
            inline: true
        );
    }

    // ---------------------------------------------------------------- data

    private function awardOf(ContractAgreement $agreement): ContractAward
    {
        $agreement->loadMissing('contractAward');
        $award = $agreement->contractAward;
        abort_unless($award, 404, 'This agreement has no Contract Award.');
        $this->assertCanAct($award);

        return $award;
    }

    private function assertCanAct(ContractAward $award): void
    {
        abort_unless(
            CommitteeScope::userCanActOnPlan(request()->user(), $award->procurement_plan_id),
            403,
            'This case is currently with a different committee.'
        );
    }

    private function noaName(ContractAward $award): string
    {
        return 'NOA-' . $this->safe((string) $award->noa_number);
    }

    /** Everything the NoA needs, pulled through plan -> PR -> case -> RFQ -> the vendor's quotation. */
    private function awardData(ContractAward $award): array
    {
        $award->loadMissing('vendor', 'procurementPlan.purchaseRequisition');
        $plan = $award->procurementPlan;

        $case = $plan?->pr_id
            ? ProcurementCase::where('purchase_requisition_id', $plan->pr_id)->with('rfqs')->first()
            : null;

        // the RFQ this vendor actually quoted on (a case can have several)
        $rfq = null;
        $quotation = null;
        foreach (($case?->rfqs ?? collect())->sortByDesc('id') as $candidate) {
            $q = Quotation::with('items')
                ->where('rfq_id', $candidate->id)
                ->where('vendor_id', $award->vendor_id)
                ->whereNotIn('status', ['rejected', 'disqualified'])
                ->latest('id')->first();
            if ($q) {
                $rfq = $candidate;
                $quotation = $q;
                break;
            }
        }
        $rfq ??= $case?->rfqs?->sortByDesc('id')->first();

        $amount = null;
        if ($quotation) {
            $amount = $quotation->items->isNotEmpty()
                ? (float) $quotation->items->sum(fn ($i) => (float) $i->amount)
                : ($quotation->quoted_amount !== null ? (float) $quotation->quoted_amount : null);
        }
        $amount ??= ($v = $award->payOrders()->latest('id')->value('awarded_amount')) !== null ? (float) $v : null;

        // Performance security: explicit amount > percentage of the award > 5 % default (only when required).
        $securityAmount = null;
        $securityPct = $award->security_deposit_percentage !== null ? (float) $award->security_deposit_percentage : null;
        if ($award->security_deposit_required) {
            if ($award->security_deposit_amount) {
                $securityAmount = (float) $award->security_deposit_amount;
            } elseif ($amount !== null) {
                $securityPct ??= 5.0;
                $securityAmount = floor($amount * $securityPct / 100); // whole taka, as on ESDO's letters
            }
        }

        $committee = CommitteeScope::currentCommitteeForPlanId($award->procurement_plan_id);
        $isMain = ! $committee || $committee->type === 'main';
        $type = $isMain ? ProcurementCommitteeMember::CENTRAL_PROCUREMENT : ProcurementCommitteeMember::PURCHASE_COMMITTEE;
        $member = fn (string $role) => ProcurementCommitteeMember::where('active', true)
            ->where('committee_type', $type)->where('role', $role)->orderBy('sort_order')->first();
        $conv = $member('convener');
        $sec = $member('member_secretary');

        return [
            'date' => $award->noa_date,
            'ref' => $award->noa_number,
            'vendorName' => $award->vendor?->name,
            'vendorAddress' => $award->vendor?->address,
            'tenderNo' => $rfq?->rfq_number,
            'tenderDate' => $rfq?->issue_date,
            'workTitle' => $rfq?->subject ?: ($plan?->purchaseRequisition?->project_name),
            'project' => $case ? CommitteeDocumentText::projectName($case) : null,
            'location' => $case ? CommitteeDocumentText::projectLocation($case) : null,
            'category' => $award->category ?: 'Work',
            'amount' => $amount,
            'securityAmount' => $securityAmount,
            'securityPct' => $securityPct,
            'warrantyEnds' => $award->warranty_period_ends_at,
            'committeeName' => $isMain ? 'Central Procurement Committee' : $committee->name,
            'convener' => $conv ? ['name' => $conv->name, 'designation' => $conv->designation] : null,
            'secretary' => $sec ? ['name' => $sec->name, 'designation' => $sec->designation] : null,
            'officeAddress' => self::OFFICE_ADDRESS,
        ];
    }

    private function agreementData(ContractAward $award, ?ContractAgreement $agreement): array
    {
        $d = $this->awardData($award);

        return array_merge($d, [
            'draft' => $agreement === null,
            'date' => $agreement?->agreement_date,
            'agreementNo' => $agreement?->agreement_number,
            'category' => $agreement?->category ?: $d['category'],
        ]);
    }
}
