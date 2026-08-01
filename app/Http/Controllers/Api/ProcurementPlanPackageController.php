<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementAnnualPlan;
use App\Models\ProcurementPlanPackage;
use App\Services\NumberGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementPlanPackageController extends Controller
{
    protected const PERIOD_RULES = [
        'periods.quarter_1.no_of_unit' => 'nullable|numeric',
        'periods.quarter_1.rate' => 'nullable|numeric',
        'periods.quarter_2.no_of_unit' => 'nullable|numeric',
        'periods.quarter_2.rate' => 'nullable|numeric',
        'periods.quarter_3.no_of_unit' => 'nullable|numeric',
        'periods.quarter_3.rate' => 'nullable|numeric',
    ];

    protected const BREAKDOWN_PERIOD_KEYS = ['previous_2nd_year', 'previous_1st_year', 'current_year', 'year_2_total', 'year_3_total'];

    protected function breakdownRules(): array
    {
        $rules = [];
        foreach (self::BREAKDOWN_PERIOD_KEYS as $key) {
            $rules["periods.$key.no_of_unit"] = 'nullable|numeric';
            $rules["periods.$key.rate"] = 'nullable|numeric';
            $rules["periods.$key.granularity"] = 'nullable|in:month,quarter';
            $rules["periods.$key.entries"] = 'nullable|array';
            $rules["periods.$key.entries.*.no_of_unit"] = 'nullable|numeric';
            $rules["periods.$key.entries.*.rate"] = 'nullable|numeric';
        }

        return $rules;
    }

    public function all()
    {
        $packages = ProcurementPlanPackage::with('plan', 'category', 'periods', 'item')->orderByDesc('id')->get()->map(fn ($p) => [
            'id' => $p->id,
            'package_number' => $p->package_number,
            'budgeted_head' => $p->budgeted_head,
            'category' => $p->category->name,
            'plan_title' => $p->plan->title,
            'estimated_cost' => (float) $p->estimated_cost,
            'already_procured' => $p->already_procured,
            'remaining_balance' => $p->remaining_balance,
        ]);

        return response()->json(['success' => true, 'data' => $packages]);
    }

    public function index(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $packages = $procurementAnnualPlan->packages()->with('periods.entries', 'category', 'item')->orderBy('sl_no')->get();

        return response()->json(['success' => true, 'data' => $packages]);
    }

    public function store(Request $request, ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $validated = $request->validate(array_merge([
            'sl_no' => 'nullable|integer',
            'procurement_category_id' => 'required|exists:procurement_categories,id',
            'budgeted_head' => 'required|string|max:255',
            'item_id' => 'nullable|exists:items,id',
            'specification' => 'nullable|string',
            'unit' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'periods' => 'required|array',
        ], self::PERIOD_RULES, $this->breakdownRules()));

        $package = DB::transaction(function () use ($validated, $procurementAnnualPlan) {
            $package = $procurementAnnualPlan->packages()->create([
                'sl_no' => $validated['sl_no'] ?? null,
                'procurement_category_id' => $validated['procurement_category_id'],
                'budgeted_head' => $validated['budgeted_head'],
                'item_id' => $validated['item_id'] ?? null,
                'specification' => $validated['specification'] ?? null,
                'unit' => $validated['unit'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'package_number' => app(NumberGeneratorService::class)->next('package', 'PKG-'),
            ]);

            $package->syncPeriods($validated['periods']);

            return $package;
        });

        $package->load('periods.entries', 'category', 'item');

        return response()->json(['success' => true, 'data' => $package], 201);
    }

    public function update(Request $request, ProcurementPlanPackage $procurementPlanPackage)
    {
        $validated = $request->validate(array_merge([
            'sl_no' => 'nullable|integer',
            'procurement_category_id' => 'sometimes|exists:procurement_categories,id',
            'budgeted_head' => 'sometimes|string|max:255',
            'item_id' => 'nullable|exists:items,id',
            'specification' => 'nullable|string',
            'unit' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'periods' => 'required|array',
        ], self::PERIOD_RULES, $this->breakdownRules()));

        DB::transaction(function () use ($validated, $procurementPlanPackage) {
            $procurementPlanPackage->update(collect($validated)->except('periods')->toArray());
            $procurementPlanPackage->syncPeriods($validated['periods']);
        });

        $procurementPlanPackage->load('periods.entries', 'category', 'item');

        return response()->json(['success' => true, 'data' => $procurementPlanPackage]);
    }

    public function destroy(ProcurementPlanPackage $procurementPlanPackage)
    {
        $procurementPlanPackage->delete();

        return response()->json(['success' => true]);
    }
}