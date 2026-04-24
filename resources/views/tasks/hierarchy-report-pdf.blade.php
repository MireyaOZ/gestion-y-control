<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1 { margin: 0 0 10px; font-size: 24px; color: #960018; }
        .meta { margin-bottom: 12px; }
        .meta p { margin: 4px 0; }
        .task-box {
            margin-bottom: 16px;
            padding: 0 0 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .task-box h2 {
            margin: 0 0 6px;
            font-size: 18px;
            color: #111827;
        }
        .task-summary {
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }
        .tree,
        .tree ul {
            list-style: none;
            margin: 0;
            padding-left: 0;
        }
        .tree ul {
            margin-left: 12px;
            padding-left: 14px;
            border-left: 1px solid #dbe4f0;
        }
        .tree li {
            margin: 8px 0;
        }
        .tree li::before {
            content: '';
            display: inline-block;
            width: 10px;
            margin-right: 8px;
            transform: translateY(-2px);
            border-top: 1px solid #dbe4f0;
        }
        .tree > li::before {
            display: none;
        }
        .node {
            display: inline-block;
            vertical-align: top;
            width: 100%;
            box-sizing: border-box;
            padding: 3px 0;
        }
        .node-header {
            margin-bottom: 2px;
        }
        .node-title {
            display: inline;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .node-meta {
            font-size: 11px;
            color: #64748b;
            word-break: break-word;
            overflow-wrap: anywhere;
            margin-top: 2px;
            line-height: 1.45;
        }
        .status-badge {
            display: inline-block;
            margin-left: 8px;
            padding: 1px 6px;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .status-badge--complete {
            background: #ecfdf3;
            border-color: #86efac;
            color: #166534;
        }
        .status-badge--pending {
            background: #fff7ed;
            border-color: #fdba74;
            color: #c2410c;
        }
        .assignee-list {
            font-size: 11px;
            color: #475569;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.45;
        }
        .empty {
            padding: 10px 12px;
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
        <div class="task-summary">
            <strong>Creada:</strong> {{ $task->created_at->format('d/m/Y') }}
            <span> | </span>
            <strong>Creador:</strong> {{ $task->creator?->name ?? 'Sin autor' }}
            <span> | </span>
            <strong>Asignados:</strong> {{ $task->assignees->isNotEmpty() ? $task->assignees->pluck('name')->join(', ') : 'Sin asignados' }}
            <span> | </span>
            <strong>Estado:</strong> {{ $task->status?->name ?? 'Sin estado' }}
            <span> | </span>
            <strong>Subtareas:</strong> {{ $task->rootSubtasks->count() }}
            <span> | </span>
            <strong>Avance general:</strong> {{ $task->subtasks_progress_percentage }}%
        </div>
    </div>

    @if ($task->rootSubtasks->isNotEmpty())
        @if (($reportView ?? 'list') === 'table')
            @include('tasks.hierarchy-report-table', ['rows' => $hierarchyRows])
        @else
            <ul class="tree">
                @foreach ($task->rootSubtasks as $subtask)
                    @include('tasks.hierarchy-report-node', ['subtask' => $subtask])
                @endforeach
            </ul>
        @endif
    @else
        <div class="empty">Esta tarea no tiene subtareas registradas.</div>
    @endif
</body>
</html>