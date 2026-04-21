<?php

namespace App\Policies\Concerns;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;

trait AuthorizesWorkItems
{
    protected function isAdmin(User $user): bool
    {
        return $user->can('admin.access') || $user->hasRole('administrador');
    }

    protected function canAccessTask(User $user, Task $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($task->created_by === $user->id) {
            return true;
        }

        return $task->assignees->contains('id', $user->id);
    }

    protected function isAssignedToTask(User $user, Task $task): bool
    {
        return $task->assignees->contains('id', $user->id);
    }

    protected function canManageTask(User $user, Task $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $task->created_by === $user->id;
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

        if ($this->isAssignedToTask($user, $subtask->task)) {
            return true;
        }

        return $subtask->assignees->contains('id', $user->id);
    }

    protected function isAssignedToSubtask(User $user, Subtask $subtask): bool
    {
        return $subtask->assignees->contains('id', $user->id);
    }

    protected function canManageSubtask(User $user, Subtask $subtask): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($subtask->created_by === $user->id) {
            return true;
        }

        return $subtask->task->created_by === $user->id;
    }

    protected function canContributeToSubtask(User $user, Subtask $subtask): bool
    {
        if ($this->canManageSubtask($user, $subtask)) {
            return true;
        }

        return $this->isAssignedToSubtask($user, $subtask);
    }
}
