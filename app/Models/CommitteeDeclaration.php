<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeDeclaration extends Model
{
    protected $guarded = [];

    protected $casts = [
        'has_conflict' => 'boolean',
        'declared_at'  => 'datetime',
    ];

    public function committeeMeeting(): BelongsTo
    {
        return $this->belongsTo(CommitteeMeeting::class);
    }

    public function committeeMember(): BelongsTo
    {
        return $this->belongsTo(CommitteeMember::class);
    }

    public function isDeclared(): bool
    {
        return $this->declared_at !== null;
    }
}
