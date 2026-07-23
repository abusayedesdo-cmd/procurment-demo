<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparativeStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'prepared_by',
        'lowest_evaluated_vendor_id',
        'file_path',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function lowestEvaluatedVendor()
    {
        return $this->belongsTo(Vendor::class, 'lowest_evaluated_vendor_id');
    }

    public function items()
    {
        return $this->hasMany(ComparativeStatementItem::class, 'comparative_statement_id');
    }

}
