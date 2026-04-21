@props(['label', 'tone' => null])

@php
    $resolvedTone = $tone;

    if ($resolvedTone === null) {
        $resolvedTone = match (
            \Illuminate\Support\Str::of((string) $label)
                ->lower()
                ->ascii()
                ->replace(' ', '-')
                ->toString()
        ) {
            'baja' => 'priority-low',
            'media' => 'priority-medium',
            'alta' => 'priority-high',
            'urgente' => 'priority-urgent',
            default => null,
        };
    }

    $toneStyles = match ($resolvedTone) {
        'pendiente' => 'border-color:#f9a8d4;background-color:#fdf2f8;color:#be185d;',
        'en-progreso' => 'border-color:#fcd34d;background-color:#fef9c3;color:#a16207;',
        'completada' => 'border-color:#86efac;background-color:#dcfce7;color:#15803d;',
        'rechazado' => 'border-color:#fca5a5;background-color:#fee2e2;color:#b91c1c;',
        'cancelada' => 'border-color:#cbd5e1;background-color:#f8fafc;color:#475569;',
        'priority-low' => 'border-color:#86efac;background-color:#dcfce7;color:#15803d;',
        'priority-medium' => 'border-color:#fcd34d;background-color:#fef3c7;color:#a16207;',
        'priority-high' => 'border-color:#fca5a5;background-color:#fee2e2;color:#b91c1c;',
        'priority-urgent' => 'border-color:#7f1d1d;background-color:#b91c1c;color:#ffffff;',
        default => 'border-color:rgba(150,0,24,.15);background-color:rgba(150,0,24,.05);color:#960018;',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border px-3 py-1 text-xs font-medium', 'style' => $toneStyles]) }}>
    {{ $label }}
</span>
