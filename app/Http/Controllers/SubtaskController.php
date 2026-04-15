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

class SubtaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Subtask::class);

        $user = $request->user();
        $search = (string) $request->string('search');

        $subtasks = Subtask::query()
            ->with(['status', 'priority', 'task', 'creator', 'assignees'])
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('assignees', fn ($assignees) => $assignees->whereKey($user->id))
                    ->orWhereHas('task', fn ($tasks) => $tasks->where('created_by', $user->id));
            }))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('subtasks.index', compact('subtasks', 'search'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Subtask::class);

        return view('subtasks.create', $this->formData([
            'selectedTaskId' => $request->integer('task_id'),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Subtask::class);

        $data = $this->validatedData($request);
        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $subtask = Subtask::query()->create($data + ['created_by' => $request->user()->id]);
        $this->syncAssignees($subtask, $assigneeIds);

        ChangeLogger::log($subtask, 'created', "<p>Subtarea creada por {$request->user()->name}.</p>");

        return redirect()->route('subtasks.show', $subtask)->with('status', 'Subtarea creada correctamente.');
    }

    public function show(Subtask $subtask): View
    {
        $this->authorize('view', $subtask);

        $subtask->load([
            'status',
            'priority',
            'task',
            'creator',
            'assignees',
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

        return view('subtasks.edit', $this->formData(['subtask' => $subtask]));
    }

    public function update(Request $request, Subtask $subtask): RedirectResponse
    {
        $this->authorize('update', $subtask);

        $data = $this->validatedData($request);
        $assigneeIds = $data['assignee_ids'] ?? [];
        unset($data['assignee_ids']);

        $subtask->update($data);
        $this->syncAssignees($subtask, $assigneeIds);

        ChangeLogger::log($subtask, 'updated', "<p>Subtarea actualizada por {$request->user()->name}.</p>");

        return redirect()->route('subtasks.show', $subtask)->with('status', 'Subtarea actualizada.');
    }

    public function updateStatus(Request $request, Subtask $subtask): RedirectResponse
    {
        $this->authorize('changeStatus', $subtask);

        $data = $request->validate([
            'task_status_id' => ['required', 'exists:task_statuses,id'],
        ]);

        $subtask->update($data);
        ChangeLogger::log($subtask, 'status_changed', "<p>Estado de subtarea actualizado por {$request->user()->name}.</p>");

        return back()->with('status', 'Estado de la subtarea actualizado.');
    }

    public function destroy(Subtask $subtask): RedirectResponse
    {
        $this->authorize('delete', $subtask);

        ChangeLogger::log($subtask, 'deleted', "<p>Subtarea eliminada por ".(string) request()->user()?->name.'.</p>');
        $subtask->delete();

        return redirect()->route('subtasks.index')->with('status', 'Subtarea eliminada.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'task_id' => ['required', 'exists:tasks,id'],
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

        if ($payload !== []) {
            ChangeLogger::log($subtask, 'assigned', '<p>Usuarios asignados a la subtarea.</p>');
        }
    }

    protected function formData(array $extra = []): array
    {
        return array_merge([
            'statuses' => TaskStatus::query()->orderBy('name')->get(),
            'priorities' => Priority::query()->orderBy('weight')->get(),
            'tasks' => Task::query()->orderBy('title')->get(['id', 'title']),
        ], $extra);
    }
}
