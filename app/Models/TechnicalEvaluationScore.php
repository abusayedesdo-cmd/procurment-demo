<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalEvaluationScore extends Model
{
    protected $fillable = ['technical_evaluation_item_id', 'criterion_id', 'score'];

    protected $casts = ['score' => 'decimal:2'];

    public function item()
    {
        return $this->belongsTo(TechnicalEvaluationItem::class, 'technical_evaluation_item_id');
    }

    public function criterion()
    {
        return $this->belongsTo(TechnicalEvaluationCriterion::class, 'criterion_id');
    }
}