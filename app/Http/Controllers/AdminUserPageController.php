<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;

class AdminUserPageController extends Controller
{
    public function index()
    {
        return view('admin.users', [
            'roles' => Role::orderBy('id')->get(['id', 'name']),
            'roleLabels' => User::ROLE_LABELS,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }
}
