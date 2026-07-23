<?php

namespace App\Http\Controllers;

use App\Models\ProcurementCommitteeMember;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function index()
    {
        return view('settings.committee', ['members' => ProcurementCommitteeMember::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'designation' => 'required|string|max:120',
            'role' => 'required|in:convener,member,member_secretary',
        ]);

        $data['sort_order'] = ProcurementCommitteeMember::max('sort_order') + 1;
        $data['active'] = true;
        ProcurementCommitteeMember::create($data);

        return back()->with('ok', 'Committee member added.');
    }

    public function toggle(ProcurementCommitteeMember $member)
    {
        $member->update(['active' => ! $member->active]);
        return back()->with('ok', $member->active ? 'Member reactivated.' : 'Member deactivated.');
    }
}
