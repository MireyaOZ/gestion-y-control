<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-white">Dashboard</h2>
            <p class="text-sm  text-white/80">Resumen de correos, sistemas, tareas y subtareas activas.</p>
        </div>
    </x-slot>

    <div class="space-y-6 py-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Tareas</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $tasksCount }}</div>
            </div>
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Subtareas</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $subtasksCount }}</div>
            </div>
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Correos</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $emailsCount }}</div>
            </div>
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Sistemas</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $systemsCount }}</div>
            </div>
        </div>

        <div class="app-card p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Próximas tareas</h3>
                <a href="{{ route('tasks.index') }}" class="text-sm text-emerald-300">Ver todas</a>
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($upcomingTasks as $task)
                    <a href="{{ route('tasks.show', $task) }}"
                        class="block rounded-2xl border border-white/10 p-4 transition hover:bg-white/5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-white">{{ $task->title }}</p>
                                <p class="text-sm text-slate-400">
                                    {{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha de vencimiento' }}</p>
                            </div>
                            <x-status-pill :label="$task->status->name" :tone="$task->status->slug" />
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No hay tareas registradas aún.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
