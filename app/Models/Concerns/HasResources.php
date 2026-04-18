<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use App\Models\ChangeLog;
use App\Models\Comment;
use App\Models\ResourceLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasResources
{
    public static function bootHasResources(): void
    {
        static::deleting(function (Model $model): void {
            foreach (['attachments', 'links', 'comments', 'changeLogs'] as $relation) {
                $items = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                    ? $model->{$relation}()->withTrashed()->get()
                    : $model->{$relation}()->get();

                foreach ($items as $item) {
                    if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                        $item->forceDelete();

                        continue;
                    }

                    $item->delete();
                }
            }
        });

        static::restoring(function (Model $model): void {
            foreach (['attachments', 'links', 'comments', 'changeLogs'] as $relation) {
                foreach ($model->{$relation}()->withTrashed()->get() as $item) {
                    if ($item->trashed()) {
                        $item->restore();
                    }
                }
            }
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    public function links(): MorphMany
    {
        return $this->morphMany(ResourceLink::class, 'linkable')->latest();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function changeLogs(): MorphMany
    {
        return $this->morphMany(ChangeLog::class, 'loggable')->latest();
    }
}
