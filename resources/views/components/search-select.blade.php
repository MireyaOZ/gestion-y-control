@props([
    'name',
    'endpoint',
    'selectedId' => null,
    'selectedLabel' => '',
    'placeholder' => 'Buscar...',
    'allowClear' => true,
])

<div
    x-data="searchSelect({
        endpoint: '{{ $endpoint }}',
        selectedId: {{ $selectedId ? (int) $selectedId : 'null' }},
        selectedLabel: @js($selectedLabel),
        placeholder: @js($placeholder)
    })"
    class="space-y-3"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId">

    <template x-if="selectedLabel">
        <div class="flex items-center justify-between rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            <div x-text="selectedLabel"></div>
            @if ($allowClear)
                <button type="button" class="text-xs font-semibold uppercase tracking-[0.2em]" @click="clear()">Quitar</button>
            @endif
        </div>
    </template>

    <div class="relative">
        <input
            type="text"
            class="app-input"
            :placeholder="placeholder"
            x-model="query"
            @input.debounce.300ms="search"
            @focus="open = true"
        >

        <div x-show="open && results.length" class="absolute z-20 mt-2 w-full overflow-hidden rounded-2xl border border-white/10 bg-slate-900 shadow-xl">
            <template x-for="item in results" :key="item.id">
                <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-100 transition hover:bg-white/5" @click="choose(item)">
                    <span x-text="item.label"></span>
                    <span class="text-xs text-slate-400" x-text="item.meta"></span>
                </button>
            </template>
        </div>
    </div>
</div>
