<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionCatalog::all() as $permissionName) {
            Permission::query()->firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $administrator = Role::query()->firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $projectManager = Role::query()->firstOrCreate(['name' => 'gestor_proyectos', 'guard_name' => 'web']);
        $taskManager = Role::query()->firstOrCreate(['name' => 'gestor_tareas', 'guard_name' => 'web']);
        $user = Role::query()->firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);

        $administrator->syncPermissions(PermissionCatalog::all());

        $projectManager->syncPermissions([
            'emails.view',
            'emails.create',
            'emails.update',
            'emails.delete',
            'systems.view',
            'systems.create',
            'systems.update',
            'systems.delete',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view',
            'tasks.assign',
            'subtasks.view',
            'task_comments.view',
            'task_comments.create',
            'subtask_comments.view',
            'subtask_comments.create',
            'task_history.view',
            'subtask_history.view',
        ]);

        $taskManager->syncPermissions([
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
        ]);

        $user->syncPermissions([
            'emails.view',
            'emails.create',
            'emails.update',
            'emails.delete',
            'systems.view',
            'systems.create',
            'systems.update',
            'systems.delete',
            'tasks.view',
            'tasks.change_status',
            'subtasks.view',
            'subtasks.change_status',
            'task_comments.view',
            'task_comments.create',
            'subtask_comments.view',
            'subtask_comments.create',
        ]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'is_active' => true,
            ],
        );

        if (! $admin->hasRole('administrador')) {
            $admin->assignRole($administrator);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
