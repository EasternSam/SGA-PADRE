<!-- --- ¡ACTUALIZADO! --- -->
{{-- Clases rediseñadas para usar el color 'sga-secondary' de la nueva paleta --}}
<button {{ $attributes->merge([
    'type' => 'submit', 
    'class' => 'inline-flex items-center justify-center gap-2 rounded-md border border-transparent bg-sga-secondary px-4 py-2 text-sm font-semibold text-white transition-all hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-sga-secondary focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none'
    ]) }}>
    {{ $slot }}
</button>