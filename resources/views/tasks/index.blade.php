<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Tareas</h2>
                <p class="text-sm  text-white/80">Listado de tareas con asignaciones y control de estado.</p>
            </div>
            @can('tasks.create')
                <a href="{{ route('tasks.create') }}" class="app-button-light">Nueva tarea</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        <form method="GET" class="app-card relative p-4" x-data="{ showFilters: false }" @keydown.escape.window="showFilters = false">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <input
                        name="search"
                        value="{{ $search }}"
                        class="app-input mt-0"
                        placeholder="Buscar tarea..."
                        @input="if ($event.target.value.trim() === '' && @js($search !== '')) { $el.form.requestSubmit(); }"
                    >
                </div>

                <div class="flex gap-3">
                    <button type="button" class="app-button-secondary" @click="showFilters = true" :aria-expanded="showFilters.toString()">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4.75A1.25 1.25 0 0 1 3.75 3.5h12.5a1.25 1.25 0 0 1 .97 2.04L12 11.95v3.55a1.25 1.25 0 0 1-.61 1.07l-2 1.2A1.25 1.25 0 0 1 7.5 16.7v-4.75L2.78 5.54a1.25 1.25 0 0 1-.28-.79Z" clip-rule="evenodd" />
                        </svg>
                        Filtros
                    </button>
                    <button type="submit" class="app-button" style="color: #ffffff !important;">Buscar</button>
                </div>
            </div>

            <div x-cloak x-show="showFilters" x-transition.opacity class="fixed inset-0 z-[140] bg-slate-950/30 backdrop-blur-sm" @click="showFilters = false"></div>

            <div
                x-cloak
                x-show="showFilters"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="fixed inset-y-0 right-0 z-[150] w-full max-w-xl overflow-y-auto border-l border-slate-200 bg-white shadow-2xl shadow-[#960018]/20"
            >
                <div class="sticky top-0 border-b border-slate-200 bg-white/95 px-6 py-5 backdrop-blur">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Filtros de tareas</h3>
                            <p class="mt-1 text-sm text-slate-500">Ajusta la búsqueda desde este panel lateral.</p>
                        </div>
                        <button type="button" class="rounded-2xl px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" @click="showFilters = false">
                            Cerrar
                        </button>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-6">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="task-created-at" class="app-label">Fecha de creación</label>
                        <input id="task-created-at" name="created_at" type="date" class="app-input" value="{{ $selectedCreatedDate }}">
                        <p class="mt-2 text-xs text-slate-500">Filtra por la fecha exacta en que se creó la tarea.</p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="task-due-date" class="app-label">Fecha de vencimiento</label>
                        <input id="task-due-date" name="due_date" type="date" class="app-input" value="{{ $selectedDueDate }}">
                        <p class="mt-2 text-xs text-slate-500">Ubica tareas por su fecha límite.</p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="task-status-id" class="app-label">Estado</label>
                        <select id="task-status-id" name="task_status_id" class="app-input">
                            <option value="">Todos los estados</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" @selected($selectedStatusId === $status->id)>{{ ucfirst($status->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label for="task-priority-id" class="app-label">Prioridad</label>
                        <select id="task-priority-id" name="priority_id" class="app-input">
                            <option value="">Todas las prioridades</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}" @selected($selectedPriorityId === $priority->id)>{{ ucfirst($priority->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        <label class="app-label">Creador</label>
                        <x-search-select
                            name="creator_id"
                            :endpoint="route('search.users')"
                            :selected-id="$selectedCreatorId > 0 ? $selectedCreatorId : null"
                            :selected-label="$selectedCreator?->name ?? ''"
                            placeholder="Buscar creador por nombre o correo..."
                        />
                        <p class="mt-2 text-xs text-slate-500">Filtra las tareas por el usuario que las creó.</p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-between">
                        <a href="{{ route('tasks.index') }}" class="app-button-secondary justify-center">Limpiar</a>
                        <button type="submit" class="app-button justify-center" style="color: #ffffff !important;" @click="showFilters = false">Aplicar filtros</button>
                    </div>
                </div>
            </div>

            @if ($search !== '' || $selectedCreatedDate !== '' || $selectedDueDate !== '' || $selectedStatusId > 0 || $selectedPriorityId > 0 || $selectedCreatorId > 0 || $trackingView !== '')
                <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
                    @if ($search !== '')
                        <span>Búsqueda: <span class="font-semibold text-slate-900">{{ $search }}</span></span>
                    @endif
                    @if ($selectedCreatedDate !== '')
                        <span>Fecha de creación: <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($selectedCreatedDate)->format('d/m/Y') }}</span></span>
                    @endif
                    @if ($selectedDueDate !== '')
                        <span>Fecha de vencimiento: <span class="font-semibold text-slate-900">{{ \Illuminate\Support\Carbon::parse($selectedDueDate)->format('d/m/Y') }}</span></span>
                    @endif
                    @if ($selectedStatusId > 0)
                        <span>Estado: <span class="font-semibold text-slate-900">{{ optional($statuses->firstWhere('id', $selectedStatusId))->name ? ucfirst(optional($statuses->firstWhere('id', $selectedStatusId))->name) : 'Sin estado' }}</span></span>
                    @endif
                    @if ($selectedPriorityId > 0)
                        <span>Prioridad: <span class="font-semibold text-slate-900">{{ optional($priorities->firstWhere('id', $selectedPriorityId))->name ? ucfirst(optional($priorities->firstWhere('id', $selectedPriorityId))->name) : 'Sin prioridad' }}</span></span>
                    @endif
                    @if ($selectedCreatorId > 0)
                        <span>Creador: <span class="font-semibold text-slate-900">{{ $selectedCreator?->name ?? 'Sin creador' }}</span></span>
                    @endif
                    @if ($trackingView === 'overdue')
                        <span>Vista: <span class="font-semibold text-rose-700">Tareas vencidas</span></span>
                    @elseif ($trackingView === 'due-date')
                        <span>Vista: <span class="font-semibold text-sky-700">Tareas con fecha de término</span></span>
                    @elseif ($trackingView === 'upcoming')
                        <span>Vista: <span class="font-semibold text-amber-700">Próximas tareas</span></span>
                    @endif
                </div>
            @endif
        </form>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('tasks.report', ['format' => 'excel', 'search' => $search, 'created_at' => $selectedCreatedDate, 'due_date' => $selectedDueDate, 'task_status_id' => $selectedStatusId, 'priority_id' => $selectedPriorityId, 'creator_id' => $selectedCreatorId, 'tracking_view' => $trackingView ?: null, 'view' => 'table']) }}" class="app-button-secondary">Excel tabla</a>
            <a href="{{ route('tasks.report', ['format' => 'excel', 'search' => $search, 'created_at' => $selectedCreatedDate, 'due_date' => $selectedDueDate, 'task_status_id' => $selectedStatusId, 'priority_id' => $selectedPriorityId, 'creator_id' => $selectedCreatorId, 'tracking_view' => $trackingView ?: null, 'view' => 'list']) }}" class="app-button-secondary">Excel lista</a>
            <a href="{{ route('tasks.report', ['format' => 'pdf', 'search' => $search, 'created_at' => $selectedCreatedDate, 'due_date' => $selectedDueDate, 'task_status_id' => $selectedStatusId, 'priority_id' => $selectedPriorityId, 'creator_id' => $selectedCreatorId, 'tracking_view' => $trackingView ?: null, 'view' => 'table']) }}" class="app-button-secondary">PDF tabla</a>
            <a href="{{ route('tasks.report', ['format' => 'pdf', 'search' => $search, 'created_at' => $selectedCreatedDate, 'due_date' => $selectedDueDate, 'task_status_id' => $selectedStatusId, 'priority_id' => $selectedPriorityId, 'creator_id' => $selectedCreatorId, 'tracking_view' => $trackingView ?: null, 'view' => 'list']) }}" class="app-button-secondary">PDF lista</a>
        </div>

        @php($trackingQuery = request()->except('page', 'tracking_view'))

        <section class="space-y-5">
            <div class="grid gap-4 xl:grid-cols-3">
                <a
                    href="{{ route('tasks.index', array_merge($trackingQuery, ['tracking_view' => $trackingView === 'overdue' ? null : 'overdue'])) }}"
                    class="app-card p-6 text-left transition hover:-translate-y-1 hover:border-rose-300/60"
                    @class([
                        'border-rose-300 bg-[linear-gradient(135deg,rgba(255,241,242,.96),rgba(255,255,255,.98))]' => $trackingView === 'overdue',
                    ])
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-rose-500">Seguimiento</p>
                    <h3 class="mt-3 text-lg font-semibold text-white">Tareas vencidas</h3>
                    <p class="mt-1 text-sm text-slate-400">Muestra solo las tareas cuya fecha límite ya pasó.</p>
                    <div class="mt-5 flex items-end justify-between gap-4">
                        <span class="text-4xl font-semibold text-rose-300">{{ $overdueCount }}</span>
                        <span class="text-sm font-medium text-[#960018]">{{ $trackingView === 'overdue' ? 'Quitar filtro' : 'Filtrar' }}</span>
                    </div>
                </a>

                <a
                    href="{{ route('tasks.index', array_merge($trackingQuery, ['tracking_view' => $trackingView === 'due-date' ? null : 'due-date'])) }}"
                    class="app-card p-6 text-left transition hover:-translate-y-1 hover:border-sky-300/60"
                    @class([
                        'border-sky-300 bg-[linear-gradient(135deg,rgba(239,246,255,.96),rgba(255,255,255,.98))]' => $trackingView === 'due-date',
                    ])
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-500">Agenda</p>
                    <h3 class="mt-3 text-lg font-semibold text-white">Tareas con fecha de término</h3>
                    <p class="mt-1 text-sm text-slate-400">Incluye todas las tareas que sí tienen fecha asignada.</p>
                    <div class="mt-5 flex items-end justify-between gap-4">
                        <span class="text-4xl font-semibold text-sky-300">{{ $tasksWithDueDateCount }}</span>
                        <span class="text-sm font-medium text-sky-600">{{ $trackingView === 'due-date' ? 'Quitar filtro' : 'Filtrar' }}</span>
                    </div>
                </a>

                <a
                    href="{{ route('tasks.index', array_merge($trackingQuery, ['tracking_view' => $trackingView === 'upcoming' ? null : 'upcoming'])) }}"
                    class="app-card p-6 text-left transition hover:-translate-y-1 hover:border-amber-300/60"
                    @class([
                        'border-amber-300 bg-[linear-gradient(135deg,rgba(255,251,235,.96),rgba(255,255,255,.98))]' => $trackingView === 'upcoming',
                    ])
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-500">Prioridad</p>
                    <h3 class="mt-3 text-lg font-semibold text-white">Próximas tareas</h3>
                    <p class="mt-1 text-sm text-slate-400">Se ordenan conforme la fecha de vencimiento se va acercando.</p>
                    <div class="mt-5 flex items-end justify-between gap-4">
                        <span class="text-4xl font-semibold text-amber-300">{{ $upcomingTasksCount }}</span>
                        <span class="text-sm font-medium text-amber-600">{{ $trackingView === 'upcoming' ? 'Quitar filtro' : 'Filtrar' }}</span>
                    </div>
                </a>
            </div>
        </section>

        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-white">
                    @if ($trackingView === 'overdue')
                        Listado de tareas vencidas
                    @elseif ($trackingView === 'due-date')
                        Listado de tareas con fecha de término
                    @elseif ($trackingView === 'upcoming')
                        Listado de próximas tareas
                    @else
                        Listado general de tareas
                    @endif
                </h3>
                <p class="text-sm text-white/70">
                    @if ($trackingView === 'overdue')
                        Se muestran solo las tareas cuya fecha límite ya pasó.
                    @elseif ($trackingView === 'due-date')
                        Se muestran solo las tareas que tienen fecha de término asignada.
                    @elseif ($trackingView === 'upcoming')
                        Se muestran las tareas activas con la fecha de vencimiento más cercana.
                    @else
                        Consulta todas las tareas registradas y su fecha de vencimiento.
                    @endif
                </p>
            </div>
        </div>

        <div class="grid gap-4">
            @foreach ($tasks as $task)
                <a href="{{ route('tasks.show', $task) }}" class="app-card block p-6 transition hover:-translate-y-1 hover:border-emerald-400/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-white">{{ $task->title }}</h3>
                                @if ($task->subtasks_count > 0)
                                    <span class="rounded-full border border-slate-200 px-2 py-1 text-[11px] uppercase tracking-[0.18em] text-slate-500">
                                        {{ $task->subtasks_count }} {{ \Illuminate\Support\Str::plural('subtarea', $task->subtasks_count) }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-slate-400">Creada: {{ $task->created_at->format('d/m/Y') }}</p>
                            <p class="mt-1 text-sm text-slate-400">Vencimiento: {{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha de vencimiento' }}</p>
                            @if ($task->is_overdue)
                                <p class="mt-1 text-sm font-semibold text-rose-300">Vencida hace {{ $task->overdue_days }} {{ \Illuminate\Support\Str::plural('día', $task->overdue_days) }}</p>
                            @elseif ($task->due_date?->isToday())
                                <p class="mt-1 text-sm font-semibold text-amber-300">Vence hoy</p>
                            @elseif ($task->due_date?->isTomorrow())
                                <p class="mt-1 text-sm font-semibold text-sky-300">Vence mañana</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-status-pill :label="$task->status->name" :tone="$task->status->slug" />
                            <x-status-pill :label="$task->priority->name" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $tasks->onEachSide(1)->links('vendor.pagination.compact') }}
    </div>
</x-app-layout>
