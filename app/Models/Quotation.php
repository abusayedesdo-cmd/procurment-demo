<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'submitted_at',
        'quoted_amount',
        'file_path',
        'status',
        'representative_name',
        'representative_contact',
        'attended',
        'trade_license_submitted',
        'tin_submitted',
        'bin_submitted',
        'opening_remarks',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'quoted_amount' => 'decimal:2',
        'attended' => 'boolean',
        'trade_license_submitted' => 'boolean',
        'tin_submitted' => 'boolean',
        'bin_submitted' => 'boolean',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

}
