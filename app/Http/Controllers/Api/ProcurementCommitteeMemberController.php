<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementCommitteeMember;
use Illuminate\Http\Request;

/**
 * Read-only lookup of the procurement_committee_members roster — used by
 * the Committees admin page to let an Admin pick a Sub-Committee member
 * straight from the roster (real name/designation, no login account)
 * instead of only from registered Users. Managing the roster itself is
 * still done from Settings > Committee Roster (CommitteeController).
 */
class ProcurementCommitteeMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcurementCommitteeMember::query()->where('active', true);

        if ($request->filled('committee_type')) {
            $query->where('committee_type', $request->string('committee_type'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('sort_order')->get(),
        ]);
    }
}
