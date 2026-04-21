<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-white">Dashboard</h2>
            <p class="text-sm  text-white/80">Resumen de correos, sistemas y tareas activas.</p>
        </div>
    </x-slot>

    <div class="space-y-6 py-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Tareas</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $tasksCount }}</div>
            </div>
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Correos</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $emailsCount }}</div>
            </div>
            <div class="app-card p-6">
                <div class="text-sm text-slate-400">Sistemas</div>
                <div class="mt-3 text-4xl font-bold text-white">{{ $systemsCount }}</div>
            </div>
        </div>

        <section class="grid gap-6 xl:grid-cols-3">
            @php($dashboardCharts = [
                ['title' => 'Tareas por estado', 'subtitle' => 'Distribución del trabajo actual', 'data' => $taskChart],
                ['title' => 'Correos por estatus', 'subtitle' => 'Activos e inactivos según el movimiento', 'data' => $emailChart],
                ['title' => 'Sistemas por estado', 'subtitle' => 'Seguimiento del flujo operativo', 'data' => $systemChart],
            ])

            @foreach ($dashboardCharts as $chart)
                <article class="app-card p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $chart['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ $chart['subtitle'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Total</p>
                            <p class="mt-2 text-2xl font-semibold text-white">{{ $chart['data']['total'] }}</p>
                        </div>
                    </div>

                    @if ($chart['data']['items'] === [])
                        <p class="mt-6 text-sm text-slate-400">{{ $chart['data']['emptyMessage'] }}</p>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($chart['data']['items'] as $item)
                                <div>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-medium text-white">{{ $item['label'] }}</span>
                                        <span class="text-slate-400">{{ $item['count'] }} · {{ $item['percentage'] }}%</span>
                                    </div>
                                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full" style="width: {{ $item['width'] }}%; background-color: {{ $item['color'] }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            @php($monthlyCharts = [
                ['title' => 'Tareas por mes', 'subtitle' => 'Altas registradas en los ultimos 6 meses', 'data' => $taskMonthlyChart],
                ['title' => 'Correos por mes', 'subtitle' => 'Solicitudes registradas por mes', 'data' => $emailMonthlyChart],
                ['title' => 'Sistemas por mes', 'subtitle' => 'Registros capturados por mes', 'data' => $systemMonthlyChart],
            ])

            @foreach ($monthlyCharts as $chart)
                <article class="app-card p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $chart['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ $chart['subtitle'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Periodo</p>
                            <p class="mt-2 text-sm font-semibold text-white">6 meses</p>
                        </div>
                    </div>

                    @if (collect($chart['data']['items'])->sum('count') === 0)
                        <p class="mt-6 text-sm text-slate-400">{{ $chart['data']['emptyMessage'] }}</p>
                    @else
                        <div class="mt-6 space-y-4">
                            @foreach ($chart['data']['items'] as $item)
                                <div>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-medium text-white">{{ $item['label'] }}</span>
                                        <span class="text-slate-400">{{ $item['count'] }}</span>
                                    </div>
                                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full" style="width: {{ $item['width'] }}%; background-color: {{ $item['color'] }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    </div>
</x-app-layout>
