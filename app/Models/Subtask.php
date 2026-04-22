<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subtask extends Model
{
    use HasResources;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'task_id',
        'parent_subtask_id',
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
        static::deleting(function (Subtask $subtask): void {
            $children = $subtask->isForceDeleting()
                ? $subtask->childSubtasks()->withTrashed()->get()
                : $subtask->childSubtasks()->get();

            foreach ($children as $child) {
                if ($subtask->isForceDeleting()) {
                    $child->forceDelete();

                    continue;
                }

                $child->delete();
            }
        });

        static::restoring(function (Subtask $subtask): void {
            foreach ($subtask->childSubtasks()->withTrashed()->get() as $child) {
                if ($child->trashed()) {
                    $child->restore();
                }
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function parentSubtask(): BelongsTo
    {
        return $this->belongsTo(Subtask::class, 'parent_subtask_id');
    }

    public function childSubtasks(): HasMany
    {
        return $this->hasMany(Subtask::class, 'parent_subtask_id')->latest();
    }

    public function childSubtasksRecursive(): HasMany
    {
        return $this->childSubtasks()->with([
            'status',
            'priority',
            'assignees',
            'childSubtasksRecursive',
        ]);
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

    public function getAssignmentElapsedAttribute(): ?string
    {
        $assignedAt = $this->assignees->sortByDesc('pivot.assigned_at')->first()?->pivot?->assigned_at;

        if (! $assignedAt instanceof CarbonInterface && ! is_string($assignedAt)) {
            return null;
        }

        return now()->diffForHumans($assignedAt, short: true, parts: 2);
    }

    public function isDescendantOf(Subtask $candidateParent): bool
    {
        $ancestor = $this->parentSubtask;

        while ($ancestor !== null) {
            if ($ancestor->is($candidateParent)) {
                return true;
            }

            $ancestor = $ancestor->parentSubtask;
        }

        return false;
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

    public function ancestry(): Collection
    {
        $ancestors = collect();
        $ancestor = $this->parentSubtask()->first();

        while ($ancestor !== null) {
            $ancestors->prepend($ancestor);
            $ancestor = $ancestor->parentSubtask()->first();
        }

        return $ancestors;
    }
}
