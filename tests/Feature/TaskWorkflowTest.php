<?php

namespace Tests\Feature;

use App\Models\Priority;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_user_can_change_status_of_assigned_task(): void
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        $owner = User::factory()->create();
        $owner->assignRole('gestor_tareas');

        $user = User::factory()->create();
        $user->assignRole('usuario');

        $task = Task::query()->create([
            'title' => 'Seguimiento',
            'description' => 'Actualizar estado',
            'task_status_id' => TaskStatus::query()->where('slug', 'pendiente')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'media')->value('id'),
            'created_by' => $owner->id,
        ]);

        $task->assignees()->attach($user->id, ['assigned_at' => now()]);

        $response = $this->actingAs($user)->patch(route('tasks.status', $task), [
            'task_status_id' => TaskStatus::query()->where('slug', 'en-progreso')->value('id'),
        ]);

        $response->assertRedirect();
        $this->assertSame('en-progreso', $task->fresh()->status->slug);
    }
}
