<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderOpeningCommittee;
use Illuminate\Http\Request;

class TenderOpeningCommitteeController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderOpeningCommittee::query();

        if ($request->filled('tender_opening_id')) {
            $query->where('tender_opening_id', $request->integer('tender_opening_id'));
        }

        $items = $query->orderBy('tender_opening_id')->orderBy('serial_no')
            ->paginate($request->integer('per_page', 20));

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

    public function show(TenderOpeningCommittee $tenderOpeningCommittee)
    {
        $tenderOpeningCommittee->load(['tenderOpening']);

        return response()->json([
            'success' => true,
            'data' => $tenderOpeningCommittee,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tender_opening_id' => 'required|exists:tender_openings,id',
            'serial_no' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'signed' => 'sometimes|boolean',
        ]);

        $tenderOpeningCommittee = TenderOpeningCommittee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderOpeningCommittee created successfully',
            'data' => $tenderOpeningCommittee,
        ], 201);
    }

    public function update(Request $request, TenderOpeningCommittee $tenderOpeningCommittee)
    {
        $validated = $request->validate([
            'serial_no' => 'sometimes|required|integer|min:1',
            'name' => 'sometimes|required|string|max:255',
            'designation' => 'sometimes|required|string|max:255',
            'signed' => 'sometimes|boolean',
        ]);

        $tenderOpeningCommittee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderOpeningCommittee updated successfully',
            'data' => $tenderOpeningCommittee,
        ]);
    }

    public function destroy(TenderOpeningCommittee $tenderOpeningCommittee)
    {
        $tenderOpeningCommittee->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderOpeningCommittee deleted successfully',
        ]);
    }
}
