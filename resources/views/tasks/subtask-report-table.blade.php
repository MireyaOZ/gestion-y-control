<table style="width:100%;border-collapse:collapse;font-size:11px;">
    <thead>
        <tr>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">No.</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Subtarea</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Tarea</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Subtarea padre</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Autor</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Fecha de creación</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Vencimiento</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Estado</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Prioridad</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Asignados</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($subtasks as $subtask)
            <tr>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $loop->iteration }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->title }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->task?->title ?? 'Sin tarea' }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->parentSubtask?->title ?? 'Raíz' }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->creator?->name ?? 'Sin autor' }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->created_at->format('d/m/Y') }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin fecha' }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->status?->name ?? 'Sin estado' }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->priority?->name ?? 'Sin prioridad' }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $subtask->assignees->isNotEmpty() ? $subtask->assignees->pluck('name')->join(', ') : 'Sin asignados' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="border:1px solid #cbd5e1;padding:8px;">No hay subtareas para el reporte.</td>
            </tr>
        @endforelse
    </tbody>
</table>
