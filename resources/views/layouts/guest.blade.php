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
    <body class="bg-white font-sans antialiased text-slate-900">
        <div class="min-h-screen bg-gradient-to-b from-slate-100 via-white to-slate-100">
            <header class="border-b border-[#7b0014]/10 bg-[#960018] text-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center">
                        <div class="rounded-2xl bg-white px-3 py-2 text-sm font-black uppercase tracking-[0.35em] text-[#960018] shadow-sm">
                            Gestión Y Control
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto flex min-h-[calc(100vh-73px)] max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl shadow-[#960018]/10 sm:p-10">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
