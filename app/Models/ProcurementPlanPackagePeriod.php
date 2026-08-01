<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementPlanPackagePeriod extends Model
{
    use HasFactory;

    protected $fillable = ['procurement_plan_package_id', 'period_type', 'period_label', 'year_number', 'slot_order', 'breakdown_granularity', 'no_of_unit', 'rate', 'total'];

    protected $casts = [
        'no_of_unit' => 'decimal:2',
        'rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];
   public function package()
    {
        return $this->belongsTo(ProcurementPlanPackage::class, 'procurement_plan_package_id');
    }

  public function entries()
    {
        return $this->hasMany(ProcurementPlanPackagePeriodEntry::class, 'procurement_plan_package_period_id')->orderBy('slot_number');
    }

    /**
     * Auto-derives a 4-quarter rollup from 12 monthly entries, purely for
     * display in downloads — grouping months 1-3, 4-6, 7-9, 10-12.
     * Returns null when this period wasn't entered monthly.
     */
    public function quarterlyRollup(): ?array
    {
        if ($this->breakdown_granularity !== 'month') {
            return null;
        }

        $quarters = [];
        foreach ($this->entries->chunk(3) as $i => $chunk) {
            $quarters[] = [
                'label' => 'Quarter-' . ($i + 1),
                'no_of_unit' => (float) $chunk->sum('no_of_unit'),
                'total' => (float) $chunk->sum('total'),
            ];
        }

        return $quarters;
    }
}