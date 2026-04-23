<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Fecha de solicitud</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Cargo</th>
            <th>Superior jerárquico</th>
            <th>Tipo de movimiento</th>
            <th>Estatus</th>
            <th>Fecha de creación</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($emailRequests as $emailRequest)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $emailRequest->request_date?->format('d/m/Y') ?? 'Sin fecha' }}</td>
                <td>{{ $emailRequest->name }}</td>
                <td>{{ $emailRequest->email }}</td>
                <td>{{ $emailRequest->cargo?->name ?? 'Sin cargo' }}</td>
                <td>{{ $emailRequest->cargo?->parent_name ?? 'Sin superior' }}</td>
                <td>{{ $emailRequest->movementType->name }}</td>
                <td>{{ $emailRequest->operational_status }}</td>
                <td>{{ $emailRequest->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No hay datos para el reporte.</td>
            </tr>
        @endforelse
    </tbody>
</table>