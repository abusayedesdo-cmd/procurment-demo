<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStep extends Model
{
    protected $guarded = [];

    protected $casts = ['completed_at' => 'datetime'];

    public function procurementCase(): BelongsTo
    {
        return $this->belongsTo(ProcurementCase::class);
    }

    public function isDone(): bool
    {
        return $this->completed_at !== null;
    }
}
