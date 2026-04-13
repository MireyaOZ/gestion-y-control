<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWorkItems;

class TaskPolicy
{
    use AuthorizesWorkItems;

    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view') && $this->canAccessTask($user, $task);
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update') && $this->canAccessTask($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete') && $this->canAccessTask($user, $task);
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->can('tasks.assign') && $this->canAccessTask($user, $task);
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return $user->can('tasks.change_status') && $this->canAccessTask($user, $task);
    }

    public function viewHistory(User $user, Task $task): bool
    {
        return $user->can('task_history.view') && $this->canAccessTask($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $user->can('task_comments.create') && $this->canAccessTask($user, $task);
    }
}
