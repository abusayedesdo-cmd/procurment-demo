<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_agreement_id',
        'category',
        'wo_number',
        'wo_date',
        'file_path',
    ];

    protected $casts = [
        'wo_date' => 'date',
    ];

    public function contractAgreement()
    {
        return $this->belongsTo(ContractAgreement::class, 'contract_agreement_id');
    }

    public function deliveryReceipts()
    {
        return $this->hasMany(DeliveryReceipt::class, 'work_order_id');
    }

}
