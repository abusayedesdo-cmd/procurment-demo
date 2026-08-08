<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrBudgetCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id', 'pr_approval_id', 'budget_line_id', 'budget_code', 'available_budget_amount', 'allocated_budget','remaining_budget_bf', 'remaining_budget_cf',
        'is_budget_code_verified', 'is_budget_available', 'decision', 'checked_by', 'checked_at', 'remarks',
    ];

    protected $casts = [
        'available_budget_amount' => 'decimal:2',
        'is_budget_code_verified' => 'boolean',
        'is_budget_available' => 'boolean',
        'allocated_budget' => 'decimal:2',
        'remaining_budget_bf' => 'decimal:2',
        'remaining_budget_cf' => 'decimal:2',
        'checked_at' => 'date',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function approval()
    {
        return $this->belongsTo(PrApproval::class, 'pr_approval_id');
    }

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}