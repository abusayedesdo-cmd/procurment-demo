<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_category_id', 'chart_of_account_id', 'item_code', 'item_name', 'unit',
        'no_of_units', 'duration', 'unit_cost', 'original_budget', 'approved_budget',
        'percent_change', 'realignment_remarks', 'reported_actual_expense',
    ];

    protected $casts = [
        'no_of_units' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'original_budget' => 'decimal:2',
        'approved_budget' => 'decimal:2',
        'percent_change' => 'decimal:2',
        'reported_actual_expense' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'budget_category_id');
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function expenses()
    {
        return $this->hasMany(BudgetExpense::class);
    }

    public function periods()
    {
        return $this->hasMany(BudgetLinePeriod::class);
    }

    public function periodCovering($date)
    {
        $date = \Illuminate\Support\Carbon::parse($date);

        return $this->periods()
            ->whereIn('period_type', ['previous_year', 'quarter'])
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->get()
            ->first(fn ($p) => $date->betweenIncluded($p->period_start, $p->period_end));
    }

    public function coversDate($date): bool
    {
        // If no periods have been defined for this line yet, don't block anything —
        // stays backward-compatible with lines that are still single-total.
        if (! $this->periods()->whereIn('period_type', ['previous_year', 'quarter'])->exists()) {
            return true;
        }

        return (bool) $this->periodCovering($date);
    }

    public function trackedExpenseTotal(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    public function totalExpense(): float
    {
        return (float) $this->reported_actual_expense + $this->trackedExpenseTotal();
    }

    public function balance(): float
    {
        return (float) $this->approved_budget - $this->totalExpense();
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance() >= $amount;
    }
}