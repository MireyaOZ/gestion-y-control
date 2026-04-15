<?php

namespace App\Support;

class PermissionCatalog
{
    public const PERMISSIONS = [
        'admin.access',
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'roles.view',
        'roles.create',
        'roles.update',
        'roles.delete',
        'permissions.view',
        'permissions.assign',
        'emails.view',
        'emails.create',
        'emails.update',
        'emails.delete',
        'systems.view',
        'systems.create',
        'systems.update',
        'systems.delete',
        'tasks.view',
        'tasks.create',
        'tasks.update',
        'tasks.delete',
        'tasks.assign',
        'tasks.change_status',
        'subtasks.view',
        'subtasks.create',
        'subtasks.update',
        'subtasks.delete',
        'subtasks.assign',
        'subtasks.change_status',
        'task_comments.view',
        'task_comments.create',
        'subtask_comments.view',
        'subtask_comments.create',
        'task_history.view',
        'subtask_history.view',
    ];

    public static function all(): array
    {
        return self::PERMISSIONS;
    }
}
