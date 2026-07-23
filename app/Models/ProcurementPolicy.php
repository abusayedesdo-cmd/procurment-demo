<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ProcurementPolicy extends Model
{
    protected $guarded = [];

    protected $casts = [
        'value' => 'float',
    ];

    /** Amount-threshold keys: below = Quotation (RFQ/RFP), at/above = Open Tender Method (OTM). */
    public const THRESHOLD_GOODS = 'threshold_goods_bdt';
    public const THRESHOLD_WORKS = 'threshold_works_bdt';
    public const THRESHOLD_SERVICES = 'threshold_services_bdt';

    /** Milestone day-offsets, counted from the PR date. */
    public const OFFSET_PUBLISH = 'offset_publish_days';
    public const OFFSET_CLOSING = 'offset_closing_days';
    public const OFFSET_OPENING = 'offset_opening_days';
    public const OFFSET_EVALUATION = 'offset_evaluation_days';
    public const OFFSET_NOA = 'offset_noa_days';
    public const OFFSET_CONTRACT = 'offset_contract_days';
    public const OFFSET_WORK_ORDER = 'offset_work_order_days';
    public const OFFSET_DELIVERY = 'offset_delivery_days';

    public const THRESHOLD_BY_CATEGORY = [
        'Goods'    => self::THRESHOLD_GOODS,
        'Works'    => self::THRESHOLD_WORKS,
        'Services' => self::THRESHOLD_SERVICES,
    ];

    public static function get(string $key, float $default = 0): float
    {
        return (float) Cache::rememberForever("policy:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, float $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget("policy:{$key}");
    }

    public static function forgetAll(): void
    {
        foreach (static::pluck('key') as $key) {
            Cache::forget("policy:{$key}");
        }
    }
}
