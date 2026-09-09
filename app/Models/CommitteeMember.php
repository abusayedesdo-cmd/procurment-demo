<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'committee_id',
        'user_id',
        'procurement_committee_member_id',
        'designation_in_committee',
    ];

    protected $appends = ['member_name'];

    public function committee()
    {
        return $this->belongsTo(PurchaseCommittee::class, 'committee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // For Sub-Committee members picked from the procurement_committee_members
    // roster instead of a login User (see the 2026_09_08 migration).
    public function procurementCommitteeMember()
    {
        return $this->belongsTo(ProcurementCommitteeMember::class);
    }

    // Whichever of the two sources this member came from.
    public function getMemberNameAttribute(): ?string
    {
        return $this->user?->name ?? $this->procurementCommitteeMember?->name;
    }

}
