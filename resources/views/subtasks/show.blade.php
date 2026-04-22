<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                @php($subtaskAncestors = $subtask->ancestry())
                <div class="mb-3 flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.18em] text-slate-400">
                    <a href="{{ route('tasks.show', $subtask->task) }}" class="transition hover:text-white">{{ $subtask->task->title }}</a>
                    @foreach ($subtaskAncestors as $ancestor)
                        <span>/</span>
                        <a href="{{ route('subtasks.show', $ancestor) }}" class="transition hover:text-white">{{ $ancestor->title }}</a>
                    @endforeach
                    <span>/</span>
                    <span class="text-white">{{ $subtask->title }}</span>
                </div>
                <h2 class="text-2xl font-semibold text-white">{{ $subtask->title }}</h2>
                <p class="text-sm text-white/80">
                    Tarea padre: {{ $subtask->task->title }}
                    @if ($subtask->parentSubtask)
                        · Subtarea superior: {{ $subtask->parentSubtask->title }}
                    @endif
                    · Tiempo desde asignación: {{ $subtask->assignment_elapsed ?: 'Sin asignar' }}
                </p>
            </div>
            <div class="flex gap-3">
                @can('update', $subtask)
                    <a href="{{ route('subtasks.edit', $subtask) }}" class="app-button-secondary border-amber-200 text-amber-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700">Editar</a>
                @endcan
                @can('delete', $subtask)
                    <form method="POST" action="{{ route('subtasks.destroy', $subtask) }}" onsubmit="return confirm('¿Deseas eliminar esta subtarea?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="app-button-secondary text-rose-600 hover:bg-rose-50">Eliminar</button>
                    </form>
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
                        <x-status-pill :label="$subtask->status->name" :tone="$subtask->status->slug" />
                    </div>
                </div>
                <div>
                    <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Prioridad</span>
                    <div class="mt-2">
                        <x-status-pill :label="$subtask->priority->name" />
                    </div>
                </div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Vencimiento</span><p class="mt-2 text-white">{{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</p></div>
            </div>
            <div class="mt-6">
                <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Descripción</span>
                <p class="mt-2 text-slate-300">{{ $subtask->description ?: 'Sin descripción' }}</p>
            </div>

            @can('changeStatus', $subtask)
                <form method="POST" action="{{ route('subtasks.status', $subtask) }}" class="mt-6 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="app-label" for="subtask_status_id">Actualizar estado</label>
                        <select id="subtask_status_id" name="task_status_id" class="app-input" onchange="this.form.requestSubmit()">
                            @foreach (\App\Models\TaskStatus::orderBy('name')->get() as $status)
                                <option value="{{ $status->id }}" @selected($subtask->task_status_id === $status->id)>{{ ucfirst($status->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @endcan
        </section>

        @if ($subtask->childSubtasksRecursive->isNotEmpty() || auth()->user()?->can('createChild', $subtask))
            <section class="app-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Subtareas hijas</h3>
                        <p class="mt-1 text-sm text-slate-400">Puedes anidar nuevas subtareas dentro de esta subtarea.</p>
                    </div>
                    @can('createChild', $subtask)
                        <a href="{{ route('subtasks.create', ['task_id' => $subtask->task_id, 'parent_subtask_id' => $subtask->id]) }}" class="app-button-secondary">Agregar subtarea</a>
                    @endcan
                </div>

                <div x-data="{ expanded: false }" class="mt-4 overflow-x-auto pb-2">
                    <div class="min-w-full space-y-3">
                        @forelse ($subtask->childSubtasksRecursive as $childSubtask)
                            <div x-show="expanded || {{ $loop->index }} < 5" x-transition.opacity.duration.150ms>
                                @include('subtasks.tree-node', ['subtask' => $childSubtask, 'level' => 0])
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No hay subtareas hijas registradas.</p>
                        @endforelse
                    </div>

                    @if ($subtask->childSubtasksRecursive->count() > 5)
                        <div class="mt-4 flex justify-center">
                            <button
                                type="button"
                                class="app-button-secondary"
                                @click="expanded = !expanded"
                                x-text="expanded ? 'Mostrar menos subtareas' : 'Ver más subtareas'"
                            ></button>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @include('shared.resource-panels', ['model' => $subtask, 'type' => 'subtask', 'showComments' => false])
            </div>
            <div>
                @include('shared.change-log-panel', ['items' => $subtask->changeLogs, 'modalName' => 'subtask-history-'.$subtask->id])
            </div>
        </div>

        @canany(['viewComments', 'comment'], $subtask)
            @include('shared.comments-section', ['model' => $subtask, 'type' => 'subtask'])
        @endcanany
    </div>
</x-app-layout>
