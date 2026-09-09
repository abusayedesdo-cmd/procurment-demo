<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\PurchaseCommittee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()->withCount('users')->with(['committees' => function ($q) {
            $q->where('type', 'sub');
        }]);

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        $items = $query->orderBy('name')->paginate($request->integer('per_page', 100));

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

    public function show(Project $project)
    {
        $project->loadCount('users')->load(['users:id,name,email,project_id', 'committees' => function ($q) {
            $q->where('type', 'sub');
        }]);

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * A new Project always gets one empty Sub-Committee, per the Admin's
     * own workflow: name the project, and a sub-committee for it exists
     * immediately — members are added afterwards, hand-picked from the
     * users assigned to this project (see PurchaseCommitteeController /
     * CommitteeMemberController), not auto-filled here.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255|unique:projects,code',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $project = DB::transaction(function () use ($validated) {
            $project = Project::create($validated);

            PurchaseCommittee::create([
                'name' => $project->name . ' — Sub-Committee',
                'type' => 'sub',
                'project_id' => $project->id,
            ]);

            return $project;
        });

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully, with an empty Sub-Committee ready for members.',
            'data' => $project,
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:255|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $project->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => $project,
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }
}