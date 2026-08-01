<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetLinePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_line_id', 'period_type', 'period_label', 'year_number',
        'period_start', 'period_end', 'no_of_unit', 'rate', 'total',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'no_of_unit' => 'decimal:2',
        'rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class);
    }
}