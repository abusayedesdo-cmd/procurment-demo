<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementAnnualPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_type', 'title', 'project_name', 'district', 'project_location', 'working_area',
        'activity_summary', 'fiscal_year_start', 'fiscal_year_end', 'project_duration', 'agreement_date',
        'donor_name', 'funding_source', 'prepared_by', 'approved_by', 'status',
    ];

    protected $casts = [
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
        'agreement_date' => 'date',
    ];

    public function packages()
    {
        return $this->hasMany(ProcurementPlanPackage::class);
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
  public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * "Year-1" is defined as starting 2 years after the plan's fiscal_year_start
     * (i.e. fiscal_year_start is the start of the "Previous 2nd Year").
     * Matches the screenshot's column layout exactly.
     */
    public function periodSlots(): array
    {
        $y = $this->fiscal_year_start->year + 2;

        return [
            'previous_2nd_year' => ['period_type' => 'previous_year', 'year_number' => null, 'label' => ($y - 2) . '-' . ($y - 1)],
            'previous_1st_year' => ['period_type' => 'previous_year', 'year_number' => null, 'label' => ($y - 1) . '-' . $y],
            'current_year' => ['period_type' => 'current_year', 'year_number' => 1, 'label' => 'Current Year (' . $y . '-' . ($y + 1) . ')'],
            'quarter_1' => ['period_type' => 'quarter', 'year_number' => 1, 'label' => 'Quarter-1 (July-October)'],
            'quarter_2' => ['period_type' => 'quarter', 'year_number' => 1, 'label' => 'Quarter-2 (November-February)'],
            'quarter_3' => ['period_type' => 'quarter', 'year_number' => 1, 'label' => 'Quarter-3 (March-June)'],
            'year_1_total' => ['period_type' => 'year_total', 'year_number' => 1, 'label' => 'Plan/Budget for ' . $y . '-' . ($y + 1)],
            'year_2_total' => ['period_type' => 'year_total', 'year_number' => 2, 'label' => 'Plan/Budget for ' . ($y + 1) . '-' . ($y + 2)],
            'year_3_total' => ['period_type' => 'year_total', 'year_number' => 3, 'label' => 'Plan/Budget for ' . ($y + 2) . '-' . ($y + 3)],
            'grand_total' => ['period_type' => 'grand_total', 'year_number' => null, 'label' => 'Plan/Budget for Total Project Duration'],
        ];
    }
}