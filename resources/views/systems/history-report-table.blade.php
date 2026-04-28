<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Fecha</th>
            <th>Acción</th>
            <th>Autor</th>
            <th>Estatus</th>
            <th>Detalle</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($system->changeLogs as $log)
            @php
                $reportContent = preg_replace(
                    '/<p>Sistema (?:actualizado|registrado|eliminado) por .*?<\/p>/is',
                    '',
                    $log->rendered_content,
                    1,
                );
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $log->created_at->format('d/m/Y') }}</td>
                <td>{{ $log->localized_action }}</td>
                <td>{{ optional($log->author)->name ?? 'Sistema' }}</td>
                <td>{{ $log->status_group }}</td>
                <td class="detail-cell">{!! $reportContent !!}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">No hay movimientos registrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>