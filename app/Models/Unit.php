<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
    ];

    public function prItems()
    {
        return $this->hasMany(PrItem::class, 'unit_id');
    }

    public function rfqItems()
    {
        return $this->hasMany(RfqItem::class, 'unit_id');
    }

}
