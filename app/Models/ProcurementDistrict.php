<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementDistrict extends Model
{
    protected $fillable = ['name'];

    public function annualPlans()
    {
        return $this->hasMany(ProcurementAnnualPlan::class, 'district_id');
    }
}
