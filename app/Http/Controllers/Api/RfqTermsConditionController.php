<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RfqTermsCondition;
use Illuminate\Http\Request;

/**
 * Step 7 "Select Terms & Conditions" — the master list an officer picks
 * from when creating an RFQ (see Rfq::termsConditions()). Index is open to
 * everyone (needed to populate the checkbox list on the RFQ form); writes
 * are gated to Admin/Procurement Officer in routes/api.php, same as the
 * Committee Roster.
 */
class RfqTermsConditionController extends Controller
{
    public function index(Request $request)
    {
        $query = RfqTermsCondition::query();

        if ($request->boolean('active')) {
            $query->where('active', true);
        }

        $items = $query->orderBy('sort_order')->orderBy('id')
            ->paginate($request->integer('per_page', 100));

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $item = RfqTermsCondition::create($validated + ['active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Terms & Conditions item added.',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, RfqTermsCondition $rfqTermsCondition)
    {
        $validated = $request->validate([
            'text' => 'sometimes|required|string',
            'sort_order' => 'sometimes|nullable|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $rfqTermsCondition->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Updated.',
            'data' => $rfqTermsCondition,
        ]);
    }

    public function destroy(RfqTermsCondition $rfqTermsCondition)
    {
        $rfqTermsCondition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted. Note: RFQ documents are generated fresh each time they\'re downloaded, so this line will also disappear from any older RFQ that had selected it — use "Deactivate" instead if you just want to stop it appearing on NEW RFQs.',
        ]);
    }
}
