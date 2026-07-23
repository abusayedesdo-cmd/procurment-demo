<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Read-only — used to populate "pick a user" dropdowns (meeting
 * attendance, sole-sourcing approver, etc). No create/update/delete here;
 * user accounts are managed separately (seeder / future admin screen).
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('role')->where('is_active', true);

        if ($request->filled('role')) {
            $query->whereHas('role', fn ($q) => $q->where('name', $request->string('role')));
        }

        $items = $query->orderBy('name')->paginate($request->integer('per_page', 50));

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
}
