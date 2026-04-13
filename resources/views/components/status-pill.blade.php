@props(['label'])

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-slate-200']) }}>
    {{ $label }}
</span>
