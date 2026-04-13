<?php

namespace Tests\Feature;

use App\Models\Priority;
use App\Models\Project;
use App\Models\ProjectStatus;
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

    public function test_task_cannot_be_related_to_ineligible_project_status(): void
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        $manager = User::factory()->create();
        $manager->assignRole('gestor_tareas');

        $project = Project::query()->create([
            'title' => 'Proyecto no elegible',
            'description' => 'Sin visto bueno.',
            'project_status_id' => ProjectStatus::query()->where('slug', 'en-proceso-de-reunion')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'media')->value('id'),
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->post(route('tasks.store'), [
            'title' => 'Tarea inválida',
            'description' => 'Intento de creación',
            'task_status_id' => TaskStatus::query()->where('slug', 'pendiente')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'alta')->value('id'),
            'project_id' => $project->id,
        ]);

        $response->assertSessionHasErrors('project_id');
    }

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

    public function test_project_search_returns_only_projects_with_final_approval(): void
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        $manager = User::factory()->create();
        $manager->assignRole('gestor_proyectos');

        Project::query()->create([
            'title' => 'Proyecto elegible',
            'description' => null,
            'project_status_id' => ProjectStatus::query()->where('slug', 'visto-bueno-de-diagramacion')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'alta')->value('id'),
            'created_by' => $manager->id,
        ]);

        Project::query()->create([
            'title' => 'Proyecto no elegible',
            'description' => null,
            'project_status_id' => ProjectStatus::query()->where('slug', 'proceso-de-validacion')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'media')->value('id'),
            'created_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->getJson(route('search.projects', ['query' => 'Proyecto']));

        $response->assertOk();
        $response->assertJsonFragment(['label' => 'Proyecto elegible']);
        $response->assertJsonMissing(['label' => 'Proyecto no elegible']);
    }
}
