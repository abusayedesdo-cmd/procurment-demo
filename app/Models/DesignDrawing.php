<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignDrawing extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'file_path',
        'drawing_no',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

}
