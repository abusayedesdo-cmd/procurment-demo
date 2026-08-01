<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetExpense extends Model
{
    use HasFactory;

    protected $fillable = ['budget_line_id', 'pr_id', 'amount', 'expense_date', 'source', 'remarks', 'recorded_by'];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}