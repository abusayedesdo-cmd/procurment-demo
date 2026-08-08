# Super Admin — User Management

Adds a full CRUD user-management screen for Admins: create users, assign
role, edit details, activate/deactivate, reset password, delete.

## No database changes needed
`users.role_id`, `phone`, `designation`, `is_active` already exist in your
schema — this is pure application code, nothing to run in phpMyAdmin.

## Files — 2 new, 4 modified

**New**
- `app/Http/Controllers/AdminUserPageController.php`
- `resources/views/admin/users.blade.php`
- `public/js/admin-users.js`

**Modified**
- `app/Http/Controllers/Api/UserController.php` — replaces the whole file.
  `index()` (used for dropdowns elsewhere) is untouched; added
  `adminIndex()`, `show()`, `store()`, `update()`, `destroy()`,
  `resetPassword()`.
- `routes/api.php` — added one new block only
  (`Route::middleware('role:admin')->prefix('admin')...`), right after the
  existing `roles` line. Nothing else changed.
- `routes/web.php` — added `use AdminUserPageController` import and one
  new route: `GET /admin/users`.
- `resources/views/layouts/app.blade.php` — added one nav link,
  "User Management", visible only when `roleName() === ADMIN`.

## Deploy steps (cPanel, no SSH)
1. Upload the 3 new files to their paths.
2. Overwrite the 4 modified files at their paths (back them up first if
   you want a rollback path — the diffs are small, see below).
3. Run `php artisan optimize:clear` (route/view/config cache) the same
   way you do for other deploys.
4. Log in as `admin@esdo.net.bd` and open **User Management** in the top
   nav.

## What it does
- **List** — search by name/email/phone/designation, filter by role and
  status, stat cards for total/active/inactive/admin counts.
- **Create** — name, email, role (from your existing 6 roles:
  Requester / Reviewer / Budget Checker / Approver / Procurement Officer
  / Admin), phone, designation, password, active toggle.
- **Edit** — same fields except password (use "Reset PW" for that,
  keeps the update endpoint's validation simple and avoids accidentally
  wiping a password from a stray form value).
- **Reset PW** — set a specific password or auto-generate a random one;
  the generated password is shown once in an alert so you can hand it to
  the user (not stored anywhere in plaintext).
- **Activate/Deactivate** — soft toggle, doesn't delete data.
- **Delete** — if the user has procurement history (raised a PR, approved
  something, ran a meeting, etc.) they're deactivated instead of hard
  deleted, since several tables FK back to `users` without cascade rules
  and a hard delete would either fail or orphan records. Users with no
  history are deleted outright.

## Safety guards baked in
- You can't deactivate or delete your own account.
- The system always keeps at least one active Admin — the last admin
  can't be demoted, deactivated, or deleted.

## Note on roles
`roles` is effectively a fixed set of 6 (see `User::ROLE_LABELS`) — the
`role:` route middleware and `roleName()` checks throughout the app key
off those exact string values (`admin`, `procurement_officer`, etc). The
`RoleController` API lets you create a *new* row with a different name,
but it won't be wired into any permission checks until you also touch
`EnsureRole` usages and `User::ROLE_LABELS` in code — so I didn't expose
"create role" in this screen, only "assign one of the existing roles."
If you do want free-form roles later, that's a bigger change (moving
from hardcoded role strings to a proper permissions table) — happy to
scope that separately if useful.
