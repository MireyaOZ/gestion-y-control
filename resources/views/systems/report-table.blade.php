<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nombre del sistema</th>
            <th>Fecha de creación</th>
            <th>Estatus</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($systems as $system)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $system->name }}</td>
                <td>{{ $system->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    {{ $system->status?->name ?? 'Sin estatus' }}
                    @if ($system->status?->slug === 'en-pruebas')
                        <div style="margin-top: 8px; font-size: 11px; color: #475569; line-height: 1.6;">
                            <div>Tarjetas errores pendientes: {{ $system->pending_errors ?? 0 }}</div>
                            <div>Tarjetas errores en proceso de solución: {{ $system->errors_in_progress ?? 0 }}</div>
                            <div>Tarjetas en revisión: {{ $system->in_review ?? 0 }}</div>
                            <div>Tarjetas finalizadas: {{ $system->finalized ?? 0 }}</div>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No hay datos para el reporte.</td>
            </tr>
        @endforelse
    </tbody>
</table>
