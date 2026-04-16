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

        abort_unless($user?->can('tasks.assign') || $user?->can('subtasks.assign'), 403);

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
