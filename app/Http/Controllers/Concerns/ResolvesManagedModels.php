<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Subtask;
use App\Models\SystemRecord;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ResolvesManagedModels
{
    protected function resolveOwnedModel(string $type, string|int $id): Model
    {
        return match ($type) {
            'task' => Task::query()->findOrFail($id),
            'subtask' => Subtask::query()->findOrFail($id),
            'system' => SystemRecord::query()->findOrFail($id),
            default => abort(404),
        };
    }

    protected function resourceCollection(Model $model, string $resource): MorphMany
    {
        return match ($resource) {
            'comments' => $model->comments(),
            'links' => $model->links(),
            'attachments' => $model->attachments(),
            default => abort(404),
        };
    }
}
