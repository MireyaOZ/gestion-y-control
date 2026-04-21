<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasResources;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'task_status_id',
        'priority_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Task $task): void {
            $subtasks = $task->isForceDeleting()
                ? $task->rootSubtasks()->withTrashed()->get()
                : $task->rootSubtasks()->get();

            foreach ($subtasks as $subtask) {
                if ($task->isForceDeleting()) {
                    $subtask->forceDelete();

                    continue;
                }

                $subtask->delete();
            }
        });

        static::restoring(function (Task $task): void {
            foreach ($task->rootSubtasks()->withTrashed()->get() as $subtask) {
                if ($subtask->trashed()) {
                    $subtask->restore();
                }
            }
        });
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->latest();
    }

    public function rootSubtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)
            ->whereNull('parent_subtask_id')
            ->latest();
    }

    public function getAssignmentElapsedAttribute(): ?string
    {
        $assignedAt = $this->assignees->sortByDesc('pivot.assigned_at')->first()?->pivot?->assigned_at;

        if (! $assignedAt instanceof CarbonInterface && ! is_string($assignedAt)) {
            return null;
        }

        return now()->diffForHumans($assignedAt, short: true, parts: 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->lt(today())
            && ! in_array($this->status?->slug, ['completada', 'cancelada', 'rechazado'], true);
    }

    public function getOverdueDaysAttribute(): ?int
    {
        if (! $this->is_overdue) {
            return null;
        }

        return (int) $this->due_date->diffInDays(today());
    }
}
