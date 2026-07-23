<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'category',
        'schedule_details',
        'file_path',
        'validity_days',
        'performance_security_percent',
        'delay_penalty_percent',
        'payment_terms',
        'award_type',
        'contract_type',
        'technical_weight',
        'financial_weight',
    ];
    protected $casts = [
    'performance_security_percent' => 'decimal:2',
    'delay_penalty_percent' => 'decimal:2',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

}
