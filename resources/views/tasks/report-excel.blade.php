<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; }
        h1 { margin: 0 0 12px; font-size: 22px; color: #960018; }
        .meta { margin-bottom: 18px; }
        .meta p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; vertical-align: top; }
        th { background: #f8fafc; text-align: left; color: #475569; }
        .list-item { margin-bottom: 16px; border: 1px solid #cbd5e1; border-radius: 14px; padding: 14px; }
        .list-item h2 { margin: 0 0 10px; color: #960018; font-size: 16px; }
        .list-item p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>{{ $reportTitle }}</h1>

    <div class="meta">
        <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y') }}</p>
        <p><strong>Vista seleccionada:</strong> {{ $reportView === 'table' ? 'Tabla' : 'Lista' }}</p>
        @if (($selectedCreatedDate ?? '') !== '')
            <p><strong>Fecha de creación:</strong> {{ \Carbon\Carbon::parse($selectedCreatedDate)->format('d/m/Y') }}</p>
        @endif
        @if (($selectedDueDate ?? '') !== '')
            <p><strong>Fecha de vencimiento:</strong> {{ \Carbon\Carbon::parse($selectedDueDate)->format('d/m/Y') }}</p>
        @endif
        @if ($selectedStatus)
            <p><strong>Estatus:</strong> {{ $selectedStatus->name }}</p>
        @endif
        @if ($selectedPriority)
            <p><strong>Prioridad:</strong> {{ $selectedPriority->name }}</p>
        @endif
        @if ($selectedCreator)
            <p><strong>Creador:</strong> {{ $selectedCreator->name }}</p>
        @endif
        @if (($trackingView ?? '') !== '')
            <p><strong>Vista de seguimiento:</strong> {{ match ($trackingView) {
                'overdue' => 'TAREAS VENCIDAS',
                'due-date' => 'TAREAS CON FECHA DE TÉRMINO',
                'upcoming' => 'PRÓXIMAS TAREAS',
                default => $trackingView,
            } }}</p>
        @endif
        @if ($search !== '')
            <p><strong>Búsqueda aplicada:</strong> {{ $search }}</p>
        @endif
    </div>

    @include($reportView === 'table' ? 'tasks.report-table' : 'tasks.report-list', ['tasks' => $tasks])
</body>
</html>