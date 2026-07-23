<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TorDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'file_path',
        'scope_of_work',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

}
