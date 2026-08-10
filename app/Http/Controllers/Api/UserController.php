<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Two audiences share this controller:
 *
 *  - index()  — read-only, used to populate "pick a user" dropdowns
 *    (meeting attendance, sole-sourcing approver, etc). Left exactly as
 *    it was: active users only, open to any logged-in user. Do not add
 *    write access here.
 *
 *  - adminIndex()/store()/update()/destroy()/resetPassword() — the
 *    actual Super Admin > User Management screen. All routed behind
 *    'role:admin' in routes/api.php.
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

    /**
     * Full list for the admin screen — includes inactive users, and
     * supports search / role / status filtering. Admin-only (see routes).
     */
    public function adminIndex(Request $request)
    {
        $query = User::query()->with(['role', 'project']);

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->integer('role_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(function ($qq) use ($term) {
                $qq->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('designation', 'like', $term);
            });
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
            'stats' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'admins' => User::whereHas('role', fn ($q) => $q->where('name', User::ADMIN))->count(),
                'projects' => \App\Models\Project::where('is_active', true)->count(),
            ],
        ]);
    }

    public function show(User $user)
    {
        $user->load(['role', 'project']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'project_id' => [Rule::requiredIf(fn () => ! $this->roleIsProjectExempt($request->integer('role_id'))), 'nullable', 'exists:projects,id'],
            'phone' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ], [
            'project_id.required' => 'Select the project this user belongs to. Only Admin and Procurement Officer accounts can be created without one.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);
        $user->load(['role', 'project']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        // The role this user will end up with once this request applies —
        // needed to decide whether project_id is still required afterwards.
        $resultingRoleId = $request->filled('role_id') ? $request->integer('role_id') : $user->role_id;
        $resultingProjectGiven = $request->has('project_id') ? $request->input('project_id') : $user->project_id;

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => 'sometimes|required|exists:roles,id',
            'project_id' => [
                Rule::requiredIf(fn () => ! $this->roleIsProjectExempt($resultingRoleId) && ! $resultingProjectGiven),
                'nullable',
                'exists:projects,id',
            ],
            'phone' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ], [
            'project_id.required' => 'Select the project this user belongs to. Only Admin and Procurement Officer accounts can be without one.',
        ]);

        // Guard 1: nobody can deactivate their own account (locks everyone out
        // of admin tools if it's the only admin logged in).
        if ($request->user()->id === $user->id
            && array_key_exists('is_active', $validated)
            && ! $validated['is_active']) {
            return response()->json([
                'success' => false,
                'message' => "You can't deactivate your own account.",
            ], 422);
        }

        // Guard 2: keep at least one active Admin in the system.
        $losingAdminRole = isset($validated['role_id']) && $user->roleName() === User::ADMIN
            && (int) $validated['role_id'] !== (int) $user->role_id;
        $losingActiveStatus = array_key_exists('is_active', $validated)
            && $user->roleName() === User::ADMIN
            && ! $validated['is_active'];

        if ($losingAdminRole || $losingActiveStatus) {
            $otherActiveAdmins = User::whereHas('role', fn ($q) => $q->where('name', User::ADMIN))
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one active Admin must remain in the system.',
                ], 422);
            }
        }

        $user->update($validated);
        $user->load(['role', 'project']);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => "You can't delete your own account.",
            ], 422);
        }

        if ($user->roleName() === User::ADMIN) {
            $otherAdmins = User::whereHas('role', fn ($q) => $q->where('name', User::ADMIN))
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one Admin must remain in the system.',
                ], 422);
            }
        }

        // This app has no soft-deletes on users, and several tables
        // (raised_by, prepared_by, approved_by, etc.) hold plain foreign
        // keys back to users without cascade rules — so a hard delete on a
        // user with history would blow up on the DB constraint. Safer
        // default: deactivate instead of destroying if they have any
        // trail; only truly unused accounts get removed outright.
        $hasHistory = $user->raisedRequisitions()->exists()
            || $user->prApprovals()->exists()
            || $user->meetingsCreated()->exists()
            || $user->committeeMemberships()->exists();

        if ($hasHistory) {
            $user->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'This user has procurement history, so they were deactivated instead of deleted (to keep past records intact).',
                'data' => $user->fresh(['role', 'project']),
            ]);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * True for roles that work across every project (Admin, Procurement
     * Officer) and so don't need a project_id assigned.
     */
    protected function roleIsProjectExempt(?int $roleId): bool
    {
        if (! $roleId) {
            return false;
        }

        $roleName = \App\Models\Role::find($roleId)?->name;

        return in_array($roleName, [User::ADMIN, User::PROCUREMENT_OFFICER], true);
    }

    /**
     * Admin-set or admin-generated password reset. If 'password' is
     * given, uses that; otherwise generates a random one and returns it
     * in the response (shown once) so the admin can hand it to the user.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'nullable|string|min:8',
        ]);

        $newPassword = $validated['password'] ?? Str::password(12, true, true, false, false);

        $user->update(['password' => Hash::make($newPassword)]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
            'data' => [
                'generated_password' => $newPassword,
            ],
        ]);
    }
}
