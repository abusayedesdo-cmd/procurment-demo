<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'minutes_number',
        'resolution_text',
        'file_path',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

}
