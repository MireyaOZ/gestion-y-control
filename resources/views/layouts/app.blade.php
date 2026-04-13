<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gestion y Seguimiento') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(52,211,153,0.18),_transparent_30%),linear-gradient(135deg,_#020617,_#0f172a_45%,_#111827)]">
            @include('layouts.navigation')

            @isset($header)
                <header class="relative z-10 border-b border-white/10 bg-slate-950/40 backdrop-blur">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <x-flash-status />
                </div>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
