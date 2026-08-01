<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementPlanPackagePeriodEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_plan_package_period_id', 'granularity', 'slot_number', 'entry_label', 'no_of_unit', 'rate', 'total',
    ];

    protected $casts = [
        'no_of_unit' => 'decimal:2',
        'rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(ProcurementPlanPackagePeriod::class, 'procurement_plan_package_period_id');
    }
}