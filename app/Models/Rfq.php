<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_case_id',
        'office',
        'rfq_number',
        'subject',
        'type',
        'issue_date',
        'closing_date',
        'file_path',
        'terms_conditions',
        'distribution_process',
        'public_token',
        'status',
        'finalized_at',
        'finalized_by',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'closing_date' => 'date',
        'finalized_at' => 'datetime',
    ];

    /** The user who created this RFQ — the signatory printed on its documents. */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The standard terms used when no term has been ticked on the RFQ form (older RFQs). */
    public const FALLBACK_TERMS = [
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

    /**
     * The numbered Terms & Conditions of this RFQ — the single list shown on the RFQ Word/PDF and to
     * vendors in the vendor portal: the two per-RFQ facts (opening date, delivery location), then the
     * terms the officer ticked on the RFQ form (or the standard list for older RFQs), then any
     * "Additional Terms" typed as free text (one per line).
     *
     * @return array<int,string>
     */
    public function termsList(): array
    {
        $closing = $this->closing_date ? $this->closing_date->format('d F, Y') : null;
        $delivery = $this->procurementCase?->purchaseRequisition?->delivery_location;

        $picked = $this->termsConditions()->get();
        $pickedTerms = $picked->isNotEmpty() ? $picked->pluck('text')->all() : self::FALLBACK_TERMS;
        $extraTerms = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->terms_conditions))));

        return array_merge([
            'Quotation will be Opened on ' . ($closing ?: '[date]') . ' at 04:00 PM (Those who will submit the quotation are invited to present at the opening time).',
            'Delivery Location: equipment must be delivered to "[Delivery Schedule]" office in ' . ($delivery ?? '[Project Location]') . '.',
        ], $pickedTerms, $extraTerms);
    }

    // The officer-selected items from the RFQ Terms & Conditions master
    // list (see rfq-terms-conditions module). If empty, the document
    // builder falls back to the old fixed 11-point list for old RFQs.
    public function termsConditions()
    {
        return $this->belongsToMany(RfqTermsCondition::class, 'rfq_terms_condition_rfq')
            ->orderBy('rfq_terms_conditions.sort_order')
            ->orderBy('rfq_terms_conditions.id')
            ->withTimestamps();
    }

    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    protected static function booted()
    {
        static::creating(function ($rfq) {
            if (empty($rfq->public_token)) {
                $rfq->public_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function procurementCase()
    {
        return $this->belongsTo(ProcurementCase::class, 'procurement_case_id');
    }

    public function tenderSchedules()
    {
        return $this->hasMany(TenderSchedule::class, 'rfq_id');
    }

    public function items()
    {
        return $this->hasMany(RfqItem::class, 'rfq_id')->orderBy('id');
    }

    public function tenderProposals()
    {
        return $this->hasMany(TenderProposal::class, 'rfq_id');
    }

    public function tenderAdvertisements()
    {
        return $this->hasMany(TenderAdvertisement::class, 'rfq_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'rfq_id');
    }

    public function tenderOpenings()
    {
        return $this->hasMany(TenderOpening::class, 'rfq_id');
    }

    public function eligibilityReports()
    {
        return $this->hasMany(EligibilityReport::class, 'rfq_id');
    }

    public function technicalEvaluationReports()
    {
        return $this->hasMany(TechnicalEvaluationReport::class, 'rfq_id');
    }

    public function financialEvaluationReports()
    {
        return $this->hasMany(FinancialEvaluationReport::class, 'rfq_id');
    }

    public function comparativeStatements()
    {
        return $this->hasMany(ComparativeStatement::class, 'rfq_id');
    }

}
