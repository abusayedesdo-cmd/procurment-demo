<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;

class AdminCommitteePageController extends Controller
{
    public function index()
    {
        return view('admin.committees', [
            'roles' => Role::orderBy('id')->get(['id', 'name']),
            'roleLabels' => User::ROLE_LABELS,
        ]);
    }
}
