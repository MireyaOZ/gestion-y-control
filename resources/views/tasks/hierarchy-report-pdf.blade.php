<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1 { margin: 0 0 14px; font-size: 24px; color: #960018; }
        .meta { margin-bottom: 20px; }
        .meta p { margin: 4px 0; }
        .task-box {
            margin-bottom: 20px;
            padding: 16px 18px;
            border: 1px solid #e2e8f0;
            border-left: 6px solid #960018;
            border-radius: 14px;
            background: #fff7f7;
        }
        .task-box h2 {
            margin: 0 0 8px;
            font-size: 18px;
            color: #111827;
        }
        .task-box p { margin: 4px 0; color: #475569; }
        .tree,
        .tree ul {
            list-style: none;
            margin: 0;
            padding-left: 22px;
        }
        .tree > li { padding-left: 0; }
        .tree ul {
            border-left: 1px solid #cbd5e1;
            margin-left: 10px;
        }
        .tree li {
            position: relative;
            margin: 12px 0;
        }
        .tree li::before {
            content: '';
            position: absolute;
            left: -22px;
            top: 18px;
            width: 22px;
            border-top: 1px solid #cbd5e1;
        }
        .node {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
        }
        .node-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .node-meta {
            font-size: 11px;
            color: #64748b;
        }
        .empty {
            padding: 14px 16px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <h1>{{ $reportTitle }}</h1>

    <div class="meta">
        <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y') }}</p>
    </div>

    <div class="task-box">
        <h2>{{ $task->title }}</h2>
        <p><strong>Creada:</strong> {{ $task->created_at->format('d/m/Y') }}</p>
        <p><strong>Creador:</strong> {{ $task->creator?->name ?? 'Sin autor' }}</p>
        <p><strong>Subtareas registradas:</strong> {{ $task->rootSubtasks->count() }}</p>
    </div>

    @if ($task->rootSubtasks->isNotEmpty())
        <ul class="tree">
            @foreach ($task->rootSubtasks as $subtask)
                @include('tasks.hierarchy-report-node', ['subtask' => $subtask])
            @endforeach
        </ul>
    @else
        <div class="empty">Esta tarea no tiene subtareas registradas.</div>
    @endif
</body>
</html>