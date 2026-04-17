<?php

namespace App\Support;

use Illuminate\Support\Str;

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

    private const PERMISSION_LABELS = [
        'admin.access' => 'Acceso administrativo',
        'users.view' => 'Ver usuarios',
        'users.create' => 'Crear usuario',
        'users.update' => 'Editar usuario',
        'users.delete' => 'Eliminar usuario',
        'roles.view' => 'Ver roles',
        'roles.create' => 'Crear rol',
        'roles.update' => 'Editar rol',
        'roles.delete' => 'Eliminar rol',
        'permissions.view' => 'Ver permisos',
        'permissions.assign' => 'Asignar permisos',
        'emails.view' => 'Ver correos',
        'emails.create' => 'Crear correo',
        'emails.update' => 'Editar correo',
        'emails.delete' => 'Eliminar correo',
        'systems.view' => 'Ver sistemas',
        'systems.create' => 'Crear sistema',
        'systems.update' => 'Editar sistema',
        'systems.delete' => 'Eliminar sistema',
        'tasks.view' => 'Ver tareas',
        'tasks.create' => 'Crear tarea',
        'tasks.update' => 'Editar tarea',
        'tasks.delete' => 'Eliminar tarea',
        'tasks.assign' => 'Asignar tarea',
        'tasks.change_status' => 'Cambiar estatus de tarea',
        'subtasks.view' => 'Ver subtareas',
        'subtasks.create' => 'Crear subtarea',
        'subtasks.update' => 'Editar subtarea',
        'subtasks.delete' => 'Eliminar subtarea',
        'subtasks.assign' => 'Asignar subtarea',
        'subtasks.change_status' => 'Cambiar estatus de subtarea',
        'task_comments.view' => 'Ver comentarios de tareas',
        'task_comments.create' => 'Crear comentario de tarea',
        'subtask_comments.view' => 'Ver comentarios de subtareas',
        'subtask_comments.create' => 'Crear comentario de subtarea',
        'task_history.view' => 'Ver historial de tareas',
        'subtask_history.view' => 'Ver historial de subtareas',
    ];

    private const ROLE_LABELS = [
        'administrador' => 'Administrador',
        'gestor_operativo' => 'Gestor operativo',
        'gestor_tareas' => 'Gestor de tareas',
        'usuario' => 'Usuario',
    ];

    public static function all(): array
    {
        return self::PERMISSIONS;
    }

    public static function permissionLabel(string $permission): string
    {
        return self::PERMISSION_LABELS[$permission] ?? Str::of($permission)
            ->replace(['.', '_'], ' ')
            ->title()
            ->toString();
    }

    public static function roleLabel(string $role): string
    {
        return self::ROLE_LABELS[$role] ?? Str::of($role)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}
