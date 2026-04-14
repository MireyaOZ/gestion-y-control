<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Proyectos</h2>
                <p class="text-sm text-slate-400">Vista operativa de proyectos y sus tareas relacionadas.</p>
            </div>
            @can('projects.create')
                <a href="{{ route('projects.create') }}" class="app-button-light">Nuevo proyecto</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        <form class="app-card p-4">
            <input name="search" value="{{ $search }}" class="app-input" placeholder="Buscar proyecto...">
        </form>

        <div class="grid gap-4">
            @foreach ($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="app-card block p-6 transition hover:-translate-y-1 hover:border-emerald-400/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $project->title }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($project->description, 140) }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-status-pill :label="$project->status->name" />
                            <x-status-pill :label="$project->priority->name" class="text-amber-200" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $projects->links() }}
    </div>
</x-app-layout>
