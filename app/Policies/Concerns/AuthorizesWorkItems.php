<?php

namespace App\Policies\Concerns;

use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;

trait AuthorizesWorkItems
{
    protected function isAdmin(User $user): bool
    {
        return $user->can('admin.access') || $user->hasRole('administrador');
    }

    protected function canAccessProject(User $user, Project $project): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($project->created_by === $user->id) {
            return true;
        }

        return $project->tasks()
            ->whereHas('assignees', fn ($query) => $query->whereKey($user->id))
            ->exists();
    }

    protected function canAccessTask(User $user, Task $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        if ($task->project && $task->project->created_by === $user->id) {
            return true;
        }

        return $task->assignees->contains('id', $user->id);
    }

    protected function canAccessSubtask(User $user, Subtask $subtask): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($subtask->created_by === $user->id) {
            return true;
        }

        if ($subtask->task->created_by === $user->id) {
            return true;
        }

        if ($subtask->task->project && $subtask->task->project->created_by === $user->id) {
            return true;
        }

        return $subtask->assignees->contains('id', $user->id);
    }
}
