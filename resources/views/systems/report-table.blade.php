<table style="table-layout: fixed;">
    <colgroup>
        <col style="width: 7%;">
        <col style="width: 29%;">
        <col style="width: 14%;">
        <col style="width: 14%;">
        <col style="width: 36%;">
    </colgroup>
    <thead>
        <tr>
            <th style="width: 7%;">No.</th>
            <th style="width: 29%;">Nombre del sistema</th>
            <th style="width: 14%;">Fecha de solicitud</th>
            <th style="width: 14%;">Fecha de creación</th>
            <th style="width: 36%;">Estatus</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($systems as $system)
            <tr>
                <td style="white-space: nowrap;">{{ $loop->iteration }}</td>
                <td style="word-break: break-word; overflow-wrap: break-word;">{{ $system->name }}</td>
                <td style="white-space: nowrap;">{{ $system->request_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                <td style="white-space: nowrap;">{{ $system->created_at->format('d/m/Y') }}</td>
                <td style="word-break: break-word; overflow-wrap: break-word; line-height: 1.5;">
                    {{ $system->status?->display_name ?? 'Sin estatus' }}
                    @if ($system->status?->isTesting())
                        <div style="margin-top: 6px; font-size: 11px; color: #475569; line-height: 1.45;">
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
