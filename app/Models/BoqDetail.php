<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoqDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'file_path',
        'description',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

}
