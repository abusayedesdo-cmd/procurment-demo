<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;

class CommitteeMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = CommitteeMember::query();
        $query->with(['committee', 'user']);

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

    public function show(CommitteeMember $committeeMember)
    {
        $committeeMember->load(['committee', 'user']);

        return response()->json([
            'success' => true,
            'data' => $committeeMember,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'committee_id' => 'required|exists:purchase_committees,id',
            'user_id' => 'required|exists:users,id',
            'designation_in_committee' => 'nullable|string|max:255'
        ]);

        $committeeMember = CommitteeMember::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'CommitteeMember created successfully',
            'data' => $committeeMember,
        ], 201);
    }

    public function update(Request $request, CommitteeMember $committeeMember)
    {
        $validated = $request->validate([
            'committee_id' => 'sometimes|required|exists:purchase_committees,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'designation_in_committee' => 'nullable|string|max:255'
        ]);

        $committeeMember->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'CommitteeMember updated successfully',
            'data' => $committeeMember,
        ]);
    }

    public function destroy(CommitteeMember $committeeMember)
    {
        $committeeMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'CommitteeMember deleted successfully',
        ]);
    }
}
