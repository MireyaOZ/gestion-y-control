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
        <div class="flex items-center justify-between rounded-2xl border border-[#960018]/20 bg-[#960018]/5 px-4 py-3 text-sm text-[#960018]">
            <div x-text="selectedLabel"></div>
            @if ($allowClear)
                <button type="button" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#960018]/70 transition hover:text-[#960018]" @click="clear()">Quitar</button>
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

        <div x-cloak x-show="open && results.length" class="absolute z-20 mt-2 max-h-56 w-full overflow-auto rounded-2xl border border-[#960018]/15 bg-white shadow-xl shadow-slate-200/70">
            <template x-for="item in results" :key="item.id">
                <button type="button" class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-[#960018]/5" @click="choose(item)">
                    <span x-text="item.label"></span>
                    <span class="text-xs text-slate-500" x-text="item.meta"></span>
                </button>
            </template>
        </div>
    </div>
</div>
