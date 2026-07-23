<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_award_id',
        'awarded_amount',
        'pay_order_amount',
        'received_amount',
        'received_date',
        'calculation_details',
    ];

    protected $casts = [
        'awarded_amount' => 'decimal:2',
        'pay_order_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'received_date' => 'date',
    ];

    public function contractAward()
    {
        return $this->belongsTo(ContractAward::class, 'contract_award_id');
    }

}
