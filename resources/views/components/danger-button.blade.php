<!-- --- ¡ACTUALIZADO! --- -->
{{-- Clases rediseñadas para usar 'sga-danger' y coincidir con 'primary-button' --}}
<button {{ $attributes->merge([
    'type' => 'button', 
    'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-sga-danger px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-sga-danger focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none'
    ]) }}>
    {{ $slot }}
</button>