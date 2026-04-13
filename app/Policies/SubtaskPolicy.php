<?php

namespace App\Policies;

use App\Models\Subtask;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWorkItems;

class SubtaskPolicy
{
    use AuthorizesWorkItems;

    public function viewAny(User $user): bool
    {
        return $user->can('subtasks.view');
    }

    public function view(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.view') && $this->canAccessSubtask($user, $subtask);
    }

    public function create(User $user): bool
    {
        return $user->can('subtasks.create');
    }

    public function update(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.update') && $this->canAccessSubtask($user, $subtask);
    }

    public function delete(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.delete') && $this->canAccessSubtask($user, $subtask);
    }

    public function assign(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.assign') && $this->canAccessSubtask($user, $subtask);
    }

    public function changeStatus(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.change_status') && $this->canAccessSubtask($user, $subtask);
    }

    public function viewHistory(User $user, Subtask $subtask): bool
    {
        return $user->can('subtask_history.view') && $this->canAccessSubtask($user, $subtask);
    }

    public function comment(User $user, Subtask $subtask): bool
    {
        return $user->can('subtask_comments.create') && $this->canAccessSubtask($user, $subtask);
    }
}
