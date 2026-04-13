<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesManagedModels;
use App\Models\Subtask;
use App\Models\Task;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ResolvesManagedModels;

    public function store(Request $request, string $type, string $id): RedirectResponse
    {
        $model = $this->resolveOwnedModel($type, $id);
        abort_unless($model instanceof Task || $model instanceof Subtask, 404);

        if ($model instanceof Task) {
            $this->authorize('comment', $model);
        }

        if ($model instanceof Subtask) {
            $this->authorize('comment', $model);
        }

        $data = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $model->comments()->create([
            'author_id' => $request->user()->id,
            'content' => HtmlSanitizer::clean($data['content']),
        ]);

        return back()->with('status', 'Comentario agregado.');
    }
}
