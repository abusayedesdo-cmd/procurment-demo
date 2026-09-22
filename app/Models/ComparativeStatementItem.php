<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparativeStatementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'comparative_statement_id',
        'vendor_id',
        'financial_evaluation_item_id',
        'technical_evaluation_item_id',
        'financial_marks',
        'technical_marks',
        'total_marks',
        'rank',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'financial_marks' => 'decimal:2',
        'technical_marks' => 'decimal:2',
        'total_marks' => 'decimal:2',
    ];

    public function financialEvaluationItem()
    {
        return $this->belongsTo(FinancialEvaluationItem::class, 'financial_evaluation_item_id');
    }

    public function technicalEvaluationItem()
    {
        return $this->belongsTo(TechnicalEvaluationItem::class, 'technical_evaluation_item_id');
    }

    public function statement()
    {
        return $this->belongsTo(ComparativeStatement::class, 'comparative_statement_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
