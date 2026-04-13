<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function projects(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->can('projects.view')
            || $request->user()->can('tasks.create')
            || $request->user()->can('tasks.update'),
            403,
        );

        $query = (string) $request->string('query');

        $results = Project::query()
            ->with('status')
            ->whereHas('status', fn ($builder) => $builder->where('slug', 'visto-bueno-de-diagramacion'))
            ->when($query !== '', fn ($builder) => $builder->where('title', 'like', "%{$query}%"))
            ->limit(10)
            ->get()
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'label' => $project->title,
                'meta' => $project->status->name,
            ]);

        return response()->json($results);
    }

    public function users(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->can('tasks.assign') || auth()->user()->can('subtasks.assign'), 403);

        $query = (string) $request->string('query');

        $results = User::query()
            ->where('is_active', true)
            ->when($query !== '', fn ($builder) => $builder->where(function ($nested) use ($query) {
                $nested->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            }))
            ->limit(10)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'label' => $user->name,
                'meta' => $user->email,
            ]);

        return response()->json($results);
    }
}
