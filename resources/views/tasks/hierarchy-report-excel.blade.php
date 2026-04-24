<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
</head>
<body>
    <h1 style="color:#960018;margin:0 0 8px;font-size:22px;">{{ $reportTitle }}</h1>
    <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y') }}</p>

    <h2 style="margin:10px 0 4px;font-size:18px;">{{ $task->title }}</h2>
    <p>
        <strong>Creada:</strong> {{ $task->created_at->format('d/m/Y') }} |
        <strong>Creador:</strong> {{ $task->creator?->name ?? 'Sin autor' }} |
        <strong>Asignados:</strong> {{ $task->assignees->isNotEmpty() ? $task->assignees->pluck('name')->join(', ') : 'Sin asignados' }} |
        <strong>Estado:</strong> {{ $task->status?->name ?? 'Sin estado' }} |
        <strong>Subtareas:</strong> {{ $task->rootSubtasks->count() }} |
        <strong>Avance general:</strong> {{ $task->subtasks_progress_percentage }}%
    </p>

    @if ($task->rootSubtasks->isNotEmpty())
        @if (($reportView ?? 'list') === 'table')
            @include('tasks.hierarchy-report-excel-table', ['rows' => $hierarchyRows])
        @else
            @include('tasks.hierarchy-report-excel-list', ['rows' => $hierarchyRows])
        @endif
    @else
        <p>Esta tarea no tiene subtareas registradas.</p>
    @endif
</body>
</html>