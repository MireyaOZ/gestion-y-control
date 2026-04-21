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

    public function createChild(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.create') && $this->canContributeToSubtask($user, $subtask);
    }

    public function update(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.update') && $this->canManageSubtask($user, $subtask);
    }

    public function delete(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.delete') && $this->canManageSubtask($user, $subtask);
    }

    public function assign(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.assign') && $this->canManageSubtask($user, $subtask);
    }

    public function changeStatus(User $user, Subtask $subtask): bool
    {
        return $user->can('subtasks.change_status')
            && $this->canContributeToSubtask($user, $subtask);
    }

    public function viewHistory(User $user, Subtask $subtask): bool
    {
        return $user->can('subtask_history.view') && $this->canAccessSubtask($user, $subtask);
    }

    public function viewComments(User $user, Subtask $subtask): bool
    {
        return $this->canAccessSubtask($user, $subtask);
    }

    public function manageResources(User $user, Subtask $subtask): bool
    {
        return $this->canContributeToSubtask($user, $subtask)
            || ($user->can('subtasks.update') && $this->canManageSubtask($user, $subtask));
    }

    public function comment(User $user, Subtask $subtask): bool
    {
        return $user->can('subtask_comments.create')
            && $this->canContributeToSubtask($user, $subtask);
    }
}
