<x-app-layout>
    <div class="relative min-h-[80vh] flex items-center justify-center p-4">
        
        <!-- Fondo con patrón sutil -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNlMmU4ZjAiLz48L3N2Zz4=')] opacity-20"></div>
        </div>

        <!-- Tarjeta de Bloqueo (Glassmorphism) -->
        <div class="relative w-full max-w-lg bg-white/80 backdrop-blur-xl border border-white/50 shadow-2xl rounded-2xl p-8 text-center ring-1 ring-gray-900/5">
            
            <!-- Icono de Candado Animado -->
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50 mb-6 group hover:scale-110 transition-transform duration-300">
                <svg class="h-10 w-10 text-indigo-600 group-hover:text-indigo-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold tracking-tight text-gray-900 mb-2">
                Acceso Restringido
            </h2>
            
            <p class="text-lg text-gray-500 mb-8 leading-relaxed">
                Esta sección <span class="font-semibold text-indigo-600">no está incluida</span> en tu plan actual.
                <br>
                Para acceder a estas funciones avanzadas, por favor contacta con soporte para un upgrade.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver al Inicio
                </a>
                
                <a href="mailto:soporte@90s.agency?subject=Solicitud%20de%20Upgrade%20SaaS&body=Hola,%20quisiera%20activar%20el%20módulo..." class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg hover:shadow-indigo-500/30 transition-all">
                    Solicitar Activación
                </a>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200/60">
                <p class="text-xs text-gray-400 uppercase tracking-widest">
                    Academic+ Security Shield
                </p>
            </div>
        </div>
    </div>
</x-app-layout>