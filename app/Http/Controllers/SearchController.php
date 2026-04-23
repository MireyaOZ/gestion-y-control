<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        $user = $request->user();

        $canSearchUsers = $user && (
            $user->can('tasks.view')
            || $user->can('subtasks.view')
            ||
            $user->can('tasks.assign')
            || $user->can('tasks.create')
            || $user->can('tasks.update')
            || $user->can('subtasks.assign')
            || $user->can('subtasks.create')
            || $user->can('subtasks.update')
        );

        abort_unless($canSearchUsers, 403);

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
