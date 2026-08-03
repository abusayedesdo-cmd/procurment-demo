<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'serial_no',
        'item_id',
        'specification',
        'ac_code',
        'is_fixed_asset',
        'unit_id',
        'quantity',
        'rate_bdt',
        'total_amount',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate_bdt' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_fixed_asset' => 'boolean',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

}
