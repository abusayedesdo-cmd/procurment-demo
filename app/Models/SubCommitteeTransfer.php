<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCommitteeTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_plan_id',
        'from_committee_id',
        'to_committee_id',
        'transfer_note',
        'transfer_date',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function procurementPlan()
    {
        return $this->belongsTo(ProcurementPlan::class, 'procurement_plan_id');
    }

    public function fromCommittee()
    {
        return $this->belongsTo(PurchaseCommittee::class, 'from_committee_id');
    }

    public function toCommittee()
    {
        return $this->belongsTo(PurchaseCommittee::class, 'to_committee_id');
    }

}
