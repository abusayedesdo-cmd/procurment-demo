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
 *  - Admin and Procurement Officer roles -> unscoped, they work across
 *    all projects by design.
 *  - A user with no project_id of their own (e.g. a cross-project
 *    account that isn't Admin/Procurement Officer) -> sees nothing
 *    rather than everything, so a mis-set account fails closed.
 */
class ProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (in_array($user->roleName(), [User::ADMIN, User::PROCUREMENT_OFFICER], true)) {
            return;
        }

        $builder->where($model->qualifyColumn('project_id'), $user->project_id);
    }
}
