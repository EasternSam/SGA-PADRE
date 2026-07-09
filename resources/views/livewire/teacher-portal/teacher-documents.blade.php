<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
    <!-- Alertas -->
    @if ($successMessage || $errorMessage)
        <div class="space-y-2">
            @if ($successMessage)
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm flex items-center justify-between animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $successMessage }}</span>
                    </div>
                    <button wire:click="resetMessages" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
            @if ($errorMessage)
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm flex items-center justify-between animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ $errorMessage }}</span>
                    </div>
                    <button wire:click="resetMessages" class="text-rose-500 hover:text-rose-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Banner Superior -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_120%,rgba(99,102,241,0.15),transparent)] pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 text-xs font-semibold uppercase tracking-wider rounded-full border border-indigo-500/30">
                    Docente DMS
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-200">
                    Repositorio de Materiales Académicos
                </h1>
                <p class="text-slate-400 mt-2 text-sm sm:text-base">
                    Sube programas de clases, guías de estudio y recursos multimedia para tus estudiantes.
                </p>
            </div>
        </div>
    </div>

    <!-- Contenido en 2 Columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Formulario de Subida -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Compartir Documento</h3>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <x-input-label for="name" value="Nombre del Documento" />
                        <input type="text" id="name" wire:model="name" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800" placeholder="Ej: Silabario de Matemática I" />
                        @error('name') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sección/Curso -->
                    <div>
                        <x-input-label for="schedule" value="Asignar a Sección / Clase" />
                        <select id="schedule" wire:model="selectedScheduleId" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-semibold text-slate-800">
                            <option value="">Selecciona una sección...</option>
                            @foreach ($activeSchedules as $sched)
                                <option value="{{ $sched->id }}">{{ $sched->module->name }} (Sec: {{ $sched->section_name }})</option>
                            @endforeach
                        </select>
                        @error('selectedScheduleId') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Archivo -->
                    <div>
                        <x-input-label value="Archivo" />
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-lg hover:border-indigo-400 transition cursor-pointer relative">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-xs text-slate-500">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-bold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Seleccionar archivo</span>
                                        <input id="file-upload" type="file" wire:model="file" class="sr-only">
                                    </label>
                                </div>
                                <p class="text-[10px] text-slate-400">PDF, Word, Excel, PPT, Imágenes de hasta 10MB</p>
                            </div>
                        </div>
                        @error('file') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
                        @if ($file)
                            <div class="mt-2 text-xs text-slate-600 bg-slate-50 p-2 rounded border border-slate-100 flex items-center justify-between font-semibold">
                                <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                                <button type="button" wire:click="$set('file', null)" class="text-rose-500 hover:text-rose-700">Quitar</button>
                            </div>
                        @endif
                    </div>

                    <!-- Botón Enviar -->
                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-lg shadow transition flex items-center justify-center space-x-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Subir y Publicar</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Columna Derecha: Tabla de Documentos -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Mis Documentos Publicados</h3>
                    <span class="px-2 py-0.5 bg-slate-200 text-slate-700 text-xs font-semibold rounded-full">{{ count($uploadedDocuments) }} archivos</span>
                </div>

                @if ($uploadedDocuments->isEmpty())
                    <div class="p-12 text-center text-slate-400 space-y-4">
                        <div class="inline-flex p-4 rounded-full bg-slate-50 text-slate-400">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold">No has subido ningún documento académico todavía.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Sección / Clase</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Tamaño / Tipo</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($uploadedDocuments as $doc)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-slate-800">{{ $doc->name }}</div>
                                            <div class="text-xs text-slate-400 font-semibold mt-1">Subido el {{ $doc->created_at->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-700">{{ $doc->module->name }}</div>
                                            @if ($doc->courseSchedule)
                                                <div class="text-xs text-indigo-600 font-semibold">Sec: {{ $doc->courseSchedule->section_name }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-slate-500 font-semibold">
                                            <span class="uppercase font-bold text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded mr-1">{{ $doc->file_type }}</span>
                                            {{ round($doc->file_size / 1024) }} KB
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ $doc->file_path }}" target="_blank" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Descargar/Ver">
                                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
                                                <button wire:click="deleteDocument({{ $doc->id }})" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Eliminar">
                                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
