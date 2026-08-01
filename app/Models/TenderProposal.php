<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenderProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_id',
        'proposal_details',
        'file_path',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

}
