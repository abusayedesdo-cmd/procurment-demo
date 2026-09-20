<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashPurchase extends Model
{
    protected $fillable = [
        'pr_id', 'committee_id', 'vendor_id', 'item_description',
        'amount', 'purchase_date', 'receipt_file', 'notes', 'purchased_by',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function committee()
    {
        return $this->belongsTo(PurchaseCommittee::class, 'committee_id');
    }

    public function purchasedByUser()
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}