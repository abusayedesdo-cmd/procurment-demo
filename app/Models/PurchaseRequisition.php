<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory, BelongsToProject;

    protected $fillable = [
        'pr_number',
        'window_type',
        'category_id',
        'project_name',
        'project_id',
        'budget_line_id',
        'procurement_plan_package_id',
        'requisition_date',
        'estimated_delivery_date',
        'delivery_location',
        'estimated_delivery_time',
        'total_estimated_amount',
        'status',
        'routed_to',
        'raised_by',
        'requestor_name',
        'requestor_designation',
        'remarks',
        'attachment_path',
    ];

    protected $casts = [
        'requisition_date' => 'date',
        'estimated_delivery_date' => 'date',
        'total_estimated_amount' => 'decimal:2',
    ];

    protected $appends = ['attachment_url'];

    /**
     * When a PR is raised against a specific plan package, it must sit in
     * that package's project rather than whatever the trait guessed from
     * the creating user (matters for Admin/Procurement Officer, who have
     * no project_id of their own but can raise PRs into any project).
     */
    protected static function booted(): void
    {
        static::creating(function (self $pr) {
            if (! $pr->procurement_plan_package_id) {
                return;
            }

            $packageProjectId = ProcurementPlanPackage::withoutGlobalScopes()
                ->whereKey($pr->procurement_plan_package_id)
                ->value('project_id');

            if ($packageProjectId) {
                $pr->project_id = $packageProjectId;
            }
        });
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path ? asset($this->attachment_path) : null;
    }

    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class, 'category_id');
    }
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