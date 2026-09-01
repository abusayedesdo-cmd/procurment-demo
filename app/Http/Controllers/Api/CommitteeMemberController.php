<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\PurchaseCommittee;
use Illuminate\Http\Request;

class CommitteeMemberController extends Controller
{
    // ESDO Procurement Policy §9 "Procurement Management System of ESDO":
    // central procurement committee = 5 to 7 members; sub-committees = 3
    // to 5 members.
    public const CENTRAL_MIN = 5;
    public const CENTRAL_MAX = 7;
    public const SUB_MIN = 3;
    public const SUB_MAX = 5;

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

        $this->assertWithinMaxSize($validated['committee_id']);

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

        // Only re-check the ceiling if this member is moving to a
        // different committee — moving within the same committee (just
        // changing designation) never changes headcount.
        if (isset($validated['committee_id']) && $validated['committee_id'] != $committeeMember->committee_id) {
            $this->assertWithinMaxSize($validated['committee_id']);
        }

        $committeeMember->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'CommitteeMember updated successfully',
            'data' => $committeeMember,
        ]);
    }

    public function destroy(CommitteeMember $committeeMember)
    {
        $this->assertAboveMinSize($committeeMember->committee_id, excludingMemberId: $committeeMember->id);

        $committeeMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'CommitteeMember deleted successfully',
        ]);
    }

    /** @return array{0:int,1:int} [min, max] for this committee's type. */
    private function sizeRange(PurchaseCommittee $committee): array
    {
        return $committee->type === 'sub'
            ? [self::SUB_MIN, self::SUB_MAX]
            : [self::CENTRAL_MIN, self::CENTRAL_MAX];
    }

    private function assertWithinMaxSize(int $committeeId): void
    {
        $committee = PurchaseCommittee::findOrFail($committeeId);
        [$min, $max] = $this->sizeRange($committee);

        $current = CommitteeMember::where('committee_id', $committeeId)->count();

        abort_if($current >= $max, 422,
            "Per ESDO Procurement Policy §9, a "
            . ($committee->type === 'sub' ? 'sub-committee' : 'central procurement committee')
            . " can have at most {$max} members. \"{$committee->name}\" already has {$current}."
        );
    }

    private function assertAboveMinSize(int $committeeId, int $excludingMemberId): void
    {
        $committee = PurchaseCommittee::findOrFail($committeeId);
        [$min, $max] = $this->sizeRange($committee);

        $remaining = CommitteeMember::where('committee_id', $committeeId)
            ->where('id', '!=', $excludingMemberId)
            ->count();

        abort_if($remaining < $min, 422,
            "Per ESDO Procurement Policy §9, a "
            . ($committee->type === 'sub' ? 'sub-committee' : 'central procurement committee')
            . " must keep at least {$min} members. Removing this member would leave only {$remaining} on \"{$committee->name}\"."
        );
    }
}