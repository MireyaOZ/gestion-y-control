@php
    $isCompleted = in_array($subtask->status?->slug, ['completada', 'completed'], true);
@endphp

<li>
    <div class="node">
        <div class="node-header">
            <span style="display:inline-block;width:10px;height:10px;border:1px solid #94a3b8;border-radius:2px;margin-right:6px;vertical-align:middle;{{ $isCompleted ? 'background:#16a34a;border-color:#16a34a;' : 'background:#ffffff;' }}"></span>
            <span class="node-title">{{ $subtask->title }}</span>
            <span class="status-badge {{ $isCompleted ? 'status-badge--complete' : 'status-badge--pending' }}">
                {{ $isCompleted ? '[x] Completada' : '[ ] Pendiente' }}
            </span>
        </div>
        <div class="node-meta">
            <strong>Vencimiento:</strong> {{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha' }}
            <span> | </span>
            <strong>Asignados:</strong> {{ $subtask->assignees->isNotEmpty() ? $subtask->assignees->pluck('name')->join(', ') : 'Sin asignados' }}
        </div>
    </div>

    @if ($subtask->childSubtasksRecursive->isNotEmpty())
        <ul>
            @foreach ($subtask->childSubtasksRecursive as $childSubtask)
                @include('tasks.hierarchy-report-node', ['subtask' => $childSubtask])
            @endforeach
        </ul>
    @endif
</li>