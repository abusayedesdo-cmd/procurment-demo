<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'sort_order'];

    public function budgetLines()
    {
        return $this->hasMany(BudgetLine::class);
    }
}