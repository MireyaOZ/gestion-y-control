<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Tareas</h2>
                <p class="text-sm text-slate-400">Listado de tareas con asignaciones y control de estado.</p>
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

        <div class="grid gap-4">
            @foreach ($tasks as $task)
                <a href="{{ route('tasks.show', $task) }}" class="app-card block p-6 transition hover:-translate-y-1 hover:border-emerald-400/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $task->title }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ $task->project?->title ?: 'Sin proyecto padre' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-status-pill :label="$task->status->name" />
                            <x-status-pill :label="$task->priority->name" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $tasks->links() }}
    </div>
</x-app-layout>
