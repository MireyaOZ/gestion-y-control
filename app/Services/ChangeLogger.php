<?php

namespace App\Services;

use App\Models\ChangeLog;
use App\Models\Subtask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ChangeLogger
{
    public static function log(Model $model, string $action, string $content): ChangeLog
    {
        $log = $model->changeLogs()->create([
            'action' => $action,
            'content' => $content,
            'author_id' => Auth::id(),
        ]);

        if ($model instanceof Subtask) {
            self::mirrorSubtaskLogToTask($model, $action, $content);
        }

        return $log;
    }

    protected static function mirrorSubtaskLogToTask(Subtask $subtask, string $action, string $content): void
    {
        $task = $subtask->task()->first();

        if (! $task) {
            return;
        }

        $task->changeLogs()->create([
            'action' => $action === 'created' ? 'subtask_added' : $action,
            'content' => self::prependSubtaskContext($subtask, $content),
            'author_id' => Auth::id(),
        ]);
    }

    protected static function prependSubtaskContext(Subtask $subtask, string $content): string
    {
        $context = '<p><strong>Subtarea:</strong> '.e($subtask->title).'</p>';

        if (preg_match('/^\s*<p>.*?<\/p>/s', $content, $matches) === 1) {
            return preg_replace('/^\s*<p>.*?<\/p>/s', $matches[0].$context, $content, 1) ?? ($context.$content);
        }

        return $context.$content;
    }
}
