<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
</head>
<body>
    <h1 style="color:#960018;margin:0 0 8px;font-size:22px;">{{ $reportTitle }}</h1>
    <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y') }}</p>
    <p><strong>Tarea base:</strong> {{ $task->title }}</p>
    <p><strong>Subtarea seleccionada:</strong> {{ $subtask->title }}</p>

    @if (($reportView ?? 'list') === 'table')
        @include('tasks.hierarchy-report-excel-table', ['rows' => $hierarchyRows])
    @else
        @include('tasks.hierarchy-report-excel-list', ['rows' => $hierarchyRows])
    @endif
</body>
</html>
