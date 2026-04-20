@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-4">
        <div class="text-sm text-slate-500">
            Mostrando {{ $paginator->firstItem() ?? 0 }} a {{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }} resultados
        </div>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-md bg-[#960018]/30 px-3 text-sm font-semibold text-white/70">&lsaquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-md bg-[#960018] px-3 text-sm font-semibold text-white transition hover:bg-[#7c0014]" rel="prev" aria-label="{{ __('pagination.previous') }}">&lsaquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border border-slate-200 px-3 text-sm font-semibold text-slate-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-md bg-[#960018] px-3 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-md border border-[#960018]/20 bg-white px-3 text-sm font-semibold text-[#960018] transition hover:bg-[#960018]/5" aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-md bg-[#960018] px-3 text-sm font-semibold text-white transition hover:bg-[#7c0014]" rel="next" aria-label="{{ __('pagination.next') }}">&rsaquo;</a>
            @else
                <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-md bg-[#960018]/30 px-3 text-sm font-semibold text-white/70">&rsaquo;</span>
            @endif
        </div>
    </nav>
@endif
