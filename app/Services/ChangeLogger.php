<?php

namespace App\Services;

use App\Models\ChangeLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChangeLogger
{
    public static function log(Model $model, string $action, string $content): ChangeLog
    {
        return $model->changeLogs()->create([
            'action' => $action,
            'content' => $content,
            'author_id' => Auth::id(),
        ]);
    }
}
