<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderAdvertisement;
use App\Support\CommitteeScope;
use Illuminate\Http\Request;

class TenderAdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderAdvertisement::query();
        $query->with(['rfq']);
        $query = TenderAdvertisement::query()->with('rfq.procurementCase');

        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->integer('rfq_id'));
        }

        $items = $query->latest('id')->paginate($request->integer('per_page', 20));
        $user = $request->user();
        $items->setCollection(  
            $items->getCollection()
                ->filter(fn ($row) => CommitteeScope::userCanActOnCase($user, $row->rfq?->procurementCase))
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

    public function show(TenderAdvertisement $tenderAdvertisement)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $tenderAdvertisement->rfq?->procurementCase),
            403
        );
        $tenderAdvertisement->load(['rfq']);

        return response()->json([
            'success' => true,
            'data' => $tenderAdvertisement,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'medium' => 'required|in:BD Jobs,National Newspaper,Local Newspaper',
            'category' => 'required|in:Goods,Works,Service',
            'publish_date' => 'required|date',
            'file_path' => 'nullable|string|max:255'
        ]);

        $tenderAdvertisement = TenderAdvertisement::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderAdvertisement created successfully',
            'data' => $tenderAdvertisement,
        ], 201);
    }

    public function update(Request $request, TenderAdvertisement $tenderAdvertisement)
    {
        $validated = $request->validate([
            'rfq_id' => 'sometimes|required|exists:rfqs,id',
            'medium' => 'sometimes|required|in:BD Jobs,National Newspaper,Local Newspaper',
            'category' => 'sometimes|required|in:Goods,Works,Service',
            'publish_date' => 'sometimes|required|date',
            'file_path' => 'nullable|string|max:255'
        ]);

        $tenderAdvertisement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'TenderAdvertisement updated successfully',
            'data' => $tenderAdvertisement,
        ]);
    }

    public function destroy(TenderAdvertisement $tenderAdvertisement)
    {
        abort_unless(
            CommitteeScope::userCanActOnCase(request()->user(), $tenderAdvertisement->rfq?->procurementCase),
            403,
            'This case is currently with a different committee.'
        );

        $tenderAdvertisement->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderAdvertisement deleted successfully',
        ]);
    }
}
