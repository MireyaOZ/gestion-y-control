<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\ChangeLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $search = (string) $request->string('search');

        $tasks = Task::query()
            ->with(['status', 'priority', 'creator', 'assignees'])
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id));
            }))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('tasks.index', compact('tasks', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', $this->formData());
    }

    public function report(Request $request, string $format): Response
    {
        $this->authorize('viewAny', Task::class);

        $search = (string) $request->string('search');
        $reportView = $request->string('view', 'table')->toString();
        abort_unless(in_array($reportView, ['table', 'list'], true), 404);

        $tasks = $this->buildFilteredTasksQuery($request)
            ->with(['status', 'priority', 'creator', 'assignees'])
            ->latest()
            ->get();

        $generatedAt = now();
        $reportTitle = 'Reporte de tareas';
        $filenameBase = 'reporte-tareas-'.$reportView.'-'.$generatedAt->format('Ymd-His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('tasks.report-pdf', compact('tasks', 'generatedAt', 'reportTitle', 'search', 'reportView'))
                ->setPaper('a4', $reportView === 'table' ? 'landscape' : 'portrait');

            return $pdf->download($filenameBase.'.pdf');
        }

        abort_unless($format === 'excel', 404);

        $content = view('tasks.report-excel', compact('tasks', 'generatedAt', 'reportTitle', 'search', 'reportView'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $data = $this->validatedData($request);
        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $task = Task::query()->create($data + ['created_by' => $request->user()->id]);
        $this->syncAssignees($task, $assigneeIds);
        $task->load(['status', 'priority', 'assignees']);

        ChangeLogger::log($task, 'created', $this->buildCreatedTaskLogContent($task, $request->user()->name));

        return redirect()->route('tasks.show', $task)->with('status', 'Tarea creada correctamente.');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load([
            'status',
            'priority',
            'creator',
            'assignees',
            'subtasks.status',
            'subtasks.priority',
            'subtasks.assignees',
            'attachments.uploader',
            'links.creator',
            'comments.author',
            'changeLogs.author',
        ]);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', $this->formData(['task' => $task]));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $this->validatedData($request);
        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $originalTitle = $task->title;
        $originalDescription = $task->description;
        $originalDueDate = optional($task->due_date)->format('Y-m-d');
        $originalStatusName = $task->status?->name ?? 'Sin estado';
        $originalPriorityName = $task->priority?->name ?? 'Sin prioridad';
        $originalAssignees = $task->assignees->pluck('name')->sort()->values()->all();

        $task->update($data);
        $this->syncAssignees($task, $assigneeIds);
        $task->load(['status', 'priority', 'assignees']);

        $changes = [];

        $this->appendTaskChange($changes, 'Título', $originalTitle, $task->title);
        $this->appendTaskChange($changes, 'Descripción', $originalDescription, $task->description, 'Sin descripción');
        $this->appendTaskChange($changes, 'Vencimiento', $this->formatTaskDate($originalDueDate), $this->formatTaskDate(optional($task->due_date)->format('Y-m-d')));
        $this->appendTaskChange($changes, 'Estado', $originalStatusName, $task->status?->name ?? 'Sin estado');
        $this->appendTaskChange($changes, 'Prioridad', $originalPriorityName, $task->priority?->name ?? 'Sin prioridad');
        $this->appendAssigneeChange($changes, $originalAssignees, $task->assignees->pluck('name')->sort()->values()->all());

        if ($changes !== []) {
            ChangeLogger::log($task, 'updated', '<p>Tarea actualizada por '.e($request->user()->name).'.</p>'.implode('', $changes));
        }

        return redirect()->route('tasks.show', $task)->with('status', 'Tarea actualizada.');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('changeStatus', $task);

        $originalStatusName = $task->status?->name ?? 'Sin estado';

        $data = $request->validate([
            'task_status_id' => ['required', 'exists:task_statuses,id'],
        ]);

        $task->update($data);
        $task->load('status');

        ChangeLogger::log(
            $task,
            'status_changed',
            '<p>Estado de tarea actualizado por '.e($request->user()->name).'.</p>'
            .'<p><strong>Estado:</strong> '.e($originalStatusName).' '.e('→').' '.e($task->status?->name ?? 'Sin estado').'</p>'
        );

        return back()->with('status', 'Estado de la tarea actualizado.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        ChangeLogger::log($task, 'deleted', "<p>Tarea eliminada por ".(string) request()->user()?->name.'.</p>');
        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Tarea eliminada.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'task_status_id' => ['required', 'exists:task_statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'assignee_ids' => ['array'],
            'assignee_ids.*' => ['integer', Rule::exists('users', 'id')->where('is_active', true)],
        ]);
    }

    protected function buildFilteredTasksQuery(Request $request)
    {
        $user = $request->user();
        $search = (string) $request->string('search');

        return Task::query()
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id));
            }))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"));
    }

    protected function syncAssignees(Task $task, array $assigneeIds): void
    {
        $payload = User::query()
            ->whereIn('id', $assigneeIds)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => ['assigned_at' => now()]])
            ->all();

        $task->assignees()->sync($payload);
    }

    protected function buildCreatedTaskLogContent(Task $task, string $authorName): string
    {
        return '<p>Tarea creada por '.e($authorName).'.</p>'
            .'<p><strong>Título:</strong> '.e($task->title).'</p>'
            .'<p><strong>Descripción:</strong> '.e($task->description ?: 'Sin descripción').'</p>'
            .'<p><strong>Vencimiento:</strong> '.e($this->formatTaskDate(optional($task->due_date)->format('Y-m-d'))).'</p>'
            .'<p><strong>Estado:</strong> '.e($task->status?->name ?? 'Sin estado').'</p>'
            .'<p><strong>Prioridad:</strong> '.e($task->priority?->name ?? 'Sin prioridad').'</p>'
            .'<p><strong>Asignados:</strong> '.e($this->formatAssigneeList($task->assignees->pluck('name')->all())).'</p>';
    }

    protected function appendTaskChange(array &$changes, string $label, ?string $originalValue, ?string $updatedValue, string $emptyLabel = 'Sin dato'): void
    {
        $originalValue = filled($originalValue) ? $originalValue : $emptyLabel;
        $updatedValue = filled($updatedValue) ? $updatedValue : $emptyLabel;

        if ($originalValue !== $updatedValue) {
            $changes[] = '<p><strong>'.e($label).':</strong> '.e($originalValue).' '.e('→').' '.e($updatedValue).'</p>';
        }
    }

    protected function appendAssigneeChange(array &$changes, array $originalAssignees, array $updatedAssignees): void
    {
        $originalList = $this->formatAssigneeList($originalAssignees);
        $updatedList = $this->formatAssigneeList($updatedAssignees);

        if ($originalList !== $updatedList) {
            $changes[] = '<p><strong>Asignados:</strong> '.e($originalList).' '.e('→').' '.e($updatedList).'</p>';
        }
    }

    protected function formatTaskDate(?string $date): string
    {
        if (blank($date)) {
            return 'Sin fecha';
        }

        return now()->parse($date)->format('d/m/Y');
    }

    protected function formatAssigneeList(array $assignees): string
    {
        return $assignees === [] ? 'Sin asignados' : implode(', ', $assignees);
    }

    protected function formData(array $extra = []): array
    {
        return array_merge([
            'statuses' => TaskStatus::query()->orderBy('name')->get(),
            'priorities' => Priority::query()->orderBy('weight')->get(),
        ], $extra);
    }
}
