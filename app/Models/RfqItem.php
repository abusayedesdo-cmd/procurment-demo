<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single requested line item under an RFQ/OTM (Annex II style price
 * schedule: SL, category, short description, quantity, unit). Vendors then
 * quote against each of these via QuotationItem.
 */
class RfqItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'category',
        'serial_no',
        'description',
        'quantity',
        'unit_id',
        'delivery_address',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class, 'rfq_item_id');
    }
}
