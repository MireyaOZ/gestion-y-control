<table style="width:100%;border-collapse:collapse;font-size:11px;">
    <thead>
        <tr>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">No.</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Jerarquía</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Detalle</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $loop->iteration }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;white-space:nowrap;">{{ str_repeat('→ ', $row['level']) }}Nivel {{ $row['level'] + 1 }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">
                    <strong>{{ $row['title'] }}</strong><br>
                    <strong>Fecha de creación:</strong> {{ $row['created_at'] }} |
                    <strong>Vencimiento:</strong> {{ $row['due_date'] }} |
                    <strong>Estado:</strong> {{ $row['status'] }} |
                    <strong>Avance:</strong> {{ $row['progress'] }} |
                    <strong>Asignados:</strong> {{ $row['assignees'] }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="border:1px solid #cbd5e1;padding:8px;">No hay subtareas registradas.</td>
            </tr>
        @endforelse
    </tbody>
</table>