@props(['active'])

@php
// --- ¡ACTUALIZADO! ---
// Clases rediseñadas para el nuevo Sidebar (fondo azul 'sga-primary')

$baseClasses = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-150 ease-in-out';

$activeClasses = 'bg-sga-secondary text-white'; // Azul brillante (activo)

$inactiveClasses = 'text-blue-100 hover:bg-sga-secondary hover:text-white'; // Azul pálido (inactivo)

$classes = $baseClasses . ' ' . ($active ? $activeClasses : $inactiveClasses);
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>