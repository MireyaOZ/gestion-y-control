@php($showComments = $showComments ?? true)

<div class="grid gap-6 lg:grid-cols-2">
    <section class="app-card p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Adjuntos</h3>
                <p class="mt-1 text-sm text-slate-400">Archivos relacionados al elemento actual.</p>
            </div>
            <button class="icon-button" type="button" x-data @click="$dispatch('open-modal', 'attachment-{{ $type }}-{{ $model->id }}')">+</button>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($model->attachments as $attachment)
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 p-4">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $attachment->original_name }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ number_format($attachment->size / 1024, 1) }} KB</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a class="text-xs text-emerald-300" href="{{ route('attachments.show', $attachment) }}" target="_blank">Abrir</a>
                        <form method="POST" action="{{ route('attachments.destroy', $attachment) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-rose-300" type="submit">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-400">No hay adjuntos.</p>
            @endforelse
        </div>
    </section>

    <section class="app-card p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Links de interés</h3>
                <p class="mt-1 text-sm text-slate-400">Referencias y recursos rápidos.</p>
            </div>
            <button class="icon-button" type="button" x-data @click="$dispatch('open-modal', 'link-{{ $type }}-{{ $model->id }}')">+</button>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($model->links as $link)
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 p-4">
                    <div>
                        <a class="text-sm font-medium text-emerald-300" href="{{ $link->url }}" target="_blank">{{ $link->label }}</a>
                        <p class="mt-1 text-xs text-slate-400">{{ $link->url }}</p>
                    </div>
                    <form method="POST" action="{{ route('links.destroy', $link) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs text-rose-300" type="submit">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="mt-3 text-sm text-slate-400">No hay links.</p>
            @endforelse
        </div>
    </section>

</div>

<x-modal name="attachment-{{ $type }}-{{ $model->id }}" :show="false" maxWidth="lg">
    <div class="p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Adjuntar archivo</h3>
                <p class="mt-1 text-sm text-slate-400">Selecciona el archivo que quieres relacionar.</p>
            </div>
            <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'attachment-{{ $type }}-{{ $model->id }}')">Cerrar</button>
        </div>
        <form method="POST" action="{{ route('attachments.store', [$type, $model->id]) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <input type="file" name="file" class="block w-full text-sm text-slate-300">
            <div class="flex justify-end gap-3">
                <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'attachment-{{ $type }}-{{ $model->id }}')">Cancelar</button>
                <button class="app-button" type="submit">Adjuntar</button>
            </div>
        </form>
    </div>
</x-modal>

<x-modal name="link-{{ $type }}-{{ $model->id }}" :show="false" maxWidth="lg">
    <div class="p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Agregar link</h3>
                <p class="mt-1 text-sm text-slate-400">Guarda una referencia importante para este elemento.</p>
            </div>
            <button type="button" class="text-slate-400" x-data @click="$dispatch('close-modal', 'link-{{ $type }}-{{ $model->id }}')">Cerrar</button>
        </div>
        <form method="POST" action="{{ route('links.store', [$type, $model->id]) }}" class="mt-6 space-y-4">
            @csrf
            <input type="text" name="label" class="app-input" placeholder="Nombre del link">
            <input type="url" name="url" class="app-input" placeholder="https://...">
            <div class="flex justify-end gap-3">
                <button type="button" class="app-button-secondary" x-data @click="$dispatch('close-modal', 'link-{{ $type }}-{{ $model->id }}')">Cancelar</button>
                <button class="app-button" type="submit">Guardar link</button>
            </div>
        </form>
    </div>
</x-modal>
