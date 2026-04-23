<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1 { margin: 0 0 12px; font-size: 22px; color: #960018; }
        .meta { margin-bottom: 18px; }
        .meta p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
        th { background: #f8fafc; text-align: left; color: #475569; }
        a { color: #960018; text-decoration: none; }
    </style>
</head>
<body>
    <h1>{{ $reportTitle }}</h1>

    <div class="meta">
        <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y') }}</p>
        @if ($selectedArea)
            <p><strong>Área filtrada:</strong> {{ $areaLabel }}</p>
            <p><strong>Área superior:</strong> {{ $parentAreaLabel }}</p>
        @endif
        @if ($selectedMovementType)
            <p><strong>Tipo de movimiento:</strong> {{ $movementTypeLabel }}</p>
        @endif
        @if ($selectedStatus)
            <p><strong>Estatus filtrado:</strong> {{ $statusLabel }}</p>
        @endif
        @if ($selectedRequestDate)
            <p><strong>Fecha de solicitud filtrada:</strong> {{ \Carbon\Carbon::parse($selectedRequestDate)->format('d/m/Y') }}</p>
        @endif
        @if ($selectedRequestYear)
            <p><strong>Año de solicitud filtrado:</strong> {{ $requestYearLabel }}</p>
        @endif
        @if ($selectedDateFrom || $selectedDateTo)
            <p><strong>Fecha filtrada:</strong> {{ $selectedDateFrom ? \Carbon\Carbon::parse($selectedDateFrom)->format('d/m/Y') : 'Sin inicio' }} - {{ $selectedDateTo ? \Carbon\Carbon::parse($selectedDateTo)->format('d/m/Y') : 'Sin fin' }}</p>
        @endif
        @if ($search !== '')
            <p><strong>Búsqueda aplicada:</strong> {{ $search }}</p>
        @endif
    </div>

    @include('emails.report-table', ['emailRequests' => $emailRequests])
</body>
</html>