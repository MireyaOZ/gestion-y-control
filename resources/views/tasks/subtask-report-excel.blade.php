<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
</head>
<body>
    <h1 style="color:#960018;margin:0 0 8px;font-size:22px;">{{ $reportTitle }}</h1>
    <p><strong>Tarea base:</strong> {{ $task->title }}</p>
    <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y') }}</p>
    <p><strong>Vista seleccionada:</strong> {{ $reportView === 'table' ? 'Tabla' : 'Lista' }}</p>

    @if ($selectedAssignee)
        <p><strong>Usuario asignado:</strong> {{ $selectedAssignee->name }}</p>
    @endif

    @if ($selectedCompletion === 'completed')
        <p><strong>Estado de entrega:</strong> Completadas</p>
    @elseif ($selectedCompletion === 'incomplete')
        <p><strong>Estado de entrega:</strong> Incompletas</p>
    @endif

    @if ($selectedCreatedFrom !== '' || $selectedCreatedTo !== '')
        <p><strong>Creación:</strong> {{ $selectedCreatedFrom !== '' ? \Carbon\Carbon::parse($selectedCreatedFrom)->format('d/m/Y') : 'Sin límite' }} a {{ $selectedCreatedTo !== '' ? \Carbon\Carbon::parse($selectedCreatedTo)->format('d/m/Y') : 'Sin límite' }}</p>
    @endif

    @if ($selectedDueFilter === 'overdue')
        <p><strong>Vencimiento:</strong> Vencidas</p>
    @elseif ($selectedDueFilter === 'today')
        <p><strong>Vencimiento:</strong> Vencen hoy</p>
    @elseif ($selectedDueFilter === 'tomorrow')
        <p><strong>Vencimiento:</strong> Vencen mañana</p>
    @elseif ($selectedDueFilter === 'exact_date' && $selectedDueDate !== '')
        <p><strong>Vencimiento:</strong> {{ \Carbon\Carbon::parse($selectedDueDate)->format('d/m/Y') }}</p>
    @elseif ($selectedDueFilter === 'range' && ($selectedDueFrom !== '' || $selectedDueTo !== ''))
        <p><strong>Vencimiento:</strong> {{ $selectedDueFrom !== '' ? \Carbon\Carbon::parse($selectedDueFrom)->format('d/m/Y') : 'Sin límite' }} a {{ $selectedDueTo !== '' ? \Carbon\Carbon::parse($selectedDueTo)->format('d/m/Y') : 'Sin límite' }}</p>
    @endif

    @include($reportView === 'table' ? 'tasks.subtask-report-table' : 'tasks.subtask-report-list', ['subtasks' => $subtasks])
</body>
</html>
