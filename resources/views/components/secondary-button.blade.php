<!-- --- ¡ACTUALIZADO! --- -->
{{-- Clases rediseñadas para un botón secundario (Cancelar) limpio --}}
<button {{ $attributes->merge([
    'type' => 'button', 
    'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-sga-gray bg-sga-card px-4 py-2 text-sm font-semibold text-sga-text transition-all hover:bg-sga-bg focus:outline-none focus:ring-2 focus:ring-sga-secondary focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none'
    ]) }}>
    {{ $slot }}
</button>