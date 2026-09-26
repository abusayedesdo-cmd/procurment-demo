<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_case_id',
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
    ];

    protected $casts = [
        'issue_date' => 'date',
        'closing_date' => 'date',
        'finalized_at' => 'datetime',
    ];

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
