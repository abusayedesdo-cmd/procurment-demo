<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A vendor's unit price / amount for one RfqItem line, within a Quotation.
 * amount is stored (not derived) so it survives even if quantity/unit_price
 * validation rules change later, and so totals can be audited against the
 * submitted paper quotation.
 */
class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'rfq_item_id',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function rfqItem()
    {
        return $this->belongsTo(RfqItem::class, 'rfq_item_id');
    }
}
