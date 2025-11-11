@props(['on'])

<!-- --- ¡CORRECCIÓN! --- -->
{{-- Se envuelve el listener en un 'if (typeof $wire !== "undefined")' --}}
{{-- Esto previene el error 'Alpine Expression Error' en páginas que no son Livewire --}}
<div x-data="{ shown: false, timeout: null }"
     x-init="if (typeof $wire !== 'undefined') { $wire.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); }) }"
     x-show="shown"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter="transition ease-in-out duration-150"
     x-transition:leave="transition ease-in-out duration-150"
     x-transition:leave-end="opacity-0 scale-95"
     style="display: none;"
    {{ $attributes->merge(['class' => 'text-sm text-sga-text-light']) }}>
    {{ $slot->isEmpty() ? 'Saved.' : $slot }}
</div>