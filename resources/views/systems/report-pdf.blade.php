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
    </style>
</head>
<body>
    <h1>{{ $reportTitle }}</h1>

    <div class="meta">
        <p><strong>Fecha de generación:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>
        @if ($search !== '')
            <p><strong>Búsqueda aplicada:</strong> {{ $search }}</p>
        @endif
    </div>

    @include('systems.report-table', ['systems' => $systems])
</body>
</html>
