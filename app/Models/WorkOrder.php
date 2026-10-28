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
        'terms',
        'file_path',
        'created_by',
    ];

    protected $casts = [
        'wo_date' => 'date',
        'terms' => 'array',
    ];

    public function contractAgreement()
    {
        return $this->belongsTo(ContractAgreement::class, 'contract_agreement_id');
    }

    public function deliveryReceipts()
    {
        return $this->hasMany(DeliveryReceipt::class, 'work_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
