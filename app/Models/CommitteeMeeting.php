<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class CommitteeMeeting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meeting_date' => 'date',
        'held_at'      => 'datetime',
    ];

    /** Which case-steps each agenda type resolves once the meeting is marked held. */
    public const STEPS_BY_AGENDA = [
        'first'  => [7, 8],   // Tender Opening Form, Conflict of Interest declaration
        'second' => [14, 15], // Comparative Statement, Regulation of Comparative Statement
    ];

    public const AGENDA_LABELS = [
        'first'  => '1st Meeting — Tender Opening & COI',
        'second' => '2nd Meeting — Comparative Statement',
    ];

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(ProcurementCase::class, 'committee_meeting_case')
            ->withPivot(['agenda_type', 'resolved', 'resolved_at'])
            ->withTimestamps();
    }

    public function declarations(): HasMany
    {
        return $this->hasMany(CommitteeDeclaration::class);
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    /** Every active committee member has submitted a COI declaration for this meeting. */
    public function allDeclared(): bool
    {
        $activeCount = CommitteeMember::where('active', true)->count();
        if ($activeCount === 0) {
            return false;
        }

        return $this->declarations()->whereNotNull('declared_at')->count() >= $activeCount;
    }

    public function conflictedCount(): int
    {
        return $this->declarations()->where('has_conflict', true)->count();
    }

    /**
     * Target date for the next sitting: last meeting date + the policy interval
     * (default 15 days), snapped forward to the next Saturday so the fixed
     * bi-weekly cadence always lands on ESDO's committee day.
     */
    public static function suggestNextDate(): Carbon
    {
        $intervalDays = (int) ProcurementPolicy::get(ProcurementPolicy::COMMITTEE_INTERVAL_DAYS, 15);
        $last = static::max('meeting_date');
        $base = $last ? Carbon::parse($last) : now();

        $target = $base->copy()->addDays($intervalDays);
        while (! $target->isSaturday()) {
            $target->addDay();
        }

        return $target;
    }

    public static function nextMeetingNo(): string
    {
        return 'CM-' . now()->year . '-' . str_pad((string) (static::count() + 1), 3, '0', STR_PAD_LEFT);
    }
}
