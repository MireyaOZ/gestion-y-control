<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Services\ChangeLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();
        $search = (string) $request->string('search');

        $projects = Project::query()
            ->with(['status', 'priority', 'creator'])
            ->when(! $user->can('admin.access'), fn ($query) => $query->where(function ($subQuery) use ($user) {
                $subQuery->where('created_by', $user->id)
                    ->orWhereHas('tasks.assignees', fn ($assignees) => $assignees->whereKey($user->id));
            }))
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('projects.index', compact('projects', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $project = Project::query()->create($this->validatedData($request) + [
            'created_by' => $request->user()->id,
        ]);

        ChangeLogger::log($project, 'created', "<p>Proyecto creado por {$request->user()->name}.</p>");

        return redirect()->route('projects.show', $project)->with('status', 'Proyecto creado correctamente.');
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'status',
            'priority',
            'creator',
            'tasks.status',
            'tasks.priority',
            'attachments.uploader',
            'links.creator',
            'comments.author',
            'changeLogs.author',
        ]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', $this->formData(['project' => $project]));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update($this->validatedData($request));
        ChangeLogger::log($project, 'updated', "<p>Proyecto actualizado por {$request->user()->name}.</p>");

        return redirect()->route('projects.show', $project)->with('status', 'Proyecto actualizado.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        ChangeLogger::log($project, 'deleted', "<p>Proyecto eliminado por ".auth()->user()->name.'.</p>');
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Proyecto eliminado.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'project_status_id' => ['required', 'exists:project_statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
        ]);
    }

    protected function formData(array $extra = []): array
    {
        return array_merge([
            'statuses' => ProjectStatus::query()->orderBy('name')->get(),
            'priorities' => Priority::query()->orderBy('weight')->get(),
        ], $extra);
    }
}
