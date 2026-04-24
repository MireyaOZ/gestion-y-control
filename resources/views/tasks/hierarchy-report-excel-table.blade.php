<table style="width:100%;border-collapse:collapse;font-size:11px;">
    <thead>
        <tr>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">No.</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Subtarea</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Nivel</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Fecha de creación</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Vencimiento</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Estado</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Avance</th>
            <th style="border:1px solid #cbd5e1;padding:8px;text-align:left;background:#f8fafc;color:#475569;">Asignados</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $loop->iteration }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ str_repeat('— ', $row['level']) }}{{ $row['title'] }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $row['level'] + 1 }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $row['created_at'] }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $row['due_date'] }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $row['status'] }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $row['progress'] }}</td>
                <td style="border:1px solid #cbd5e1;padding:8px;vertical-align:top;">{{ $row['assignees'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="border:1px solid #cbd5e1;padding:8px;">No hay subtareas registradas.</td>
            </tr>
        @endforelse
    </tbody>
</table>