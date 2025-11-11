@props(['disabled' => false])

<!-- --- ¡ACTUALIZADO! --- -->
{{-- Clases rediseñadas para bordes 'sga-gray' y foco 'sga-secondary' --}}
<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge([
    'class' => 'block w-full rounded-md border-0 py-1.5 text-sga-text shadow-sm ring-1 ring-inset ring-sga-gray placeholder:text-sga-text-light focus:ring-2 focus:ring-inset focus:ring-sga-secondary sm:text-sm sm:leading-6 disabled:opacity-50 disabled:bg-sga-bg'
    ]) !!}>