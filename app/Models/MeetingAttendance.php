<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'committee_member_id',
        'name',
        'designation',
        'present',
        'signature_file',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'present' => 'boolean',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function committeeMember()
    {
        return $this->belongsTo(ProcurementCommitteeMember::class, 'committee_member_id');
    }
}
