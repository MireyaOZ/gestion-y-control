<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nombre del sistema</th>
            <th>Fecha de solicitud</th>
            <th>Fecha de creación</th>
            <th>Estatus</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($systems as $system)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $system->name }}</td>
                <td>{{ $system->request_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                <td>{{ $system->created_at->format('d/m/Y') }}</td>
                <td>
                    {{ $system->status?->display_name ?? 'Sin estatus' }}
                    @if ($system->status?->isTesting())
                        <div style="margin-top: 8px; font-size: 11px; color: #475569; line-height: 1.6;">
                            <div>Tarjetas errores pendientes: {{ $system->pending_errors ?? 0 }}</div>
                            <div>Tarjetas errores en proceso de solución: {{ $system->errors_in_progress ?? 0 }}</div>
                            <div>Tarjetas en revisión: {{ $system->in_review ?? 0 }}</div>
                            <div>Tarjetas finalizadas: {{ $system->finalized ?? 0 }}</div>
                            <div>Total de tarjetas en trello: {{ $system->total_trello_cards }}</div>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No hay datos para el reporte.</td>
            </tr>
        @endforelse
    </tbody>
</table>
