<?php

namespace App\Models\Concerns;

use App\Models\Project;
use App\Models\Scopes\ProjectScope;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Full project enforcement for a model:
 *  - every query (index/show/update/delete, including implicit route
 *    model binding) is filtered to the logged-in user's project_id,
 *    unless they're Admin/Procurement Officer (see ProjectScope).
 *  - on create, project_id is auto-stamped from the logged-in user
 *    unless the caller explicitly set it (e.g. an Admin choosing a
 *    project on behalf of someone else).
 *
 * Add `protected $fillable = [..., 'project_id']` (or guard it out and
 * rely purely on the auto-stamp) on any model using this trait.
 */
trait BelongsToProject
{
    public static function bootBelongsToProject(): void
    {
        static::addGlobalScope(new ProjectScope);

        static::creating(function ($model) {
            if ($model->project_id) {
                return;
            }

            $user = Auth::user();

            if ($user && $user->project_id) {
                $model->project_id = $user->project_id;
            }
        });
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
