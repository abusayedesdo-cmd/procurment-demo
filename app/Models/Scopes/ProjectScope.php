<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Confines every query on a project-scoped model to the logged-in user's
 * project_id. Applied automatically to any model using the
 * App\Models\Concerns\BelongsToProject trait.
 *
 * Exemptions (see full project scope roll-out):
 *  - No authenticated user (console/queue/seeders) -> unscoped.
 *  - Admin, Procurement Officer, Focal Person, and Executive Director
 *    roles -> unscoped. Admin/Procurement Officer work across all
 *    projects by design; Focal Person/ED sign off on PRs org-wide rather
 *    than being tied to one project.
 *  - A user with no project_id of their own (e.g. a cross-project
 *    account that isn't one of the roles above) -> sees nothing rather
 *    than everything, so a mis-set account fails closed.
 */
class ProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (in_array($user->roleName(), [
            User::ADMIN,
            User::PROCUREMENT_OFFICER,
            User::FOCAL_PERSON,
            User::EXECUTIVE_DIRECTOR,
        ], true)) {
            return;
        }

        $builder->where($model->qualifyColumn('project_id'), $user->project_id);
    }
}