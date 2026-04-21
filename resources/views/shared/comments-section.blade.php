<section class="app-card p-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-white">Comentarios</h3>
            <p class="mt-1 text-sm text-slate-400">Seguimiento colaborativo con editor enriquecido.</p>
        </div>
        @can('viewComments', $model)
            <x-status-pill :label="$model->comments->count().' comentarios'" />
        @endcan
    </div>

    @can('comment', $model)
        <form method="POST" action="{{ route('comments.store', [$type, $model->id]) }}" class="mt-6 space-y-4">
            @csrf
            <input id="{{ $type }}-comment-content" type="hidden" name="content">
            <trix-editor input="{{ $type }}-comment-content" class="trix-content text-slate-900"></trix-editor>
            <div class="flex justify-end">
                <button class="app-button" type="submit">Agregar comentario</button>
            </div>
        </form>
    @endcan

    @can('viewComments', $model)
        <div class="mt-6 space-y-4">
            @forelse ($model->comments as $comment)
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-sm font-medium text-white">{{ $comment->author->name }}</div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $comment->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="prose max-w-none text-slate-900">{!! $comment->content !!}</div>
                </div>
            @empty
                <p class="text-sm text-slate-400">No hay comentarios todavía.</p>
            @endforelse
        </div>
    @endcan
</section>
