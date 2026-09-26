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
        'earnest_money_required',
        'earnest_money_amount',
        'earnest_money_status',
        'earnest_money_notes',
        'terms_accepted',
        'delivery_terms_accepted',
        'general_experience',
        'relevant_experience',
        'submitted_via_portal',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'quoted_amount' => 'decimal:2',
        'attended' => 'boolean',
        'trade_license_submitted' => 'boolean',
        'tin_submitted' => 'boolean',
        'bin_submitted' => 'boolean',
        'earnest_money_required' => 'boolean',
        'earnest_money_amount' => 'decimal:2',
        'terms_accepted' => 'boolean',
        'delivery_terms_accepted' => 'boolean',
        'submitted_via_portal' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    // Who forwarded/rejected this quotation at the Opening Report step.
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class, 'quotation_id');
    }

}
