<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementAnnualPlan;
use Illuminate\Http\Request;

class ProcurementAnnualPlanController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => ProcurementAnnualPlan::orderByDesc('id')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_type' => 'required|in:annual,project',
            'title' => 'required|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'project_location' => 'nullable|string',
            'working_area' => 'nullable|string',
            'activity_summary' => 'nullable|string',
            'fiscal_year_start' => 'required|date',
            'fiscal_year_end' => 'required|date|after:fiscal_year_start',
            'project_duration' => 'nullable|string|max:255',
            'agreement_date' => 'nullable|date',
            'donor_name' => 'nullable|string|max:255',
            'funding_source' => 'nullable|string|max:255',
        ]);

        $validated['prepared_by'] = $request->user()->id;
        $validated['status'] = 'draft';

        $plan = ProcurementAnnualPlan::create($validated);

        return response()->json(['success' => true, 'data' => $plan], 201);
    }

    public function show(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->load('packages.periods.entries', 'packages.category', 'packages.item');

        return response()->json(['success' => true, 'data' => $procurementAnnualPlan]);
    }

    public function update(Request $request, ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'project_location' => 'nullable|string',
            'working_area' => 'nullable|string',
            'activity_summary' => 'nullable|string',
            'project_duration' => 'nullable|string|max:255',
            'agreement_date' => 'nullable|date',
            'donor_name' => 'nullable|string|max:255',
            'funding_source' => 'nullable|string|max:255',
            'status' => 'sometimes|in:draft,approved',
        ]);
        if (($validated['status'] ?? null) === 'approved') {
            $validated['approved_by'] = $request->user()->id;
        }

        $procurementAnnualPlan->update($validated);

        return response()->json(['success' => true, 'data' => $procurementAnnualPlan]);
    }

    public function destroy(ProcurementAnnualPlan $procurementAnnualPlan)
    {
        $procurementAnnualPlan->delete();

        return response()->json(['success' => true]);
    }
}