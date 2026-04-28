<?php

namespace Tests\Feature;

use App\Models\Priority;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class TaskExcelExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_report_download_is_xlsx(): void
    {
        [$owner, $task] = $this->createTaskFixture();

        $response = $this->actingAs($owner)->get(route('tasks.report', ['format' => 'excel', 'view' => 'table']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());

        $spreadsheet = $this->loadSpreadsheetFromResponse($response->getContent());
        $this->assertSame('Reporte de tareas', $spreadsheet->getActiveSheet()->getCell('A1')->getValue());
    }

    public function test_full_task_hierarchy_download_is_xlsx(): void
    {
        [$owner, $task, $subtask] = $this->createTaskFixture();

        $response = $this->actingAs($owner)->get(route('tasks.hierarchy.report', [
            'task' => $task,
            'format' => 'excel',
            'scope' => 'full_task',
            'view' => 'table',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_specific_subtask_hierarchy_download_is_xlsx(): void
    {
        [$owner, $task, $subtask, $childSubtask] = $this->createTaskFixture();

        $response = $this->actingAs($owner)->get(route('tasks.hierarchy.report', [
            'task' => $task,
            'format' => 'excel',
            'scope' => 'specific_subtask',
            'view' => 'list',
            'subtask_id' => $subtask->id,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_filtered_subtasks_download_is_xlsx(): void
    {
        [$owner, $task, $subtask] = $this->createTaskFixture();

        $response = $this->actingAs($owner)->get(route('tasks.hierarchy.report', [
            'task' => $task,
            'format' => 'excel',
            'scope' => 'filtered_subtasks',
            'view' => 'table',
            'completion' => 'incomplete',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx"', $response->headers->get('Content-Disposition', ''));
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    private function createTaskFixture(): array
    {
        $this->seed([CatalogSeeder::class, RolesAndPermissionsSeeder::class]);

        $owner = User::factory()->create();
        $owner->assignRole('gestor_tareas');

        $assignee = User::factory()->create();
        $assignee->assignRole('usuario');

        $task = Task::query()->create([
            'title' => 'Plan maestro',
            'description' => 'Seguimiento de exportaciones',
            'due_date' => '2026-05-10',
            'task_status_id' => TaskStatus::query()->where('slug', 'pendiente')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'alta')->value('id'),
            'created_by' => $owner->id,
        ]);

        $task->assignees()->attach($assignee->id, ['assigned_at' => now()]);

        $subtask = Subtask::query()->create([
            'title' => 'Subtarea principal',
            'description' => 'Nodo raíz',
            'due_date' => '2026-05-08',
            'task_id' => $task->id,
            'parent_subtask_id' => null,
            'task_status_id' => TaskStatus::query()->where('slug', 'pendiente')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'media')->value('id'),
            'created_by' => $owner->id,
        ]);

        $subtask->assignees()->attach($assignee->id, ['assigned_at' => now()]);

        $childSubtask = Subtask::query()->create([
            'title' => 'Subtarea hija',
            'description' => 'Nodo hijo',
            'due_date' => '2026-05-09',
            'task_id' => $task->id,
            'parent_subtask_id' => $subtask->id,
            'task_status_id' => TaskStatus::query()->where('slug', 'en-progreso')->value('id'),
            'priority_id' => Priority::query()->where('slug', 'baja')->value('id'),
            'created_by' => $owner->id,
        ]);

        return [$owner, $task->fresh(['status', 'priority', 'creator', 'assignees', 'rootSubtasks.childSubtasksRecursive']), $subtask, $childSubtask];
    }

    private function loadSpreadsheetFromResponse(string $content): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx-test-');
        file_put_contents($tempFile, $content);

        $spreadsheet = IOFactory::load($tempFile);
        @unlink($tempFile);

        return $spreadsheet;
    }
}