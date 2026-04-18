<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Fecha de movimiento</th>
            <th>Acción</th>
            <th>Autor</th>
            <th>Detalle</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($emailRequest->changeLogs as $log)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->localized_action }}</td>
                <td>{{ optional($log->author)->name ?? 'Sistema' }}</td>
                <td>{!! $log->report_content !!}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No hay movimientos registrados.</td>
            </tr>
        @endforelse
    </tbody>
</table>