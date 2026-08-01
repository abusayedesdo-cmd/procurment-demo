<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'eligibility_report_id',
        'vendor_id',
        'eligible',
        'remarks',
    ];

    protected $casts = [
        'eligible' => 'boolean',
    ];

    public function report()
    {
        return $this->belongsTo(EligibilityReport::class, 'eligibility_report_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
