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
        'quotation_id',
        'trade_license_verified',
        'tin_verified',
        'bin_verified',
        'psr_verified',
        'eligible',
        'remarks',
    ];

    protected $casts = [
        'trade_license_verified' => 'boolean',
        'tin_verified' => 'boolean',
        'bin_verified' => 'boolean',
        'psr_verified' => 'boolean',
        'eligible' => 'boolean',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function report()
    {
        return $this->belongsTo(EligibilityReport::class, 'eligibility_report_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
