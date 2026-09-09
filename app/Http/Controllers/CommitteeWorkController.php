<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMember;
use App\Models\SubCommitteeTransfer;
use Illuminate\Http\Request;

class CommitteeWorkController extends Controller
{
    /**
     * "My Committee Work" — every case currently sitting with a committee
     * the logged-in user belongs to. A case can be re-transferred more
     * than once (e.g. Sub-Committee A → Sub-Committee B), so only the
     * LATEST transfer per Procurement Plan decides who currently holds it.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $committeeIds = CommitteeMember::where('user_id', $user->id)->pluck('committee_id');

        $transfers = SubCommitteeTransfer::with([
                'toCommittee',
                'fromCommittee',
                'procurementPlan.purchaseRequisition.procurementCase',
            ])
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get()
            // Ordered newest-first, so the first one seen per plan is the
            // current holder — this is the "latest transfer per case" cut,
            // done in PHP since the table stays small (transfer events are
            // rare, not per-row-of-data volume).
            ->unique('procurement_plan_id')
            ->filter(fn ($t) => $committeeIds->contains($t->to_committee_id))
            ->values();

        return view('committee-work.index', [
            'transfers' => $transfers,
            'myCommittees' => $user->committeeMemberships()->with('committee')->get(),
        ]);
    }
}
