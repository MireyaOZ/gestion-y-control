<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">{{ $task->title }}</h2>
                <p class="text-sm text-white/80">Creada el {{ $task->created_at->format('d/m/Y') }} · Tiempo desde asignación: {{ $task->assignment_elapsed ?: 'Sin asignar' }}</p>
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    class="app-button-secondary border-[#960018]/30 text-[#960018] hover:border-[#960018] hover:bg-[#fff1f3] hover:text-[#7c0014] focus:outline-none focus:ring-2 focus:ring-white/60"
                    x-data
                    @click="$dispatch('open-modal', 'task-report-options-{{ $task->id }}')"
                >Generar reporte</button>
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

        <x-modal name="task-report-options-{{ $task->id }}" :show="false" maxWidth="2xl">
            <div x-data="taskReportOptions()" class="p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Generar reporte</h3>
                            <p class="mt-1 text-sm text-slate-500">Elige si quieres exportar la tarea completa, una subtarea concreta o subtareas filtradas por usuario, estado y vencimiento.</p>
                        </div>
                        <button type="button" class="text-slate-400 transition hover:text-slate-700" x-data @click="$dispatch('close-modal', 'task-report-options-{{ $task->id }}')">Cerrar</button>
                    </div>

                    <form method="GET" :action="'{{ route('tasks.hierarchy.report', $task) }}'" class="mt-6 space-y-6">
                        <div class="grid gap-6 xl:grid-cols-3">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5 xl:col-span-3">
                                <label class="app-label">Alcance</label>
                                <div class="mt-3 grid gap-3 lg:grid-cols-3">
                                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="scope" value="full_task" x-model="scope" class="mt-0.5 text-[#960018] focus:ring-[#960018]">
                                        <span>
                                            <span class="block font-semibold text-slate-900">Tarea completa</span>
                                            <span class="mt-1 block text-xs text-slate-500">Incluye la tarea, sus subtareas y todas las subtareas hijas.</span>
                                        </span>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="scope" value="specific_subtask" x-model="scope" class="mt-0.5 text-[#960018] focus:ring-[#960018]">
                                        <span>
                                            <span class="block font-semibold text-slate-900">Subtarea específica</span>
                                            <span class="mt-1 block text-xs text-slate-500">Genera una subtarea puntual junto con su rama hija.</span>
                                        </span>
                                    </label>
                                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-700 transition hover:border-[#960018]/30 hover:bg-[#960018]/5">
                                        <input type="radio" name="scope" value="filtered_subtasks" x-model="scope" class="mt-0.5 text-[#960018] focus:ring-[#960018]">
                                        <span>
                                            <span class="block font-semibold text-slate-900">Subtareas filtradas</span>
                                            <span class="mt-1 block text-xs text-slate-500">Permite filtrar por nombre, tareas completadas, tareas incompletas, tareas vencidas, fecha límite: hoy, entrega mañana.</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

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

                            <div x-show="requiresSubtask()" x-transition class="rounded-3xl border border-slate-200 bg-slate-50 p-5 xl:col-span-3">
                                <label for="task-report-subtask-id-{{ $task->id }}" class="app-label">Subtarea específica</label>
                                <select id="task-report-subtask-id-{{ $task->id }}" name="subtask_id" class="app-input" :disabled="!requiresSubtask()" :required="requiresSubtask()">
                                    <option value="">Selecciona una subtarea</option>
                                    @foreach ($reportSubtaskOptions as $option)
                                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="usesSubtaskFilters()" x-transition class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <label for="task-report-assignee-id-{{ $task->id }}" class="app-label">Usuario asignado</label>
                                <select id="task-report-assignee-id-{{ $task->id }}" name="assignee_id" class="app-input" :disabled="!usesSubtaskFilters()">
                                    <option value="">Todos los usuarios</option>
                                    @foreach ($reportAssigneeOptions as $assigneeOption)
                                        <option value="{{ $assigneeOption->id }}">{{ $assigneeOption->name }} · {{ $assigneeOption->email }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="usesSubtaskFilters()" x-transition class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <label for="task-report-completion-{{ $task->id }}" class="app-label">Estado de entrega</label>
                                <select id="task-report-completion-{{ $task->id }}" name="completion" class="app-input" :disabled="!usesSubtaskFilters()">
                                    <option value="all">Todas</option>
                                    <option value="completed">Completadas</option>
                                    <option value="incomplete">Incompletas</option>
                                </select>
                            </div>

                            <div x-show="usesSubtaskFilters()" x-transition class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                <label for="task-report-due-filter-{{ $task->id }}" class="app-label">Filtro de vencimiento</label>
                                <select id="task-report-due-filter-{{ $task->id }}" name="due_filter" class="app-input" x-model="dueFilter" :disabled="!usesSubtaskFilters()">
                                    <option value="all">Cualquier fecha</option>
                                    <option value="overdue">Vencidas</option>
                                    <option value="today">Vencen hoy</option>
                                    <option value="tomorrow">Vencen mañana</option>
                                    <option value="exact_date">Fecha exacta</option>
                                    <option value="range">Rango de fechas</option>
                                </select>
                            </div>

                            <div x-show="usesSubtaskFilters() && usesDueDate()" x-transition class="rounded-3xl border border-slate-200 bg-slate-50 p-5 xl:col-span-3">
                                <label for="task-report-due-date-{{ $task->id }}" class="app-label">Fecha exacta de vencimiento</label>
                                <input id="task-report-due-date-{{ $task->id }}" name="due_date" type="date" class="app-input" :disabled="!usesSubtaskFilters() || !usesDueDate()">
                            </div>

                            <div x-show="usesSubtaskFilters() && usesDueRange()" x-transition class="rounded-3xl border border-slate-200 bg-slate-50 p-5 xl:col-span-3">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="task-report-due-from-{{ $task->id }}" class="app-label">Vence desde</label>
                                        <input id="task-report-due-from-{{ $task->id }}" name="due_from" type="date" class="app-input" :disabled="!usesSubtaskFilters() || !usesDueRange()">
                                    </div>
                                    <div>
                                        <label for="task-report-due-to-{{ $task->id }}" class="app-label">Vence hasta</label>
                                        <input id="task-report-due-to-{{ $task->id }}" name="due_to" type="date" class="app-input" :disabled="!usesSubtaskFilters() || !usesDueRange()">
                                    </div>
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
    </div>
</x-app-layout>
