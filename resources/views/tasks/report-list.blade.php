@forelse ($tasks as $task)
    <section class="list-item">
        <h2>{{ $loop->iteration }}. {{ $task->title }}</h2>
        <p><strong>Autor:</strong> {{ $task->creator?->name ?? 'Sin autor' }}</p>
        <p><strong>Descripción:</strong> {{ $task->description ?: 'Sin descripción' }}</p>
        <p><strong>Fecha de creación:</strong> {{ $task->created_at->format('d/m/Y') }}</p>
        <p><strong>Vencimiento:</strong> {{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</p>
        <p><strong>Estado:</strong> {{ $task->status?->name ?? 'Sin estado' }}</p>
        <p><strong>Prioridad:</strong> {{ $task->priority?->name ?? 'Sin prioridad' }}</p>
        <p><strong>Asignados:</strong> {{ $task->assignees->isNotEmpty() ? $task->assignees->pluck('name')->join(', ') : 'Sin asignados' }}</p>
    </section>
@empty
    <p>No hay datos para el reporte.</p>
@endforelse