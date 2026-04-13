@props([
    'name',
    'endpoint',
    'selected' => [],
])

<div x-data="searchMultiSelect({ endpoint: '{{ $endpoint }}', selected: @js($selected) })" class="space-y-3">
    <div class="relative">
        <input
            type="text"
            class="app-input"
            placeholder="Buscar usuarios activos..."
            x-model="query"
            @input.debounce.300ms="search"
            @focus="open = true"
        >

        <div x-show="open && results.length" class="absolute z-20 mt-2 w-full overflow-hidden rounded-2xl border border-white/10 bg-slate-900 shadow-xl">
            <template x-for="item in results" :key="item.id">
                <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-100 transition hover:bg-white/5" @click="add(item)">
                    <span x-text="item.label"></span>
                    <span class="text-xs text-slate-400" x-text="item.meta"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <template x-for="item in selected" :key="item.id">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-100">
                <input type="hidden" name="{{ $name }}[]" :value="item.id">
                <span x-text="item.label"></span>
                <button type="button" class="text-xs uppercase tracking-[0.2em] text-slate-400" @click="remove(item.id)">Quitar</button>
            </div>
        </template>
    </div>
</div>
