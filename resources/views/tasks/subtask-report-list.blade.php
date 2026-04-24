@forelse ($subtasks as $subtask)
    <section style="margin-bottom:16px;border:1px solid #cbd5e1;border-radius:14px;padding:14px;">
        <h2 style="margin:0 0 10px;color:#960018;font-size:16px;">{{ $loop->iteration }}. {{ $subtask->title }}</h2>
        <p><strong>Tarea:</strong> {{ $subtask->task?->title ?? 'Sin tarea' }}</p>
        <p><strong>Subtarea padre:</strong> {{ $subtask->parentSubtask?->title ?? 'Raíz' }}</p>
        <p><strong>Autor:</strong> {{ $subtask->creator?->name ?? 'Sin autor' }}</p>
        <p><strong>Fecha de creación:</strong> {{ $subtask->created_at->format('d/m/Y') }}</p>
        <p><strong>Vencimiento:</strong> {{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</p>
        <p><strong>Estado:</strong> {{ $subtask->status?->name ?? 'Sin estado' }}</p>
        <p><strong>Prioridad:</strong> {{ $subtask->priority?->name ?? 'Sin prioridad' }}</p>
        <p><strong>Asignados:</strong> {{ $subtask->assignees->isNotEmpty() ? $subtask->assignees->pluck('name')->join(', ') : 'Sin asignados' }}</p>
    </section>
@empty
    <p>No hay subtareas para el reporte.</p>
@endforelse
