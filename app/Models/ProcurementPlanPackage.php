<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementPlanPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_annual_plan_id', 'sl_no', 'procurement_category_id', 'chart_of_account_id', 'budgeted_head', 'item_id', 'specification', 'unit',
        'package_number', 'estimated_cost', 'procurement_method', 'responsible_officer_id', 'budget_line_id',
        'planned_invitation_date', 'actual_invitation_date', 'planned_evaluation_date', 'actual_evaluation_date',
        'planned_award_date', 'actual_award_date', 'planned_delivery_date', 'actual_delivery_date', 'remarks',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'planned_invitation_date' => 'date', 'actual_invitation_date' => 'date',
        'planned_evaluation_date' => 'date', 'actual_evaluation_date' => 'date',
        'planned_award_date' => 'date', 'actual_award_date' => 'date',
        'planned_delivery_date' => 'date', 'actual_delivery_date' => 'date',
    ];

    protected $appends = ['delay_days', 'already_procured', 'remaining_balance'];

    protected const SLOT_ORDER = [
        'previous_2nd_year' => 0,
        'previous_1st_year' => 1,
        'current_year' => 2,
        'quarter_1' => 3,
        'quarter_2' => 4,
        'quarter_3' => 5,
        'year_1_total' => 6,
        'year_2_total' => 7,
        'year_3_total' => 8,
        'grand_total' => 9,
    ];

    protected const BREAKDOWN_KEYS = ['previous_2nd_year', 'previous_1st_year', 'current_year', 'year_2_total', 'year_3_total'];

    protected const MONTH_NAMES = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    public function plan()
    {
        return $this->belongsTo(ProcurementAnnualPlan::class, 'procurement_annual_plan_id');
    }

    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class, 'procurement_category_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function responsibleOfficer()
    {
        return $this->belongsTo(User::class, 'responsible_officer_id');
    }

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function periods()
    {
        return $this->hasMany(ProcurementPlanPackagePeriod::class)->orderBy('slot_order');
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class, 'procurement_plan_package_id');
    }

    public function periodBySlotKey(string $key)
    {
        return $this->periods->firstWhere('slot_order', self::SLOT_ORDER[$key] ?? -1);
    }

    /**
     * Returns one value per label for a given period slot. If this
     * package's period was broken down with exactly as many entries as
     * $labels has slots, returns each entry's real total in order.
     * Otherwise (flat/yearly, or a mismatched granularity) the single
     * collapsed total goes in the first slot and the rest are null —
     * never invented, never redistributed.
     */
    public function alignedValuesFor(string $key, array $sublabels): array
    {
        $count = count($sublabels);
        $empty = ['no_of_unit' => null, 'rate' => null, 'total' => null];

        $period = $this->periodBySlotKey($key);

        if (! $period) {
            return array_fill(0, $count, $empty);
        }

        if ($period->breakdown_granularity && $period->entries->count() === $count) {
            return $period->entries->map(fn ($e) => [
                'no_of_unit' => (float) $e->no_of_unit,
                'rate' => (float) $e->rate,
                'total' => (float) $e->total,
            ])->all();
        }

        $values = array_fill(0, $count, $empty);
        $values[0] = ['no_of_unit' => (float) $period->no_of_unit, 'rate' => (float) $period->rate, 'total' => (float) $period->total];

        return $values;
    }

    public function getAlreadyProcuredAttribute(): float
    {
        return (float) $this->purchaseRequisitions()->where('status', 'approved')->sum('total_estimated_amount');
    }

    public function getRemainingBalanceAttribute(): float
    {
        $grand = $this->periods->firstWhere('period_type', 'grand_total');

        return (float) ($grand->total ?? 0) - $this->already_procured;
    }

    /**
     * Takes the 7 manually-entered period slots (each optionally broken down
     * into 12 monthly or 4 quarterly entries), computes Year-1 Total (sum of
     * its 3 quarters) and the Grand Total, and upserts all 9 period rows plus
     * their child entries. Nothing here trusts a client-submitted total —
     * every total is derived from no_of_unit * rate, or summed from children.
     */
    public function syncPeriods(array $input): void
    {
        $slots = $this->plan()->firstOrFail()->periodSlots();
        $manualKeys = ['previous_2nd_year', 'previous_1st_year', 'current_year', 'quarter_1', 'quarter_2', 'quarter_3', 'year_2_total', 'year_3_total'];
        $computed = [];

        foreach ($manualKeys as $key) {
            $slot = $slots[$key];
            $isBreakable = in_array($key, self::BREAKDOWN_KEYS, true);
            $granularity = $isBreakable ? ($input[$key]['granularity'] ?? null) : null;
            $entries = $granularity ? ($input[$key]['entries'] ?? []) : [];

            if ($granularity && count($entries)) {
                $noOfUnit = 0;
                $total = 0;
                $rate = 0;

                foreach ($entries as $e) {
                    $u = (float) ($e['no_of_unit'] ?? 0);
                    $r = (float) ($e['rate'] ?? 0);
                    $noOfUnit += $u;
                    $total += round($u * $r, 2);
                    $rate = $r; // reference value only — real per-row rates live on the entries
                }
            } else {
                $granularity = null;
                $noOfUnit = (float) ($input[$key]['no_of_unit'] ?? 0);
                $rate = (float) ($input[$key]['rate'] ?? 0);
                $total = round($noOfUnit * $rate, 2);
            }

            $period = $this->periods()->updateOrCreate(
                ['period_type' => $slot['period_type'], 'period_label' => $slot['label']],
                [
                    'year_number' => $slot['year_number'],
                    'slot_order' => self::SLOT_ORDER[$key],
                    'breakdown_granularity' => $granularity,
                    'no_of_unit' => $noOfUnit,
                    'rate' => $rate,
                    'total' => $total,
                ]
            );

            // Replace child entries wholesale — simplest way to keep them consistent with the submitted form.
            $period->entries()->delete();
            if ($granularity) {
                $labels = $granularity === 'month' ? self::MONTH_NAMES : ['Quarter-1', 'Quarter-2', 'Quarter-3', 'Quarter-4'];
                foreach ($entries as $i => $e) {
                    $u = (float) ($e['no_of_unit'] ?? 0);
                    $r = (float) ($e['rate'] ?? 0);
                    $period->entries()->create([
                        'granularity' => $granularity,
                        'slot_number' => $i + 1,
                        'entry_label' => $labels[$i] ?? ('#' . ($i + 1)),
                        'no_of_unit' => $u,
                        'rate' => $r,
                        'total' => round($u * $r, 2),
                    ]);
                }
            }

            $computed[$key] = ['no_of_unit' => $noOfUnit, 'rate' => $rate, 'total' => $total];
        }

        // Year-1 Total = sum of its 3 quarters (never manually entered).
        $y1NoOfUnit = $computed['quarter_1']['no_of_unit'] + $computed['quarter_2']['no_of_unit'] + $computed['quarter_3']['no_of_unit'];
        $y1Total = $computed['quarter_1']['total'] + $computed['quarter_2']['total'] + $computed['quarter_3']['total'];
        $y1Rate = $computed['quarter_1']['rate'];

        $y1Slot = $slots['year_1_total'];
        $this->periods()->updateOrCreate(
            ['period_type' => $y1Slot['period_type'], 'period_label' => $y1Slot['label']],
            ['year_number' => $y1Slot['year_number'], 'slot_order' => self::SLOT_ORDER['year_1_total'], 'no_of_unit' => $y1NoOfUnit, 'rate' => $y1Rate, 'total' => $y1Total]
        );

        // Grand Total = Previous 2 years + Year-1/2/3 totals (never manually entered).
        $grandNoOfUnit = $computed['previous_2nd_year']['no_of_unit'] + $computed['previous_1st_year']['no_of_unit']
            + $y1NoOfUnit + $computed['year_2_total']['no_of_unit'] + $computed['year_3_total']['no_of_unit'];
        $grandTotal = $computed['previous_2nd_year']['total'] + $computed['previous_1st_year']['total']
            + $y1Total + $computed['year_2_total']['total'] + $computed['year_3_total']['total'];

        $gSlot = $slots['grand_total'];
        $this->periods()->updateOrCreate(
            ['period_type' => $gSlot['period_type'], 'period_label' => $gSlot['label']],
            ['year_number' => null, 'slot_order' => self::SLOT_ORDER['grand_total'], 'no_of_unit' => $grandNoOfUnit, 'rate' => $y1Rate, 'total' => $grandTotal]
        );

        // Keep estimated_cost consistent with the matrix's own grand total.
        $this->estimated_cost = $grandTotal;
        $this->save();
    }

    /**
     * Days late on the most recent milestone that's due — null if nothing's due yet,
     * 0 if on time/early, positive integer if delayed.
     */
    public function getDelayDaysAttribute(): ?int
    {
        $milestones = [
            ['planned' => $this->planned_invitation_date, 'actual' => $this->actual_invitation_date],
            ['planned' => $this->planned_evaluation_date, 'actual' => $this->actual_evaluation_date],
            ['planned' => $this->planned_award_date, 'actual' => $this->actual_award_date],
            ['planned' => $this->planned_delivery_date, 'actual' => $this->actual_delivery_date],
        ];

        foreach (array_reverse($milestones) as $m) {
            if (! $m['planned']) {
                continue;
            }

            $reference = $m['actual'] ?? now();

            if ($reference->greaterThan($m['planned'])) {
                return (int) $m['planned']->diffInDays($reference);
            }

            return 0;
        }

        return null;
    }
}