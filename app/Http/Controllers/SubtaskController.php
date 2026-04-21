<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\ChangeLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubtaskController extends Controller
{
    private const CHANGE_ARROW = '<span style="color:#2563eb;font-weight:700;">&rarr;</span>';

    public function create(Request $request): View
    {
        $this->authorize('create', Subtask::class);

        $selectedParentSubtask = $this->resolveParentSubtask($request->integer('parent_subtask_id'));
        $selectedTaskId = $request->integer('task_id');

        if ($selectedParentSubtask !== null) {
            $this->authorize('createChild', $selectedParentSubtask);
            $selectedTaskId = $selectedParentSubtask->task_id;
        }

        return view('subtasks.create', $this->formData([
            'selectedTaskId' => $selectedTaskId,
            'selectedParentSubtask' => $selectedParentSubtask,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Subtask::class);

        $data = $this->validatedData($request);
        $parentSubtask = $this->resolveValidatedParentSubtask($data);

        if ($parentSubtask !== null) {
            $this->authorize('createChild', $parentSubtask);
            $data['task_id'] = $parentSubtask->task_id;
            $data['parent_subtask_id'] = $parentSubtask->id;
        }

        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $subtask = Subtask::query()->create($data + ['created_by' => $request->user()->id]);
        $this->syncAssignees($subtask, $assigneeIds);
        $subtask->load(['status', 'priority', 'assignees', 'parentSubtask']);

        ChangeLogger::log($subtask, 'created', $this->buildCreatedSubtaskLogContent($subtask, $request->user()->name));

        return redirect()->route('subtasks.show', $subtask)->with('status', 'Subtarea creada correctamente.');
    }

    public function show(Subtask $subtask): View
    {
        $this->authorize('view', $subtask);

        $subtask->load([
            'status',
            'priority',
            'task',
            'parentSubtask',
            'creator',
            'assignees',
            'childSubtasksRecursive',
            'attachments.uploader',
            'links.creator',
            'comments.author',
            'changeLogs.author',
        ]);

        return view('subtasks.show', compact('subtask'));
    }

    public function edit(Subtask $subtask): View
    {
        $this->authorize('update', $subtask);

        $subtask->loadMissing('parentSubtask');

        return view('subtasks.edit', $this->formData([
            'subtask' => $subtask,
            'selectedParentSubtask' => $subtask->parentSubtask,
        ]));
    }

    public function update(Request $request, Subtask $subtask): RedirectResponse
    {
        $this->authorize('update', $subtask);

        $data = $this->validatedData($request);
        $parentSubtask = $this->resolveValidatedParentSubtask($data, $subtask);

        if ($parentSubtask !== null) {
            $data['task_id'] = $parentSubtask->task_id;
            $data['parent_subtask_id'] = $parentSubtask->id;
        } else {
            $data['parent_subtask_id'] = null;
        }

        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $originalTitle = $subtask->title;
        $originalDescription = $subtask->description;
        $originalDueDate = optional($subtask->due_date)->format('Y-m-d');
        $originalStatusName = $subtask->status?->name ?? 'Sin estado';
        $originalPriorityName = $subtask->priority?->name ?? 'Sin prioridad';
        $originalParentTitle = $subtask->parentSubtask?->title;
        $originalAssignees = $subtask->assignees->pluck('name')->sort()->values()->all();

        $subtask->update($data);
        $this->syncAssignees($subtask, $assigneeIds);
        $subtask->load(['status', 'priority', 'assignees', 'parentSubtask']);

        $changes = [];

        $this->appendSubtaskChange($changes, 'Título', $originalTitle, $subtask->title);
        $this->appendSubtaskChange($changes, 'Descripción', $originalDescription, $subtask->description, 'Sin descripción');
        $this->appendSubtaskChange($changes, 'Vencimiento', $this->formatSubtaskDate($originalDueDate), $this->formatSubtaskDate(optional($subtask->due_date)->format('Y-m-d')));
        $this->appendSubtaskChange($changes, 'Estado', $originalStatusName, $subtask->status?->name ?? 'Sin estado');
        $this->appendSubtaskChange($changes, 'Prioridad', $originalPriorityName, $subtask->priority?->name ?? 'Sin prioridad');
        $this->appendSubtaskChange($changes, 'Subtarea superior', $originalParentTitle, $subtask->parentSubtask?->title, 'Sin subtarea superior');
        $this->appendAssigneeChange($changes, $originalAssignees, $subtask->assignees->pluck('name')->sort()->values()->all());

        if ($changes !== []) {
            ChangeLogger::log($subtask, 'updated', '<p>Subtarea actualizada por '.e($request->user()->name).'.</p>'.implode('', $changes));
        }

        return redirect()->route('subtasks.show', $subtask)->with('status', 'Subtarea actualizada.');
    }

    public function updateStatus(Request $request, Subtask $subtask): RedirectResponse
    {
        $this->authorize('changeStatus', $subtask);

        $originalStatusName = $subtask->status?->name ?? 'Sin estado';

        $data = $request->validate([
            'task_status_id' => ['required', 'exists:task_statuses,id'],
        ]);

        $subtask->update($data);
        $subtask->load('status');

        ChangeLogger::log(
            $subtask,
            'status_changed',
            '<p>Estado de subtarea actualizado por '.e($request->user()->name).'.</p>'
            .'<p><strong>Estado:</strong> '.e($originalStatusName).' '.self::CHANGE_ARROW.' '.e($subtask->status?->name ?? 'Sin estado').'</p>'
        );

        return back()->with('status', 'Estado de la subtarea actualizado.');
    }

    public function destroy(Subtask $subtask): RedirectResponse
    {
        $this->authorize('delete', $subtask);

        $task = $subtask->task;

        ChangeLogger::log($subtask, 'deleted', "<p>Subtarea eliminada por ".(string) request()->user()?->name.'.</p>');
        $subtask->delete();

        return redirect()->route('tasks.show', $task)->with('status', 'Subtarea eliminada.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'task_id' => ['required', 'exists:tasks,id'],
            'parent_subtask_id' => ['nullable', 'integer', 'exists:subtasks,id'],
            'task_status_id' => ['required', 'exists:task_statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'assignee_ids' => ['array'],
            'assignee_ids.*' => ['integer', Rule::exists('users', 'id')->where('is_active', true)],
        ]);
    }

    protected function syncAssignees(Subtask $subtask, array $assigneeIds): void
    {
        $payload = User::query()
            ->whereIn('id', $assigneeIds)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => ['assigned_at' => now()]])
            ->all();

        $subtask->assignees()->sync($payload);
    }

    protected function formData(array $extra = []): array
    {
        return array_merge([
            'statuses' => TaskStatus::query()->orderBy('name')->get(),
            'priorities' => Priority::query()->orderBy('weight')->get(),
            'tasks' => Task::query()->orderBy('title')->get(['id', 'title']),
            'selectedParentSubtask' => null,
        ], $extra);
    }

    protected function buildCreatedSubtaskLogContent(Subtask $subtask, string $authorName): string
    {
        return '<p>Subtarea creada por '.e($authorName).'.</p>'
            .'<p><strong>Título:</strong> '.e($subtask->title).'</p>'
            .'<p><strong>Descripción:</strong> '.e($subtask->description ?: 'Sin descripción').'</p>'
            .'<p><strong>Vencimiento:</strong> '.e(optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha').'</p>'
            .'<p><strong>Subtarea superior:</strong> '.e($subtask->parentSubtask?->title ?? 'Sin subtarea superior').'</p>'
            .'<p><strong>Estado:</strong> '.e($subtask->status?->name ?? 'Sin estado').'</p>'
            .'<p><strong>Prioridad:</strong> '.e($subtask->priority?->name ?? 'Sin prioridad').'</p>'
            .'<p><strong>Asignados:</strong> '.e($subtask->assignees->isNotEmpty() ? $subtask->assignees->pluck('name')->join(', ') : 'Sin asignados').'</p>';
    }

    protected function resolveParentSubtask(?int $parentSubtaskId): ?Subtask
    {
        if (($parentSubtaskId ?? 0) <= 0) {
            return null;
        }

        return Subtask::query()->with('parentSubtask')->find($parentSubtaskId);
    }

    protected function resolveValidatedParentSubtask(array $data, ?Subtask $currentSubtask = null): ?Subtask
    {
        $parentSubtaskId = (int) ($data['parent_subtask_id'] ?? 0);

        if ($parentSubtaskId <= 0) {
            return null;
        }

        $parentSubtask = Subtask::query()->with('parentSubtask')->find($parentSubtaskId);

        if ($parentSubtask === null) {
            throw ValidationException::withMessages([
                'parent_subtask_id' => 'La subtarea superior seleccionada no existe.',
            ]);
        }

        if ((int) $data['task_id'] !== $parentSubtask->task_id) {
            throw ValidationException::withMessages([
                'parent_subtask_id' => 'La subtarea superior debe pertenecer a la misma tarea principal.',
            ]);
        }

        if ($currentSubtask !== null) {
            $currentSubtask->loadMissing('parentSubtask');

            if ($parentSubtask->is($currentSubtask)) {
                throw ValidationException::withMessages([
                    'parent_subtask_id' => 'Una subtarea no puede ser su propia subtarea superior.',
                ]);
            }

            if ($parentSubtask->isDescendantOf($currentSubtask)) {
                throw ValidationException::withMessages([
                    'parent_subtask_id' => 'No puedes mover una subtarea dentro de una de sus descendientes.',
                ]);
            }
        }

        return $parentSubtask;
    }

    protected function appendSubtaskChange(array &$changes, string $label, ?string $originalValue, ?string $updatedValue, string $emptyLabel = 'Sin dato'): void
    {
        $originalValue = filled($originalValue) ? $originalValue : $emptyLabel;
        $updatedValue = filled($updatedValue) ? $updatedValue : $emptyLabel;

        if ($originalValue !== $updatedValue) {
            $changes[] = '<p><strong>'.e($label).':</strong> '.e($originalValue).' '.self::CHANGE_ARROW.' '.e($updatedValue).'</p>';
        }
    }

    protected function appendAssigneeChange(array &$changes, array $originalAssignees, array $updatedAssignees): void
    {
        $originalList = $this->formatAssigneeList($originalAssignees);
        $updatedList = $this->formatAssigneeList($updatedAssignees);

        if ($originalList !== $updatedList) {
            $changes[] = '<p><strong>Asignados:</strong> '.e($originalList).' '.self::CHANGE_ARROW.' '.e($updatedList).'</p>';
        }
    }

    protected function formatSubtaskDate(?string $date): string
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
}
