<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderAdvertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'medium',
        'category',
        'publish_date',
        'file_path',
    ];

    protected $casts = [
        'publish_date' => 'date',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

}
