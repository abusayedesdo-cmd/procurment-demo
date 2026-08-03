<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementUpazila extends Model
{
    protected $fillable = ['name', 'district_id'];

    public function district()
    {
        return $this->belongsTo(ProcurementDistrict::class, 'district_id');
    }

    public function annualPlans()
    {
        return $this->hasMany(ProcurementAnnualPlan::class, 'upazila_id');
    }
}
