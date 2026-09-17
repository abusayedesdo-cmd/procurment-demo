<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProcurementCommitteeMember;
use App\Models\PurchaseCommittee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * সংকীর্ণ, একক-উদ্দেশ্যের endpoint — যাতে Admin ও Procurement Officer দুজনেই
 * Committee Roster থেকে কাউকে লগইন দিতে পারে, কিন্তু পুরো /api/admin/users
 * (সব ইউজার লিস্ট/এডিট/ডিলিট/পাসওয়ার্ড রিসেট) Procurement Officer-এর জন্য
 * খুলতে না হয়। এখানে তৈরি অ্যাকাউন্ট সবসময় role=procurement_officer —
 * caller অন্য কোনো রোল বেছে নিতে পারবে না।
 */
class CommitteeRosterLoginController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'procurement_committee_member_id' => 'required|exists:procurement_committee_members,id',
            'committee_id' => 'required|exists:purchase_committees,id',
            'email' => 'required|email|unique:users,email',
            'designation' => 'nullable|string|max:255',
        ]);

        $rosterMember = ProcurementCommitteeMember::findOrFail($validated['procurement_committee_member_id']);
        $committee = PurchaseCommittee::findOrFail($validated['committee_id']);

        $roleId = Role::where('name', User::PROCUREMENT_OFFICER)->value('id');
        abort_if(! $roleId, 500, '"Procurement Officer" role not found — check the Role seeder.');

        $password = Str::password(12);

        $user = User::create([
            'name' => $rosterMember->name,
            'email' => $validated['email'],
            'password' => $password, // User::$casts hashes this automatically
            'role_id' => $roleId,
            'project_id' => $committee->project_id,
            'designation' => $validated['designation'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'password' => $password,
            ],
        ], 201);
    }
}
