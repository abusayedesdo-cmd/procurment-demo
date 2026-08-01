<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'received_pr_date',
        'nature',
        'estimated_amount',
        'status',
        'est_advertisement_date',
        'est_closing_date',
        'est_opening_date',
        'est_evaluation_date',
        'est_noa_date',
        'est_contract_signing_date',
        'est_work_order_date',
        'est_delivery_date',
        'est_completion_days',
    ];

    protected $casts = [
        'received_pr_date' => 'date',
        'estimated_amount' => 'decimal:2',
        'est_advertisement_date' => 'date',
        'est_closing_date' => 'date',
        'est_opening_date' => 'date',
        'est_evaluation_date' => 'date',
        'est_noa_date' => 'date',
        'est_contract_signing_date' => 'date',
        'est_work_order_date' => 'date',
        'est_delivery_date' => 'date',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'procurement_plan_id');
    }

    public function subCommitteeTransfers()
    {
        return $this->hasMany(SubCommitteeTransfer::class, 'procurement_plan_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class, 'procurement_plan_id');
    }

    public function contractAwards()
    {
        return $this->hasMany(ContractAward::class, 'procurement_plan_id');
    }

}
