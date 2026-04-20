<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Título</th>
            <th>Descripción</th>
            <th>Vencimiento</th>
            <th>Estado</th>
            <th>Prioridad</th>
            <th>Asignados</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tasks as $task)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $task->title }}</td>
                <td>{{ $task->description ?: 'Sin descripción' }}</td>
                <td>{{ optional($task->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</td>
                <td>{{ $task->status?->name ?? 'Sin estado' }}</td>
                <td>{{ $task->priority?->name ?? 'Sin prioridad' }}</td>
                <td>{{ $task->assignees->isNotEmpty() ? $task->assignees->pluck('name')->join(', ') : 'Sin asignados' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No hay datos para el reporte.</td>
            </tr>
        @endforelse
    </tbody>
</table>