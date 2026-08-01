<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A committee member listed on the Tender/RFQ Opening Record Form
 * (SL, Name, Designation, Signature). Kept as free-text name/designation
 * rather than a users FK because opening committees are formally
 * constituted per the procurement policy and may include people who
 * are not system users.
 */
class TenderOpeningCommittee extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_opening_id',
        'serial_no',
        'name',
        'designation',
        'signed',
    ];

    protected $casts = [
        'signed' => 'boolean',
    ];

    public function tenderOpening()
    {
        return $this->belongsTo(TenderOpening::class, 'tender_opening_id');
    }
}
