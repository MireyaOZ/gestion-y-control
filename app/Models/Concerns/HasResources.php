<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use App\Models\ChangeLog;
use App\Models\Comment;
use App\Models\ResourceLink;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasResources
{
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
