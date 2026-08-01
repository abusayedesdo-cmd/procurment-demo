<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalEvaluationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ter_id',
        'vendor_id',
        'score',
        'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function report()
    {
        return $this->belongsTo(TechnicalEvaluationReport::class, 'ter_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
