<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">{{ $project->title }}</h2>
                <p class="text-sm text-slate-400">Creado {{ $project->created_at->diffForHumans() }} por {{ $project->creator->name }}</p>
            </div>
            <div class="flex gap-3">
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="app-button-secondary">Editar</a>
                @endcan
                @can('delete', $project)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}">
                        @csrf
                        @method('DELETE')
                        <button class="app-button-secondary text-rose-200" type="submit">Eliminar</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="app-card p-6">
            <div class="grid gap-4 lg:grid-cols-4">
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Estado</span><p class="mt-2 text-white">{{ $project->status->name }}</p></div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Prioridad</span><p class="mt-2 text-white">{{ $project->priority->name }}</p></div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Inicio</span><p class="mt-2 text-white">{{ optional($project->start_date)->format('d/m/Y') ?: 'Sin fecha' }}</p></div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Fin</span><p class="mt-2 text-white">{{ optional($project->end_date)->format('d/m/Y') ?: 'Sin fecha' }}</p></div>
            </div>
            <p class="mt-6 text-slate-300">{{ $project->description }}</p>
        </section>

        <section class="app-card p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Tareas relacionadas</h3>
                @can('tasks.create')
                    <a href="{{ route('tasks.create') }}" class="app-button-secondary">Nueva tarea</a>
                @endcan
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($project->tasks as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block rounded-2xl border border-white/10 p-4 transition hover:bg-white/5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-white">{{ $task->title }}</p>
                                <p class="text-sm text-slate-400">{{ optional($task->due_date)->format('d/m/Y') ?: 'Sin vencimiento' }}</p>
                            </div>
                            <x-status-pill :label="$task->status->name" />
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-400">Aún no hay tareas para este proyecto.</p>
                @endforelse
            </div>
        </section>

        @include('shared.resource-panels', ['model' => $project, 'type' => 'project', 'showComments' => false])
        @include('shared.change-log-panel', ['items' => $project->changeLogs])
    </div>
</x-app-layout>
