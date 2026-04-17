<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">{{ $task->title }}</h2>
                <p class="text-sm text-slate-400">Creada {{ $task->created_at->diffForHumans() }} · Tiempo desde asignación: {{ $task->assignment_elapsed ?: 'Sin asignar' }}</p>
            </div>
            <div class="flex gap-3">
                @can('update', $task)
                    <a href="{{ route('tasks.edit', $task) }}" class="app-button-secondary">Editar</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="app-card p-6">
            <div class="grid gap-4 lg:grid-cols-4">
                <div>
                    <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Estado</span>
                    <div class="mt-2">
                        <x-status-pill :label="$task->status->name" :tone="$task->status->slug" />
                    </div>
                </div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Prioridad</span><p class="mt-2 text-white">{{ $task->priority->name }}</p></div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Vencimiento</span><p class="mt-2 text-white">{{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</p></div>
            </div>
            <p class="mt-6 text-slate-300">{{ $task->description }}</p>

            @can('changeStatus', $task)
                <form method="POST" action="{{ route('tasks.status', $task) }}" class="mt-6 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="app-label" for="task_status_id">Actualizar estado</label>
                        <select id="task_status_id" name="task_status_id" class="app-input" onchange="this.form.requestSubmit()">
                            @foreach (\App\Models\TaskStatus::orderBy('name')->get() as $status)
                                <option value="{{ $status->id }}" @selected($task->task_status_id === $status->id)>{{ ucfirst($status->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endcan
        </section>

        <section class="app-card p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Usuarios asignados</h3>
                <div class="text-sm text-slate-400">{{ $task->assignees->count() }} asignados</div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($task->assignees as $assignee)
                    <x-status-pill :label="$assignee->name.' · '.$assignee->email" />
                @endforeach
            </div>
        </section>

        <section class="app-card p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Subtareas</h3>
                @can('subtasks.create')
                    <a href="{{ route('subtasks.create', ['task_id' => $task->id]) }}" class="app-button-light">Nueva subtarea</a>
                @endcan
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($task->subtasks as $subtask)
                    <a href="{{ route('subtasks.show', $subtask) }}" class="block rounded-2xl border border-white/10 p-4 transition hover:bg-white/5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-white">{{ $subtask->title }}</p>
                                <p class="text-sm text-slate-400">{{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin vencimiento' }}</p>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $subtask->assignees->isNotEmpty() ? 'Asignado a: '.$subtask->assignees->pluck('name')->join(', ') : 'Sin usuario asignado' }}
                                </p>
                            </div>
                            <x-status-pill :label="$subtask->status->name" :tone="$subtask->status->slug" />
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No hay subtareas registradas.</p>
                @endforelse
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @include('shared.resource-panels', ['model' => $task, 'type' => 'task', 'showComments' => false])
            </div>
            <div>
                @include('shared.change-log-panel', ['items' => $task->changeLogs, 'modalName' => 'task-history-'.$task->id])
            </div>
        </div>

        @include('shared.comments-section', ['model' => $task, 'type' => 'task'])
    </div>
</x-app-layout>
