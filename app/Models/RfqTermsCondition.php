<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfqTermsCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function rfqs()
    {
        return $this->belongsToMany(Rfq::class, 'rfq_terms_condition_rfq')->withTimestamps();
    }
}
