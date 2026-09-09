<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseCommittee;
use Illuminate\Http\Request;

class PurchaseCommitteeController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseCommittee::query();
        $query->with(['parentCommittee', 'members.user', 'project']);

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

    public function show(PurchaseCommittee $purchaseCommittee)
    {
        $purchaseCommittee->load(['parentCommittee', 'members.user', 'project']);

        return response()->json([
            'success' => true,
            'data' => $purchaseCommittee,
        ]);
    }

    /**
     * Policy §9: sub-committees are formed for remote/specific projects; the
     * central committee is organization-wide. So project_id is required
     * exactly when type=sub, and cleared for type=main regardless of what
     * was submitted (a main/central committee never belongs to one project).
     */
    private function rulesFor(?string $type): array
    {
        $isSub = $type === 'sub';

        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'type' => 'required|in:main,sub',
            'parent_committee_id' => 'nullable|exists:purchase_committees,id',
            'project_id' => ($isSub ? 'required' : 'nullable') . '|exists:projects,id',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rulesFor($request->input('type')));

        if ($validated['type'] === 'main') {
            $validated['project_id'] = null;
        }

        $purchaseCommittee = PurchaseCommittee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'PurchaseCommittee created successfully',
            'data' => $purchaseCommittee,
        ], 201);
    }

    public function update(Request $request, PurchaseCommittee $purchaseCommittee)
    {
        $type = $request->input('type', $purchaseCommittee->type);
        $rules = $this->rulesFor($type);
        $rules['name'] = 'sometimes|required|string|max:255';
        $rules['type'] = 'sometimes|required|in:main,sub';

        $validated = $request->validate($rules);

        if ($type === 'main') {
            $validated['project_id'] = null;
        }

        $purchaseCommittee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'PurchaseCommittee updated successfully',
            'data' => $purchaseCommittee,
        ]);
    }

    public function destroy(PurchaseCommittee $purchaseCommittee)
    {
        $purchaseCommittee->delete();

        return response()->json([
            'success' => true,
            'message' => 'PurchaseCommittee deleted successfully',
        ]);
    }
}
