<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\AuthorizesWorkItems;

class ProjectPolicy
{
    use AuthorizesWorkItems;

    public function viewAny(User $user): bool
    {
        return $user->can('projects.view');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view') && $this->canAccessProject($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.update') && $this->canAccessProject($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete') && $this->canAccessProject($user, $project);
    }
}
