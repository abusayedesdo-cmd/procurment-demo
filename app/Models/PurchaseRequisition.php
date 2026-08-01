<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_number',
        'window_type',
        'category_id',
<<<<<<< HEAD
        'budget_line_id',
        'procurement_plan_package_id',
=======
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc
        'requisition_date',
        'estimated_delivery_date',
        'total_estimated_amount',
        'status',
        'raised_by',
        'remarks',
    ];

    protected $casts = [
        'requisition_date' => 'date',
        'estimated_delivery_date' => 'date',
        'total_estimated_amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class, 'category_id');
    }
<<<<<<< HEAD
    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function package()
    {
        return $this->belongsTo(ProcurementPlanPackage::class, 'procurement_plan_package_id');
    }

    public function budgetChecks()
    {
        return $this->hasMany(PrBudgetCheck::class, 'pr_id');
    }
=======
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc

    public function raisedBy()
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function items()
    {
        return $this->hasMany(PrItem::class, 'pr_id');
    }

    public function approvals()
    {
        return $this->hasMany(PrApproval::class, 'pr_id');
    }

    public function boqDetail()
    {
        return $this->hasOne(BoqDetail::class, 'pr_id');
    }

    public function torDetail()
    {
        return $this->hasOne(TorDetail::class, 'pr_id');
    }

    public function designDrawing()
    {
        return $this->hasOne(DesignDrawing::class, 'pr_id');
    }

    public function procurementPlan()
    {
        return $this->hasOne(ProcurementPlan::class, 'pr_id');
    }

    public function soleSourcingRequests()
    {
        return $this->hasMany(SoleSourcingRequest::class, 'pr_id');
    }

}
