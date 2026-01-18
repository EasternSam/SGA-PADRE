<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <div class="flex items-center gap-2">
            {{-- Botón para Volver --}}
            <a href="{{ url()->previous() }}"
               class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sga-text-light transition hover:bg-sga-bg hover:text-sga-text">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-sga-text">
                Tomar Asistencia
            </h2>
        </div>
    </x-slot>

    <!-- Mensaje de Éxito (AÑADIDO) -->
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700" role="alert">
            {{ session('message') }}
        </div>
    @endif
    <!-- Mensaje de Error (AÑADIDO) -->
    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700" role="alert">
            {{ session('error') }}
        </div>
    @endif


    <!-- --- ¡NUEVO DISEÑO DE PÁGINA! --- -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Columna Principal (Tabla) -->
        <div class="space-y-6 lg:col-span-2">

            <!-- 1. Tarjeta de Cabecera (Información del Curso) -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-sga-text">
                                {{ $section->module->course->name ?? 'N/A' }}
                            </h3>
                            <p class="mt-1 text-sm text-sga-text-light">
                                Módulo: {{ $section->module->name ?? 'N/A' }}
                            </p>
                            <p class="mt-1 text-sm text-sga-text-light">
                                Horario: {{ implode(', ', $section->days_of_week ?? []) }}, {{ \Carbon\Carbon::parse($section->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('h:i A') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 space-y-2">
                            <div>
                                <label for="attendance_date" class="block text-sm font-medium text-sga-text-light">Fecha de Asistencia</label>
                                <x-text-input id="attendance_date" type="date" class="mt-1 block w-full"
                                    wire:model.live="attendanceDate" />
                            </div>
                            
                            {{-- --- ¡¡¡BOTÓN AÑADIDO!!! --- --}}
                            <button 
                                type="button" 
                                wire:click="generateReport"
                                class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:opacity-50">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Generar Reporte
                            </button>
                            {{-- --- FIN BOTÓN AÑADIDO --- --}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Tarjeta de Contenido (Lista de Estudiantes) -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                
                <!-- Contenedor de la Tabla -->
                <div class="flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-sga-gray">
                                <thead class="bg-sga-bg">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Estudiante</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-sga-gray bg-sga-card">
                                    @forelse ($enrollments as $enrollment)
                                        <tr wire:key="enrollment-{{ $enrollment->id }}">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 flex-shrink-0">
                                                        <img class="h-10 w-10 rounded-full" 
                                                             src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr($enrollment->student->first_name, 0, 1) }}{{ substr($enrollment->student->last_name, 0, 1) }}" 
                                                             alt="Avatar de {{ $enrollment->student->first_name }}">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="font-medium text-sga-text">{{ $enrollment->student->fullName }}</div>
                                                        <div class="text-sga-text-light">{{ $enrollment->student->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <fieldset>
                                                    <legend class="sr-only">Estado de {{ $enrollment->student->first_name }}</legend>
                                                    <div class="flex items-center gap-3">
                                                        <!-- Presente -->
                                                        <div>
                                                            <input wire:model="attendanceData.{{ $enrollment->id }}" id="status-presente-{{ $enrollment->id }}" value="Presente" type="radio" class="h-4 w-4 border-sga-gray text-sga-primary focus:ring-sga-primary disabled:opacity-50 disabled:cursor-not-allowed" @if($isLocked) disabled @endif>
                                                            <label for="status-presente-{{ $enrollment->id }}" class="ml-2 text-sm font-medium text-sga-text">Presente</label>
                                                        </div>
                                                        <!-- Ausente -->
                                                        <div>
                                                            <input wire:model="attendanceData.{{ $enrollment->id }}" id="status-ausente-{{ $enrollment->id }}" value="Ausente" type="radio" class="h-4 w-4 border-sga-gray text-sga-primary focus:ring-sga-primary disabled:opacity-50 disabled:cursor-not-allowed" @if($isLocked) disabled @endif>
                                                            <label for="status-ausente-{{ $enrollment->id }}" class="ml-2 text-sm font-medium text-sga-text">Ausente</label>
                                                        </div>
                                                        <!-- Tardanza -->
                                                        <div>
                                                            <input wire:model="attendanceData.{{ $enrollment->id }}" id="status-tardanza-{{ $enrollment->id }}" value="Tardanza" type="radio" class="h-4 w-4 border-sga-gray text-sga-primary focus:ring-sga-primary disabled:opacity-50 disabled:cursor-not-allowed" @if($isLocked) disabled @endif>
                                                            <label for="status-tardanza-{{ $enrollment->id }}" class="ml-2 text-sm font-medium text-sga-text">Tardanza</label>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                                No hay estudiantes inscritos en esta sección.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Pie de la Tarjeta (Botón de Guardar) -->
                <div class="border-t border-sga-gray bg-sga-bg px-4 py-4 sm:px-6">
                    <div class="flex justify-end">
                        
                        @if($isLocked)
                            <p class="rounded-md bg-sga-warning/10 px-3 py-2 text-sm font-medium text-sga-warning">
                                La asistencia para este día ya fue guardada y está bloqueada.
                            </p>
                        @else
                            <button type="button" 
                                    wire:click="saveAttendance"
                                    wire:loading.attr="disabled"
                                    wire:target="saveAttendance"
                                    class="rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary/80 disabled:opacity-50">
                                <span wire:loading.remove wire:target="saveAttendance">
                                    Guardar Asistencia
                                </span>
                                <span wire:loading wire:target="saveAttendance">
                                    Guardando...
                                </span>
                            </button>
                        @endif

                    </div>
                </div>

            </div>
        </div>

        <!-- Columna Lateral (Pases de Lista) -->
        <div class="space-y-6 lg:col-span-1">
            
            <!-- 3. Tarjeta de Pases de Lista Completados (NUEVO) -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-sga-text">
                        Pases de Lista Completados
                    </h3>
                    <p class="mt-1 text-sm text-sga-text-light">
                        Días en los que ya se ha guardado la asistencia.
                    </p>
                </div>
                <div class="border-t border-sga-gray p-4 sm:p-6">
                    @if($this->completedDates->isEmpty())
                        <p class="text-sm text-sga-text-light">No se ha completado ningún pase de lista para esta sección.</p>
                    @else
                        <ul class="max-h-96 overflow-y-auto space-y-2">
                            @foreach($this->completedDates as $date)
                                <li wire:key="date-{{ $date->format('Y-m-d') }}" class="flex items-center justify-between rounded-md bg-sga-bg p-3">
                                    <span class="text-sm font-medium text-sga-text">
                                        {{ $date->format('d \d\e F, Y') }}
                                    </span>
                                    <button 
                                        type="button"
                                        wire:click="$set('attendanceDate', '{{ $date->format('Y-m-d') }}')"
                                        class="text-sm font-semibold text-sga-primary hover:text-sga-primary/80">
                                        Ver
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>

    </div>


    {{-- --- ¡¡¡INICIO DEL CÓDIGO AÑADIDO PARA MODAL DE PDF!!! --- --}}
    {{-- Este es el mismo modal que usas en el perfil del estudiante/profesor --}}
    <div
        x-data="{ show: false, pdfUrl: '' }"
        @open-pdf-modal.window="
            console.log('Evento open-pdf-modal recibido.');
            pdfUrl = $event.detail.url;
            console.log('URL recibida:', pdfUrl);
            show = true;
        "
        x-show="show"
        x-on:keydown.escape.window="show = false; pdfUrl = ''"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        style="display: none;"
    >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Fondo oscuro -->
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="show = false; pdfUrl = ''" aria-hidden="true"></div>

            <!-- Contenedor del Modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-6xl p-4 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg"
            >
                <!-- Encabezado del Modal -->
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        Visor de Reporte
                    </h3>
                    <button @click="show = false; pdfUrl = ''" class="text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Cerrar</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Contenido del Modal (iframe) -->
                <div class="mt-4" style="width: 100%; height: 75vh;">
                    <iframe :src="pdfUrl" frameborder="0" width="100%" height="100%">
                        Tu navegador no soporta iframes. Por favor, descarga el reporte.
                    </iframe>
                </div>

                <!-- Pie del Modal -->
                <div class="flex justify-end pt-4 mt-4 border-t">
                    <a :href="pdfUrl" download class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Descargar
                    </a>
                    <button @click="show = false; pdfUrl = ''" type="button" class="ml-3 inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- --- ¡¡¡FIN DEL CÓDIGO AÑADIDO PARA MODAL DE PDF!!! --- --}}

</div>