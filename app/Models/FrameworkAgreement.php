<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrameworkAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'start_date',
        'end_date',
        'terms',
        'file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class, 'category_id');
    }

}
