<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialEvaluationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fer_id',
        'vendor_id',
        'quoted_amount',
        'remarks',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
    ];

    public function report()
    {
        return $this->belongsTo(FinancialEvaluationReport::class, 'fer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
