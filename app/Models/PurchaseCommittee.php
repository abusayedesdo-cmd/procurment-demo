<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCommittee extends Model
{
    use HasFactory;
    use BelongsToProject;

    protected $fillable = [
        'name',
        'address',
        'type',
        'parent_committee_id',
        'project_id',
    ];

    public function parentCommittee()
    {
        return $this->belongsTo(PurchaseCommittee::class, 'parent_committee_id');
    }

    public function children()
    {
        return $this->hasMany(PurchaseCommittee::class, 'parent_committee_id');
    }

    public function members()
    {
        return $this->hasMany(CommitteeMember::class, 'committee_id');
    }

}