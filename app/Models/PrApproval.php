<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'pr_id',
        'user_id',
        'role_at_action',
        'action',
        'acted_at',
        'remarks',
    ];

    protected $casts = [
        'acted_at' => 'date',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
