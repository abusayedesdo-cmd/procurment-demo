<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;

class AdminProjectPageController extends Controller
{
    public function index()
    {
        return view('admin.projects', [
            'roles' => Role::orderBy('id')->get(['id', 'name']),
            'roleLabels' => User::ROLE_LABELS,
        ]);
    }
}