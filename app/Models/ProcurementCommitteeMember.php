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

    public const CENTRAL_PROCUREMENT = 'central_procurement';
    public const PURCHASE_COMMITTEE = 'purchase_committee';

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }

    /**
     * @param string $committeeType one of CENTRAL_PROCUREMENT / PURCHASE_COMMITTEE
     * @param string|null $branch optional branch/zone filter (null = HQ / not branch-specific)
     */
    public static function activeRoster(string $committeeType = self::CENTRAL_PROCUREMENT, ?string $branch = null)
    {
        return static::where('active', true)
            ->where('committee_type', $committeeType)
            ->when($branch !== null, fn ($q) => $q->where('branch', $branch))
            ->orderBy('sort_order')
            ->get();
    }
}
