@php
    $level = $level ?? 0;
    $childSubtasks = $subtask->childSubtasksRecursive;
    $leftOffset = min($level * 20, 80);
@endphp

<div class="space-y-3" @if($leftOffset > 0) style="margin-left: {{ $leftOffset }}px;" @endif>
    <div class="rounded-2xl border border-white/10 p-4 transition hover:bg-white/5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <a href="{{ route('subtasks.show', $subtask) }}" class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="font-medium text-white">{{ $subtask->title }}</p>
                    @if ($level > 0)
                        <span class="text-[11px] uppercase tracking-[0.2em] text-slate-500">Nivel {{ $level + 1 }}</span>
                    @endif
                </div>
                <p class="text-sm text-slate-400">Vencimiento: {{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin vencimiento' }}</p>
                <p class="mt-1 text-sm text-slate-400">
                    {{ $subtask->assignees->isNotEmpty() ? 'Asignado a: '.$subtask->assignees->pluck('name')->join(', ') : 'Sin usuario asignado' }}
                </p>
            </a>
            <div class="flex items-center gap-3">
                @can('createChild', $subtask)
                    <a href="{{ route('subtasks.create', ['task_id' => $subtask->task_id, 'parent_subtask_id' => $subtask->id]) }}" class="text-xs font-medium text-slate-300 transition hover:text-white">Agregar hija</a>
                @endcan
                <x-status-pill :label="$subtask->status->name" :tone="$subtask->status->slug" />
            </div>
        </div>
    </div>

    @foreach ($childSubtasks as $childSubtask)
        @include('subtasks.tree-node', ['subtask' => $childSubtask, 'level' => $level + 1])
    @endforeach
</div>