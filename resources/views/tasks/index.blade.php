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
        <form class="app-card p-4">
            <input name="search" value="{{ $search }}" class="app-input" placeholder="Buscar tarea...">
        </form>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('tasks.report', ['format' => 'excel', 'search' => $search, 'view' => 'table']) }}" class="app-button-secondary">Excel tabla</a>
            <a href="{{ route('tasks.report', ['format' => 'excel', 'search' => $search, 'view' => 'list']) }}" class="app-button-secondary">Excel lista</a>
            <a href="{{ route('tasks.report', ['format' => 'pdf', 'search' => $search, 'view' => 'table']) }}" class="app-button-secondary">PDF tabla</a>
            <a href="{{ route('tasks.report', ['format' => 'pdf', 'search' => $search, 'view' => 'list']) }}" class="app-button-secondary">PDF lista</a>
        </div>

        <div class="grid gap-4">
            @foreach ($tasks as $task)
                <a href="{{ route('tasks.show', $task) }}" class="app-card block p-6 transition hover:-translate-y-1 hover:border-emerald-400/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $task->title }}</h3>
                            <p class="mt-1 text-sm text-slate-400">Vencimiento: {{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha de vencimiento' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-status-pill :label="$task->status->name" :tone="$task->status->slug" />
                            <x-status-pill :label="$task->priority->name" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $tasks->links() }}
    </div>
</x-app-layout>
