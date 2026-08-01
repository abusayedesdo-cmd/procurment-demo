<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparativeStatementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'comparative_statement_id',
        'vendor_id',
        'rank',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function statement()
    {
        return $this->belongsTo(ComparativeStatement::class, 'comparative_statement_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
