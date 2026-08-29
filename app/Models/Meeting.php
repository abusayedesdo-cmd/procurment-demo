<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_case_id',
        'meeting_type',
        'rezulation_no',
        'location',
        'meeting_date',
        'meeting_time',
        'notice_number',
        'notice_date',
        'notice_file',
        'attendance_number',
        'agenda',
        'publish_date',
        'closing_date',
        'opening_date',
        'schedule_override_reason',
        'decisions',
        'attendance_file',
        'minutes_file',
        'held_at',
        'recorded_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'notice_date' => 'date',
        'publish_date' => 'date',
        'closing_date' => 'date',
        'opening_date' => 'date',
        'held_at' => 'datetime',
    ];

    public function procurementCase()
    {
        return $this->belongsTo(ProcurementCase::class, 'procurement_case_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function attendees()
    {
        return $this->hasMany(MeetingAttendance::class, 'meeting_id')->orderBy('sort_order');
    }

    public function awards()
    {
        return $this->hasMany(MeetingAward::class, 'meeting_id');
    }

    public function isHeld(): bool
    {
        return ! is_null($this->held_at);
    }

    public function typeLabel(): string
    {
        return $this->meeting_type === 'first' ? '1st Meeting' : '2nd Meeting';
    }

    public function totalAwarded(): float
    {
        return (float) $this->awards->sum('amount');
    }
}
