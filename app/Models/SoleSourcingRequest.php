<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoleSourcingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'vendor_id',
        'justification',
        'approved_by',
        'approval_date',
        'file_path',
    ];

    protected $casts = [
        'approval_date' => 'date',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}
