<li>
    <div class="node">
        <div class="node-title">{{ $subtask->title }}</div>
        <div class="node-meta">
            Vencimiento: {{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha' }}
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