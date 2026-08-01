<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenderAdvertisement;
use Illuminate\Http\Request;

class TenderAdvertisementController extends Controller
{
    public function index(Request $request)
    {
        $query = TenderAdvertisement::query();
        $query->with(['rfq']);

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

    public function show(TenderAdvertisement $tenderAdvertisement)
    {
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
            'medium' => 'required|in:Newspaper,bdjobs',
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
            'medium' => 'sometimes|required|in:Newspaper,bdjobs',
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
        $tenderAdvertisement->delete();

        return response()->json([
            'success' => true,
            'message' => 'TenderAdvertisement deleted successfully',
        ]);
    }
}
