<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'email',
        'phone',
        'trade_license_no',
        'vat_reg_no',
        'tax_id',
    ];

    public function documents()
    {
        return $this->hasMany(VendorDocument::class, 'vendor_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'vendor_id');
    }

    public function eligibilityReportItems()
    {
        return $this->hasMany(EligibilityReportItem::class, 'vendor_id');
    }

    public function technicalEvaluationItems()
    {
        return $this->hasMany(TechnicalEvaluationItem::class, 'vendor_id');
    }

    public function financialEvaluationItems()
    {
        return $this->hasMany(FinancialEvaluationItem::class, 'vendor_id');
    }

    public function comparativeStatementItems()
    {
        return $this->hasMany(ComparativeStatementItem::class, 'vendor_id');
    }

    public function contractAwards()
    {
        return $this->hasMany(ContractAward::class, 'vendor_id');
    }

    public function frameworkAgreements()
    {
        return $this->hasMany(FrameworkAgreement::class, 'vendor_id');
    }

    public function soleSourcingRequests()
    {
        return $this->hasMany(SoleSourcingRequest::class, 'vendor_id');
    }

}
