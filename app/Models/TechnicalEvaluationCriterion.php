<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalEvaluationCriterion extends Model
{
    protected $fillable = ['ter_id', 'name', 'max_marks', 'sort_order'];

    protected $casts = ['max_marks' => 'decimal:2'];

    public function report()
    {
        return $this->belongsTo(TechnicalEvaluationReport::class, 'ter_id');
    }

    public function scores()
    {
        return $this->hasMany(TechnicalEvaluationScore::class, 'criterion_id');
    }
}