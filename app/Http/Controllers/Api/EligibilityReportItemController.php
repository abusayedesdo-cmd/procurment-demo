<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EligibilityReportItem;
use Illuminate\Http\Request;

class EligibilityReportItemController extends Controller
{
    public function index(Request $request)
    {
        $query = EligibilityReportItem::query();
        $query->with(['report', 'vendor']);

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(EligibilityReportItem $eligibilityReportItem)
    {
        $eligibilityReportItem->load(['report', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => $eligibilityReportItem,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'eligibility_report_id' => 'required|exists:eligibility_reports,id',
            'vendor_id' => 'required|exists:vendors,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'trade_license_verified' => 'boolean',
            'tin_verified' => 'boolean',
            'bin_verified' => 'boolean',
            'psr_verified' => 'boolean',
            'remarks' => 'nullable|string',
        ]);

        // "Eligible" স্টাফ আলাদা করে টাইপ করে না — চারটা ভেরিফিকেশন চেকবক্স
        // সবগুলো টিক থাকলেই স্বয়ংক্রিয়ভাবে Eligible ধরা হয়।
        $validated['eligible'] = ($validated['trade_license_verified'] ?? false)
            && ($validated['tin_verified'] ?? false)
            && ($validated['bin_verified'] ?? false)
            && ($validated['psr_verified'] ?? false);

        $eligibilityReportItem = EligibilityReportItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'EligibilityReportItem created successfully',
            'data' => $eligibilityReportItem,
        ], 201);
    }

    public function update(Request $request, EligibilityReportItem $eligibilityReportItem)
    {
        $validated = $request->validate([
            'eligibility_report_id' => 'required|exists:eligibility_reports,id',
            'vendor_id' => 'required|exists:vendors,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'trade_license_verified' => 'boolean',
            'tin_verified' => 'boolean',
            'bin_verified' => 'boolean',
            'psr_verified' => 'boolean',
            'remarks' => 'nullable|string',
        ]);

        // "Eligible" স্টাফ আলাদা করে টাইপ করে না — চারটা ভেরিফিকেশন চেকবক্স
        // সবগুলো টিক থাকলেই স্বয়ংক্রিয়ভাবে Eligible ধরা হয়।
        $validated['eligible'] = ($validated['trade_license_verified'] ?? false)
            && ($validated['tin_verified'] ?? false)
            && ($validated['bin_verified'] ?? false)
            && ($validated['psr_verified'] ?? false);

        $eligibilityReportItem = EligibilityReportItem::create($validated);

        $eligibilityReportItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'EligibilityReportItem updated successfully',
            'data' => $eligibilityReportItem,
        ]);
    }

    public function destroy(EligibilityReportItem $eligibilityReportItem)
    {
        $eligibilityReportItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'EligibilityReportItem deleted successfully',
        ]);
    }
}
