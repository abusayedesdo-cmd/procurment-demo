<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'name',
    ];

    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'chart_of_account_id');
    }

}
