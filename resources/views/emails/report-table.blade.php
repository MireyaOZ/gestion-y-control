<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Cargo</th>
            <th>Dependencia</th>
            <th>Tipo de movimiento</th>
            <th>Fecha de creación</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($emailRequests as $emailRequest)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $emailRequest->name }}</td>
                <td>{{ $emailRequest->email }}</td>
                <td>{{ $emailRequest->cargo?->name ?? 'Sin cargo' }}</td>
                <td>{{ $emailRequest->cargo?->parent_name ?? 'Sin area dependiente' }}</td>
                <td>{{ $emailRequest->movementType->name }}</td>
                <td>{{ $emailRequest->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No hay datos para el reporte.</td>
            </tr>
        @endforelse
    </tbody>
</table>