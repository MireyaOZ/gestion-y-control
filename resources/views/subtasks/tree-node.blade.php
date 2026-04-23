@php
    $level = $level ?? 0;
    $childSubtasks = $subtask->childSubtasksRecursive;
    $leftOffset = min($level * 16, 64);
    $childCount = $childSubtasks->count();
    $hasChildren = $childCount > 0;
@endphp

<div x-data="{ open: false }" class="min-w-[56rem] space-y-3" @if($leftOffset > 0) style="margin-left: {{ $leftOffset }}px;" @endif>
    <div class="rounded-2xl border border-white/10 p-4 transition hover:bg-white/5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($hasChildren)
                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:border-[#960018]/20 hover:bg-[#960018]/5 hover:text-[#960018]"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                        >
                            <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-90 text-[#960018]' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L10.94 10 7.23 6.29a.75.75 0 1 1 1.06-1.06l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.08-.02Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">
                            {{ $level + 1 }}
                        </span>
                    @endif

                    <a href="{{ route('subtasks.show', $subtask) }}" class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-medium text-white">{{ $subtask->title }}</p>
                            @if ($hasChildren)
                                <span class="rounded-full border border-slate-200 px-2 py-1 text-[11px] uppercase tracking-[0.18em] text-slate-500">
                                    {{ $childCount }} {{ \Illuminate\Support\Str::plural('subtarea', $childCount) }}
                                </span>
                            @endif
                        </div>
                    </a>
                </div>

                <div class="mt-2 pl-10">
                    <p class="text-sm text-slate-400">Vencimiento: {{ optional($subtask->due_date)->format('d/m/Y') ?: 'Sin vencimiento' }}</p>
                    @if ($subtask->is_overdue)
                        <p class="mt-1 text-sm font-semibold text-rose-300">Vencida hace {{ $subtask->overdue_days }} {{ \Illuminate\Support\Str::plural('día', $subtask->overdue_days) }}</p>
                    @elseif ($subtask->due_date?->isToday())
                        <p class="mt-1 text-sm font-semibold text-amber-300">Vence hoy</p>
                    @elseif ($subtask->due_date?->isTomorrow())
                        <p class="mt-1 text-sm font-semibold text-sky-300">Vence mañana</p>
                    @endif
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $subtask->assignees->isNotEmpty() ? 'Asignado a: '.$subtask->assignees->pluck('name')->join(', ') : 'Sin usuario asignado' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-status-pill :label="$subtask->status->name" :tone="$subtask->status->slug" />
                <x-status-pill :label="$subtask->priority->name" />
            </div>
        </div>
    </div>

    @if ($hasChildren)
        <div x-cloak x-show="open" x-transition.opacity.duration.200ms class="w-max min-w-full space-y-3 border-l border-slate-200/80 pl-4">
            @foreach ($childSubtasks as $childSubtask)
                @include('subtasks.tree-node', ['subtask' => $childSubtask, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>