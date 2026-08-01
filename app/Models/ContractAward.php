<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAward extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_plan_id',
        'category',
        'vendor_id',
        'noa_number',
        'noa_date',
        'file_path',
    ];

    protected $casts = [
        'noa_date' => 'date',
    ];

    public function procurementPlan()
    {
        return $this->belongsTo(ProcurementPlan::class, 'procurement_plan_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function payOrders()
    {
        return $this->hasMany(PayOrder::class, 'contract_award_id');
    }

    public function contractAgreements()
    {
        return $this->hasMany(ContractAgreement::class, 'contract_award_id');
    }

}
