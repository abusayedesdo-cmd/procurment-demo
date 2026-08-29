<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingAward extends Model
{
    protected $fillable = [
        'meeting_id',
        'vendor_id',
        'vendor_name',
        'scope_note',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
