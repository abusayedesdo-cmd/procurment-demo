<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComparativeStatement;
use App\Models\Rfq;
use App\Services\EvaluationVendorSeeder;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;

class ComparativeStatementController extends Controller
{
    public function index(Request $request)
    {
        $query = ComparativeStatement::query();
        $query->with(['rfq.procurementCase', 'preparedBy', 'lowestEvaluatedVendor', 'items']);

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));

        $user = $request->user();
        $items->setCollection(
            $items->getCollection()
                ->filter(fn ($cs) => CommitteeScope::userCanActOnCase($user, $cs->rfq?->procurementCase))
                ->values()
        );

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

    public function show(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->load(['rfq.procurementCase', 'preparedBy', 'lowestEvaluatedVendor', 'items']);

        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $comparativeStatement->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        return response()->json([
            'success' => true,
            'data' => $comparativeStatement,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'prepared_by' => 'required|exists:users,id',
            'lowest_evaluated_vendor_id' => 'nullable|exists:vendors,id',
            'file_path' => 'nullable|string|max:255'
        ]);

        $rfq = Rfq::with('procurementCase')->findOrFail($validated['rfq_id']);
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $rfq->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $comparativeStatement = ComparativeStatement::create($validated);

        // Vendor rows used to be typed in one by one, so a new statement printed "[No comparative items recorded yet]".
        $seeded = EvaluationVendorSeeder::seedComparative($comparativeStatement);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "Comparative Statement created with {$seeded} vendor(s), marks and ranking loaded from the evaluations"
                : 'Comparative Statement created — no vendors to load yet (Financial Evaluation is empty); use "Load Vendors" once it is filled',
            'data' => $comparativeStatement->load('items'),
        ], 201);
    }

    /**
     * POST comparative-statements/{id}/sync-vendors — adds the vendors that are on the Financial
     * Evaluation but not on this statement yet, then recalculates total marks, ranks and the winner.
     */
    public function syncVendors(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->loadMissing('rfq.procurementCase');
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $comparativeStatement->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $seeded = EvaluationVendorSeeder::seedComparative($comparativeStatement);

        return response()->json([
            'success' => true,
            'message' => $seeded > 0
                ? "{$seeded} vendor(s) loaded and ranked"
                : 'No new vendors to add — every vendor of the Financial Evaluation is already on this statement',
            'data' => $comparativeStatement->load('items'),
        ]);
    }

    public function update(Request $request, ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->loadMissing('rfq.procurementCase');
        abort_unless(
            CommitteeScope::userCanActOnCase($request->user(), $comparativeStatement->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'prepared_by' => 'sometimes|required|exists:users,id',
            'lowest_evaluated_vendor_id' => 'nullable|exists:vendors,id',
            'file_path' => 'nullable|string|max:255'
        ]);

        $comparativeStatement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatement updated successfully',
            'data' => $comparativeStatement,
        ]);
    }

    public function destroy(ComparativeStatement $comparativeStatement)
    {
        $comparativeStatement->loadMissing('rfq.procurementCase');
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $comparativeStatement->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $comparativeStatement->delete();

        return response()->json([
            'success' => true,
            'message' => 'ComparativeStatement deleted successfully',
        ]);
    }
}
