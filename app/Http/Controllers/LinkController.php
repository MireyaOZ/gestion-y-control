<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesManagedModels;
use App\Models\ResourceLink;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    use ResolvesManagedModels;

    public function store(Request $request, string $type, string $id): RedirectResponse
    {
        $model = $this->resolveOwnedModel($type, $id);
        $this->authorizeAction($model);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $model->links()->create($data + ['created_by' => $request->user()->id]);

        return back()->with('status', 'Link agregado.');
    }

    public function destroy(ResourceLink $link): RedirectResponse
    {
        $this->authorizeAction($link->linkable);
        $link->delete();

        return back()->with('status', 'Link eliminado.');
    }

    protected function authorizeAction(Task|Subtask $model): void
    {
        if ($model instanceof Task) {
            $this->authorize('update', $model);
        } else {
            $this->authorize('update', $model);
        }
    }
}
