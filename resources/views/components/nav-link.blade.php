@props(['active'])

@php
// MEJORA: Clases adaptadas para un fondo oscuro (sga-primary)
$classes = ($active ?? false)
            // Activo: Fondo azul secundario, texto blanco, borde acento (amarillo)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-sga-accent text-start text-base font-medium text-white bg-sga-secondary focus:outline-none transition duration-150 ease-in-out'
            // Inactivo: Texto azul claro, hover azul secundario
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-blue-200 hover:text-white hover:bg-sga-secondary focus:outline-none focus:text-white focus:bg-sga-secondary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>