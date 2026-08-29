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
    ];

    protected $casts = [
        'issue_date' => 'date',
        'closing_date' => 'date',
    ];

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
        return $this->hasMany(RfqItem::class, 'rfq_id');
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
