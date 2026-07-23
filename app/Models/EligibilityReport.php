<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'prepared_by',
        'report_file',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function items()
    {
        return $this->hasMany(EligibilityReportItem::class, 'eligibility_report_id');
    }

}
