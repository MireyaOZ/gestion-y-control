@props(['label'])

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border border-[#960018]/15 bg-[#960018]/5 px-3 py-1 text-xs font-medium text-[#960018]']) }}>
    {{ $label }}
</span>
