<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'opening_date',
        'venue',
        'opening_time',
        'opened_by',
        'report_file',
        'remarks',
    ];

    protected $casts = [
        'opening_date' => 'date',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function committeeMembers()
    {
        return $this->hasMany(TenderOpeningCommittee::class, 'tender_opening_id');
    }

}
