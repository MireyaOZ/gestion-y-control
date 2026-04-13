<section class="app-card p-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-white">Historial de cambios</h3>
            <p class="mt-1 text-sm text-slate-400">Consulta el detalle completo en un diálogo.</p>
        </div>
        <button class="app-button-secondary" type="button" x-data @click="$dispatch('open-modal', '{{ $modalName }}')">
            Ver historial
        </button>
    </div>
</section>

<x-modal :name="$modalName" :show="false" maxWidth="2xl">
    <div class="p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Historial de cambios</h3>
                <p class="mt-1 text-sm text-slate-400">Registro cronológico de actividad.</p>
            </div>
            <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', '{{ $modalName }}')">Cerrar</button>
        </div>

        <div class="mt-6 max-h-[70vh] space-y-3 overflow-y-auto pr-2">
            @forelse ($items as $log)
                <div class="rounded-2xl border border-white/10 p-4">
                    <div class="mb-2 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $log->action }} · {{ optional($log->author)->name ?? 'Sistema' }} · {{ $log->created_at->format('d/m/Y H:i') }}</div>
                    <div class="prose prose-invert max-w-none">{!! $log->content !!}</div>
                </div>
            @empty
                <p class="text-sm text-slate-400">No hay cambios registrados.</p>
            @endforelse
        </div>
    </div>
</x-modal>
