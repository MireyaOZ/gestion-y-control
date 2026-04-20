<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Plataforma de gestion y control') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-b from-slate-100 via-white to-slate-100 font-sans text-slate-900 antialiased">
        <div class="min-h-screen">
            <header class="border-b border-[#7b0014]/10 bg-[#960018] text-white shadow-sm">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex min-h-16 items-center justify-between gap-4 py-3">
                        <div class="rounded-2xl bg-white px-4 py-2 text-sm font-black uppercase tracking-[0.35em] text-[#960018] shadow-sm">
                            Gestión Y Control
                        </div>

                        @auth
                            <nav class="flex items-center gap-3">
                                <a href="{{ url('/dashboard') }}" class="app-button-light">
                                    Dashboard
                                </a>
                            </nav>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                <section class="grid items-center gap-10 lg:grid-cols-[1.05fr_1.15fr]">
                    <div class="space-y-6">
                        <div class="inline-flex items-center rounded-full border border-[#960018]/15 bg-[#960018]/5 px-4 py-2 text-sm font-medium text-[#960018]">
                            Centro de seguimiento operativo
                        </div>

                        <div class="space-y-4">
                            <h1 class="max-w-xl text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl">
                                Plataforma de gestión y control
                            </h1>
                            <p class="max-w-2xl text-lg leading-8 text-slate-600">
                                Organiza, asigna y supervisa tareas fácilmente.
                            </p>
                            <p class="max-w-2xl text-base leading-7 text-slate-500">
                                Administra correos, sistemas y tareas desde un solo lugar con una vista clara del avance operativo y del historial de cambios.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="app-button">
                                    Ir al dashboard
                                </a>
                            @else
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="app-button">
                                        Loguearse
                                    </a>
                                @endif
                            @endauth
                        </div>

                        <div class="grid gap-4 pt-2 sm:grid-cols-3">
                            <div class="app-card p-5">
                                <div class="text-sm font-semibold text-slate-900">Tareas claras</div>
                                <p class="mt-2 text-sm leading-6 text-slate-500">Da seguimiento a pendientes y responsables en un solo flujo.</p>
                            </div>
                            <div class="app-card p-5">
                                <div class="text-sm font-semibold text-slate-900">Control operativo</div>
                                <p class="mt-2 text-sm leading-6 text-slate-500">Concentra correos, sistemas y cambios importantes.</p>
                            </div>
                            <div class="app-card p-5">
                                <div class="text-sm font-semibold text-slate-900">Historial útil</div>
                                <p class="mt-2 text-sm leading-6 text-slate-500">Consulta qué cambió, cuándo cambió y quién lo hizo.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-0 rounded-[2rem] bg-[radial-gradient(circle_at_top,_rgba(150,0,24,0.10),_transparent_52%)]"></div>
                        <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-300/40 sm:p-6">
                            <img
                                src="{{ asset('hero-gestion-control.svg') }}"
                                alt="Ilustración de gestión, planificación y control operativo"
                                class="mx-auto w-full max-w-4xl"
                            >
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
