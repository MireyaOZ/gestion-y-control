<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesManagedModels;
use App\Models\ResourceLink;
use App\Models\Subtask;
use App\Models\Task;
use App\Services\ChangeLogger;
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
            'url' => ['required', 'url', 'max:5000'],
        ]);

        $link = $model->links()->create($data + ['created_by' => $request->user()->id]);

        ChangeLogger::log(
            $model,
            'link_added',
            '<p>Link agregado por '.e($request->user()->name).'.</p>'
            .'<p><strong>Etiqueta:</strong> '.e($link->label).'</p>'
            .'<p><strong>URL:</strong> <a href="'.e($link->url).'" target="_blank" style="color:#960018;text-decoration:underline;">'.e($link->url).'</a></p>'
        );

        return back()->with('status', 'Link agregado.');
    }

    public function destroy(ResourceLink $link): RedirectResponse
    {
        $this->authorizeAction($link->linkable);

        $loggable = $link->linkable;

        ChangeLogger::log(
            $loggable,
            'link_deleted',
            '<p>Link eliminado por '.e(request()->user()?->name ?? 'Sistema').'.</p>'
            .'<p><strong>Etiqueta:</strong> '.e($link->label).'</p>'
            .'<p><strong>URL:</strong> '.e($link->url).'</p>'
        );

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
