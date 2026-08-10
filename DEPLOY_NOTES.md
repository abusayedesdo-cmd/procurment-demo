# Super Admin — User Management, Data Manager & Projects

Three features, delivered together since they build on each other:

1. **User Management** (`/admin/users`) — create/edit/deactivate/delete
   users, assign role, reset password.
2. **Data Manager** (`/admin/database`) — generic, schema-driven CRUD
   over every table in the database (68 tables). Reads live column/FK
   metadata, so it works for any table with no per-table code.
3. **Projects** — a new `projects` table; every user can optionally sit
   under one project alongside their role.

## Database changes — required this time

Unlike the first User Management drop, this one **does** need a DB
change for Projects. Run `database/projects_feature.sql` once in
phpMyAdmin's SQL tab (full statements included, safe to read before
running). It:
- Creates `projects` (`name`, `code`, `description`, `is_active`)
- Adds `users.project_id` (nullable FK → `projects.id`, `ON DELETE SET NULL`)
- Inserts one starter row ("General / Unassigned") so the dropdown isn't
  empty on first load

Matching Laravel migrations are included too
(`database/migrations/2026_08_06_*.php`) for your repo's history / any
future local `artisan migrate` — but the SQL file is what actually needs
to run on production.

## Files — 8 new, 7 modified

**New**
- `app/Http/Controllers/Api/Admin/DatabaseTableController.php` — generic CRUD engine
- `app/Http/Controllers/AdminDatabasePageController.php`
- `app/Models/Project.php`
- `resources/views/admin/database.blade.php`
- `public/js/admin-database.js`
- `database/projects_feature.sql`
- `database/migrations/2026_08_06_000001_create_projects_table.php`
- `database/migrations/2026_08_06_000002_add_project_id_to_users_table.php`

**Modified**
- `app/Models/User.php` — added `project_id` to `$fillable`, added `project()` relation
- `app/Http/Controllers/Api/UserController.php` — project filter/eager-load/validation added to `adminIndex`, `show`, `store`, `update`
- `app/Http/Controllers/AdminUserPageController.php` — now also passes `$projects` to the view
- `resources/views/admin/users.blade.php` — Project filter, Project column, Project field in the form, "Manage Projects" shortcut button, Active Projects stat card
- `public/js/admin-users.js` — same, wired up client-side
- `resources/views/layouts/app.blade.php` — added "Data Manager" nav link
- `routes/api.php` — added `admin/tables/*` routes
- `routes/web.php` — added `GET /admin/database`

## Deploy steps
1. Run `database/projects_feature.sql` in phpMyAdmin.
2. Upload the new files, overwrite the modified ones (all paths mirror
   your Laravel app root — copy each file to the same relative path).
3. `php artisan optimize:clear`.
4. Log in as Admin → **User Management** now has a Project column/filter
   and a "Manage Projects" button; **Data Manager** in the nav lets you
   create/edit/delete Projects (and anything else) directly.

## How "define a project" works day to day
- Super Admin opens **Data Manager → projects** (the "Manage Projects"
  button on the Users page deep-links straight there), clicks **+ New
  Row**, fills in Name (required, unique), Code, Description, Active —
  done. No code changes needed for this or any future table.
- Back on **User Management**, every create/edit form has a **Project**
  dropdown next to Role. It's optional — an Admin can be left
  unassigned if they're cross-project — but Requesters/Reviewers/etc.
  can now be scoped to a project.
- **Note on scope:** right now `project_id` marks *which project a user
  belongs to* — it doesn't yet restrict what that user can see or do
  (e.g. a Requester under "Project A" can still view Project B's
  purchase requisitions, since PRs/vendors/etc. don't carry a
  `project_id` themselves yet). If you want actual data-scoping by
  project — a Requester only seeing their own project's PRs — that's a
  bigger follow-up (adding `project_id` to `purchase_requisitions` and
  filtering every relevant query), similar in shape to the geography
  scoping already built into ESDO SISTE. Happy to scope that separately
  once you've used this for a bit and confirmed it's the direction you
  want.

## Data Manager — safety notes
- **Hidden tables** (never reachable, even to Admin): `migrations`,
  `password_reset_tokens`, `personal_access_tokens`, `sessions`,
  `failed_jobs`, `cache`, `cache_locks`, `jobs`, `job_batches`, and
  `users` (has its own screen so password hashing / "keep one admin"
  guards aren't bypassed).
- Deletes that violate a foreign-key constraint (e.g. deleting a Vendor
  that already has Quotations) return a clear error instead of a raw
  SQL failure — the row stays intact.
- Every write still goes through Laravel's query builder with
  parameter binding — no raw string concatenation of values — and the
  table/column names themselves are checked against
  `information_schema` before use, so arbitrary table names can't be
  injected via the URL.
- This tool bypasses your normal application workflow rules (PR
  approval chain, budget checks, etc.) since it edits tables directly —
  treat it like phpMyAdmin-in-the-app, not a replacement for the normal
  screens.

## Recap: everything from the first drop still applies
- `role:admin` middleware guards every route in this feature set.
- You can't deactivate or delete your own account; the system always
  keeps at least one active Admin.
- `roles` is still a fixed set of 6 tied to string constants
  (`User::ROLE_LABELS`) — this drop doesn't change that. Projects are
  a separate, free-form concept layered alongside role, not a
  replacement for it.
