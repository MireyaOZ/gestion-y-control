<?php

namespace App\Models;

use App\Models\Concerns\HasResources;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
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
}
