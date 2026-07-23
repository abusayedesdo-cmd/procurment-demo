<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementCommitteeMember extends Model
{
    protected $guarded = [];

    protected $casts = ['active' => 'boolean'];

    public const ROLE_LABELS = [
        'convener' => 'Convener',
        'member' => 'Member',
        'member_secretary' => 'Member Secretary',
    ];

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }

    public static function activeRoster()
    {
        return static::where('active', true)->orderBy('sort_order')->get();
    }
}
