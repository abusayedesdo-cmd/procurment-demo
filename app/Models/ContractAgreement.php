<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_award_id',
        'category',
        'agreement_number',
        'agreement_date',
        'file_path',
    ];

    protected $casts = [
        'agreement_date' => 'date',
    ];

    public function contractAward()
    {
        return $this->belongsTo(ContractAward::class, 'contract_award_id');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'contract_agreement_id');
    }

}
