<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                
                <!-- Encabezado -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Plantillas de Certificados</h2>
                        <p class="text-sm text-gray-500">Gestiona los diseños base para los diplomas de tus cursos.</p>
                    </div>
                    <!-- CORREGIDO: Ruta en plural 'admin.certificates.editor' -->
                    <a href="{{ route('admin.certificates.editor') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                        <i class="ph-bold ph-plus mr-2 text-lg"></i> Nueva Plantilla
                    </a>
                </div>

                <!-- Buscador y Mensajes -->
                <div class="mb-6">
                    <div class="relative max-w-md">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar plantilla por nombre..." class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ph-bold ph-magnifying-glass text-gray-400"></i>
                        </div>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg shadow-sm flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="ph-bold ph-check-circle text-xl mr-2"></i>
                            {{ session('message') }}
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800"><i class="ph-bold ph-x"></i></button>
                    </div>
                @endif

                <!-- Grid de Plantillas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($templates as $template)
                        <div class="relative border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 bg-white flex flex-col group hover:-translate-y-1 cursor-pointer">
                            
                            <!-- Enlace envolvente para editar al hacer clic en cualquier parte -->
                            <a href="{{ route('admin.certificates.editor', ['templateId' => $template->id]) }}" class="absolute inset-0 z-10" title="Editar Plantilla"></a>

                            <!-- Previsualización Miniatura -->
                            <div class="h-48 bg-gray-100 w-full relative overflow-hidden flex items-center justify-center border-b border-gray-100 group-hover:bg-gray-50 transition">
                                @if($template->background_image)
                                    <img src="{{ asset('storage/' . $template->background_image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 opacity-90">
                                @else
                                    <div class="text-gray-300 flex flex-col items-center justify-center h-full w-full bg-slate-50">
                                        <i class="ph-duotone ph-certificate text-5xl mb-2 text-gray-400"></i>
                                        <span class="text-xs font-medium text-gray-400">Sin fondo personalizado</span>
                                    </div>
                                @endif
                                
                                <!-- Overlay visual -->
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3 pointer-events-none">
                                    <span class="p-2 bg-white rounded-full text-indigo-600 shadow-lg transform scale-75 group-hover:scale-100 transition duration-300">
                                        <i class="ph-bold ph-pencil-simple text-xl"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Info y Acciones -->
                            <div class="p-5 flex flex-col flex-1 relative z-20 pointer-events-none">
                                <h3 class="text-lg font-bold text-gray-900 truncate mb-1 group-hover:text-indigo-600 transition">{{ $template->name }}</h3>
                                <p class="text-xs text-gray-500 mb-4 flex items-center gap-1">
                                    <i class="ph-regular ph-calendar-blank"></i> 
                                    {{ $template->created_at->format('d M, Y') }}
                                </p>
                                
                                <div class="mt-auto flex justify-between items-center pt-4 border-t border-gray-100">
                                    <span class="text-[10px] font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded">ID: {{ $template->id }}</span>
                                    
                                    <!-- Botón Eliminar (con z-index alto para funcionar sobre el enlace principal) -->
                                    <button wire:click="delete({{ $template->id }})" 
                                            wire:confirm="¿Estás seguro de eliminar esta plantilla? Esta acción no se puede deshacer." 
                                            class="text-red-500 hover:text-red-700 text-xs font-bold flex items-center gap-1 px-2 py-1 hover:bg-red-50 rounded transition pointer-events-auto cursor-pointer relative z-30">
                                        <i class="ph-bold ph-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 flex flex-col items-center justify-center text-center bg-white border-2 border-dashed border-gray-300 rounded-xl">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i class="ph-duotone ph-files text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">No hay plantillas creadas</h3>
                            <p class="text-sm text-gray-500 max-w-sm mt-1 mb-6">Comienza creando tu primer diseño de certificado para tus estudiantes.</p>
                            <!-- CORREGIDO: Ruta en plural -->
                            <a href="{{ route('admin.certificates.editor') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-md">
                                <i class="ph-bold ph-plus mr-2"></i> Crear Primera Plantilla
                            </a>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>
</div>