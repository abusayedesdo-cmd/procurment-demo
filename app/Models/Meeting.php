<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_plan_id',
        'meeting_sequence',
        'meeting_date',
        'notice_number',
        'notice_file',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    public function procurementPlan()
    {
        return $this->belongsTo(ProcurementPlan::class, 'procurement_plan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class, 'meeting_id');
    }

    public function minutes()
    {
        return $this->hasMany(MeetingMinute::class, 'meeting_id');
    }

}
