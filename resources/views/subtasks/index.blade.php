<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">Subtareas</h2>
                <p class="text-sm  text-white/80">Seguimiento fino por tarea padre y responsables.</p>
            </div>
            @can('subtasks.create')
                <a href="{{ route('subtasks.create') }}" class="app-button-light">Nueva subtarea</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        <form class="app-card p-4">
            <input name="search" value="{{ $search }}" class="app-input" placeholder="Buscar subtarea...">
        </form>

        <div class="grid gap-4">
            @foreach ($subtasks as $subtask)
                <a href="{{ route('subtasks.show', $subtask) }}" class="app-card block p-6 transition hover:-translate-y-1 hover:border-emerald-400/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $subtask->title }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ $subtask->task->title }}</p>
                            <p class="mt-1 text-sm text-slate-400">Vencimiento: {{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin vencimiento' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-status-pill :label="$subtask->status->name" :tone="$subtask->status->slug" />
                            <x-status-pill :label="$subtask->priority->name" />
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $subtasks->links() }}
    </div>
</x-app-layout>
