@props([
    'name',
    'endpoint',
    'selected' => [],
])

<div x-data="searchMultiSelect({ endpoint: '{{ $endpoint }}', selected: @js($selected) })" class="space-y-3" @click.outside="open = false">
    <div>
        <input
            type="text"
            class="app-input"
            placeholder="Buscar usuarios activos..."
            x-model="query"
            @input.debounce.300ms="search"
            @focus="open = true"
            @keydown.escape="open = false"
        >

        <div x-cloak x-show="open && results.length" class="mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-[#960018]/15 bg-white shadow-xl shadow-slate-200/70">
            <template x-for="item in results" :key="item.id">
                <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-[#960018]/5" @click="add(item)">
                    <span x-text="item.label"></span>
                    <span class="text-xs text-slate-500" x-text="item.meta"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <template x-for="item in selected" :key="item.id">
            <div class="inline-flex items-center gap-2 rounded-full border border-[#960018]/20 bg-[#960018]/5 px-3 py-2 text-sm text-[#960018]">
                <input type="hidden" name="{{ $name }}[]" :value="item.id">
                <span x-text="item.label"></span>
                <button type="button" class="text-xs uppercase tracking-[0.2em] text-[#960018]/70 transition hover:text-[#960018]" @click="remove(item.id)">Quitar</button>
            </div>
        </template>
    </div>
</div>
