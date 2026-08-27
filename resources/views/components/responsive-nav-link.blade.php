@props(['active'])

@php
$baseClasses = 'group flex items-center gap-2.5 rounded-xl px-3 py-2 text-[13px] font-medium transition-all duration-200 ease-out select-none';

$activeClasses = 'bg-white/20 text-white font-semibold shadow-[0_2px_8px_rgba(0,0,0,0.12)] backdrop-blur-md border border-white/20';

$inactiveClasses = 'text-white/70 hover:text-white hover:bg-white/[0.09] hover:translate-x-0.5';

$classes = $baseClasses . ' ' . ($active ? $activeClasses : $inactiveClasses);
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
