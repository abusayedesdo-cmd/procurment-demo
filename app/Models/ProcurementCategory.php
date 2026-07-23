<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function chartOfAccounts()
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class, 'category_id');
    }

    public function frameworkAgreements()
    {
        return $this->hasMany(FrameworkAgreement::class, 'category_id');
    }

}
