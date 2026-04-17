<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-white">{{ $subtask->title }}</h2>
                <p class="text-sm text-slate-400">Tarea padre: {{ $subtask->task->title }} · Tiempo desde asignación: {{ $subtask->assignment_elapsed ?: 'Sin asignar' }}</p>
            </div>
            @can('update', $subtask)
                <a href="{{ route('subtasks.edit', $subtask) }}" class="app-button-secondary">Editar</a>
            @endcan
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
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Prioridad</span><p class="mt-2 text-white">{{ $subtask->priority->name }}</p></div>
                <div><span class="text-xs uppercase tracking-[0.2em] text-slate-400">Vencimiento</span><p class="mt-2 text-white">{{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</p></div>
            </div>
            <p class="mt-6 text-slate-300">{{ $subtask->description }}</p>

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

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @include('shared.resource-panels', ['model' => $subtask, 'type' => 'subtask', 'showComments' => false])
            </div>
            <div>
                @include('shared.change-log-panel', ['items' => $subtask->changeLogs, 'modalName' => 'subtask-history-'.$subtask->id])
            </div>
        </div>

        @include('shared.comments-section', ['model' => $subtask, 'type' => 'subtask'])
    </div>
</x-app-layout>
