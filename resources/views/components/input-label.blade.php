@props(['value'])

<!-- --- ¡ACTUALIZADO! --- -->
{{-- Clases de texto actualizadas a 'sga-text' --}}
<label {{ $attributes->merge(['class' => 'block text-sm font-medium leading-6 text-sga-text']) }}>
    {{ $value ?? $slot }}
</label>