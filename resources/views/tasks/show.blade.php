<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">{{ $task->title }}</h2>
                <p class="text-sm text-white/80">Creada el {{ $task->created_at->format('d/m/Y') }} · Tiempo desde asignación: {{ $task->assignment_elapsed ?: 'Sin asignar' }}</p>
            </div>
            <div class="flex gap-3">
                @if ($task->rootSubtasks->isNotEmpty())
                    <button
                        type="button"
                        class="app-button-secondary border-[#960018]/30 text-[#960018] hover:border-[#960018] hover:bg-[#fff1f3] hover:text-[#7c0014] focus:outline-none focus:ring-2 focus:ring-white/60"
                        x-data
                        @click="$dispatch('open-modal', 'task-report-options-{{ $task->id }}')"
                    >Generar reporte</button>
                @endif
                @can('update', $task)
                    <a href="{{ route('tasks.edit', $task) }}" class="app-button-secondary border-amber-200 text-amber-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700">Editar</a>
                @endcan
                @can('delete', $task)
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Deseas eliminar esta tarea?');">
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
                        <x-status-pill :label="$task->status->name" :tone="$task->status->slug" />
                    </div>
                </div>
                <div>
                    <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Prioridad</span>
                    <div class="mt-2">
                        <x-status-pill :label="$task->priority->name" />
                    </div>
                </div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Vencimiento</span><p class="mt-2 text-white">{{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</p></div>
            </div>
            <div class="mt-6">
                <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Descripción</span>
                <p class="mt-2 text-slate-300">{{ $task->description ?: 'Sin descripción' }}</p>
            </div>

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

        @if ($task->rootSubtasks->isNotEmpty() || auth()->user()?->can('subtasks.create'))
            <section class="app-card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Subtareas</h3>
                    @can('subtasks.create')
                        <a href="{{ route('subtasks.create', ['task_id' => $task->id]) }}" class="app-button-secondary">Nueva subtarea</a>
                    @endcan
                </div>
                <div x-data="expandableList()" class="mt-4 overflow-x-auto pb-2">
                    <div class="min-w-full space-y-3">
                        @forelse ($task->rootSubtasks as $subtask)
                            <div x-show="expanded || {{ $loop->index }} < 5" x-transition.opacity.duration.150ms>
                                @include('subtasks.tree-node', ['subtask' => $subtask, 'level' => 0])
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No hay subtareas registradas.</p>
                        @endforelse
                    </div>

                    @if ($task->rootSubtasks->count() > 5)
                        <div class="mt-4 flex justify-center">
                            <button
                                type="button"
                                class="app-button-secondary"
                                @click="toggle()"
                                x-text="expanded ? 'Mostrar menos subtareas' : 'Ver más subtareas'"
                            ></button>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @include('shared.resource-panels', ['model' => $task, 'type' => 'task', 'showComments' => false])
            </div>
            <div>
                @include('shared.change-log-panel', ['items' => $task->changeLogs, 'modalName' => 'task-history-'.$task->id])
            </div>
        </div>

        @canany(['viewComments', 'comment'], $task)
            @include('shared.comments-section', ['model' => $task, 'type' => 'task'])
        @endcanany

        @if ($task->rootSubtasks->isNotEmpty())
            <x-modal name="task-report-options-{{ $task->id }}" :show="false" maxWidth="lg">
                <div x-data="reportOptions()" class="p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Generar reporte</h3>
                            <p class="mt-1 text-sm text-slate-500">Selecciona el formato y el tipo de vista para exportar la tarea con sus subtareas.</p>
                        </div>
                        <button type="button" class="text-slate-400 transition hover:text-slate-700" x-data @click="$dispatch('close-modal', 'task-report-options-{{ $task->id }}')">Cerrar</button>
                    </div>

                    <form method="GET" :action="'{{ route('tasks.hierarchy.report', $task) }}'" class="mt-6 space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <label class="app-label">Formato</label>
                                <div class="mt-3 grid gap-3">
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="format" value="pdf" x-model="format" class="text-[#960018] focus:ring-[#960018]">
                                        <span>PDF</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="format" value="excel" x-model="format" class="text-[#960018] focus:ring-[#960018]">
                                        <span>Excel</span>
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <label class="app-label">Vista</label>
                                <div class="mt-3 grid gap-3">
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="view" value="list" x-model="view" class="text-[#960018] focus:ring-[#960018]">
                                        <span>Lista</span>
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="view" value="table" x-model="view" class="text-[#960018] focus:ring-[#960018]">
                                        <span>Tabla</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                            <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'task-report-options-{{ $task->id }}')">Cancelar</button>
                            <button type="submit" class="app-button" style="color: #ffffff !important;">Generar reporte</button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif
    </div>
</x-app-layout>
