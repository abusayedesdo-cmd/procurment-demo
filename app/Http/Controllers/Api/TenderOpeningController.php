<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderOpening;
use Illuminate\Http\Request;

class TenderOpeningController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderOpening::query();
        $query->with(['rfq', 'openedBy']);

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
        }

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

    public function show(TenderOpening $tenderOpening)
    {
        $tenderOpening->load([
            'rfq', 'openedBy', 'committeeMembers',
            'rfq.quotations.vendor',
        ]);

        return response()->json([
            'success' => true,
            'data' => $tenderOpening,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'opening_date' => 'required|date',
            'venue' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'opened_by' => 'required|exists:users,id',
            'report_file' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        $tenderOpening = TenderOpening::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderOpening created successfully',
            'data' => $tenderOpening,
        ], 201);
    }

    public function update(Request $request, TenderOpening $tenderOpening)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'opening_date' => 'sometimes|required|date',
            'venue' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'opened_by' => 'sometimes|required|exists:users,id',
            'report_file' => 'nullable|string|max:255',
            'remarks' => 'nullable|string'
        ]);

        $tenderOpening->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderOpening updated successfully',
            'data' => $tenderOpening,
        ]);
    }

    public function destroy(TenderOpening $tenderOpening)
    {
        $tenderOpening->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderOpening deleted successfully',
        ]);
    }
}
