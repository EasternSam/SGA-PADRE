<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <div class="flex items-center gap-4">
            {{-- Botón para Volver --}}
            <a href="{{ url()->previous() }}"
               class="group inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm ring-1 ring-gray-200 transition-all hover:bg-indigo-50 hover:text-indigo-600 hover:ring-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <svg class="h-5 w-5 transition-transform group-hover:-translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold leading-tight text-gray-900">
                    Tomar Asistencia
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Gestiona la presencia de tus estudiantes para esta clase.</p>
            </div>
        </div>
    </x-slot>

    <!-- Mensajes de Estado -->
    <div class="mb-6 space-y-2">
        @if (session()->has('message'))
            <div class="rounded-xl bg-green-50 border border-green-100 p-4 flex items-start gap-3 shadow-sm">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm font-medium text-green-800">{{ session('message') }}</div>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="rounded-xl bg-red-50 border border-red-100 p-4 flex items-start gap-3 shadow-sm">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm font-medium text-red-800">{{ session('error') }}</div>
            </div>
        @endif
    </div>

    <!-- --- DISEÑO DE PÁGINA REDISEÑADO --- -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

        <!-- Columna Principal (Detalles y Tabla) -->
        <div class="space-y-8 lg:col-span-2">

            <!-- 1. Tarjeta de Cabecera (Información del Curso) -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="relative bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-8 sm:px-10">
                     <!-- Decoración -->
                     <div class="absolute top-0 right-0 -mt-6 -mr-6 h-32 w-32 rounded-full bg-white opacity-10 blur-2xl"></div>
                     <div class="absolute bottom-0 left-0 -mb-6 -ml-6 h-32 w-32 rounded-full bg-indigo-400 opacity-20 blur-2xl"></div>
                     
                     <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                        <div class="text-white">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center rounded-md bg-indigo-400/20 px-2 py-1 text-xs font-medium text-indigo-50 ring-1 ring-inset ring-indigo-400/30">
                                    {{ $section->module->name ?? 'N/A' }}
                                </span>
                            </div>
                            <h3 class="text-2xl font-bold tracking-tight">
                                {{ $section->module->course->name ?? 'Curso No Asignado' }}
                            </h3>
                            <div class="mt-2 flex items-center text-indigo-100 text-sm gap-4">
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    {{ implode(', ', $section->days_of_week ?? []) }}
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($section->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                     </div>
                </div>

                <!-- Controles de Fecha y Reporte -->
                <div class="bg-gray-50/50 px-6 py-5 sm:px-10 border-t border-gray-100">
                    <div class="flex flex-col sm:flex-row gap-4 items-end sm:items-center justify-between">
                         <div class="w-full sm:w-auto flex-1 max-w-xs">
                            <label for="attendance_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Fecha de Clase</label>
                            <div class="relative">
                                <input id="attendance_date" type="date" 
                                    class="block w-full rounded-lg border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors cursor-pointer hover:bg-gray-50"
                                    wire:model.live="attendanceDate" />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            </div>
                        </div>
                        
                        <button 
                            type="button" 
                            wire:click="generateReport"
                            class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none">
                            <svg class="mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            Reporte PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Lista de Estudiantes -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Listado de Estudiantes</h3>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                        {{ count($enrollments) }} alumnos
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold uppercase tracking-wider">Estudiante</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Estado de Asistencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @forelse ($enrollments as $enrollment)
                                <tr wire:key="enrollment-{{ $enrollment->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full object-cover shadow-sm ring-2 ring-white" 
                                                     src="https://ui-avatars.com/api/?name={{ urlencode($enrollment->student->fullName) }}&color=7F9CF5&background=EBF4FF&bold=true" 
                                                     alt="{{ $enrollment->student->fullName }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-gray-900">{{ $enrollment->student->fullName }}</div>
                                                <div class="text-xs text-gray-500">{{ $enrollment->student->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <!-- Opción Presente -->
                                            <label class="group relative cursor-pointer">
                                                <input type="radio" wire:model="attendanceData.{{ $enrollment->id }}" value="Presente" class="peer sr-only" @if($isLocked) disabled @endif>
                                                <span class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-all
                                                    peer-checked:bg-green-100 peer-checked:text-green-700 peer-checked:ring-1 peer-checked:ring-green-500/20
                                                    bg-gray-50 text-gray-500 hover:bg-gray-100 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-current transition-colors opacity-40 peer-checked:opacity-100"></span>
                                                    Presente
                                                </span>
                                            </label>

                                            <!-- Opción Ausente -->
                                            <label class="group relative cursor-pointer">
                                                <input type="radio" wire:model="attendanceData.{{ $enrollment->id }}" value="Ausente" class="peer sr-only" @if($isLocked) disabled @endif>
                                                <span class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-all
                                                    peer-checked:bg-red-100 peer-checked:text-red-700 peer-checked:ring-1 peer-checked:ring-red-500/20
                                                    bg-gray-50 text-gray-500 hover:bg-gray-100 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-current transition-colors opacity-40 peer-checked:opacity-100"></span>
                                                    Ausente
                                                </span>
                                            </label>

                                            <!-- Opción Tardanza -->
                                            <label class="group relative cursor-pointer">
                                                <input type="radio" wire:model="attendanceData.{{ $enrollment->id }}" value="Tardanza" class="peer sr-only" @if($isLocked) disabled @endif>
                                                <span class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-all
                                                    peer-checked:bg-yellow-100 peer-checked:text-yellow-700 peer-checked:ring-1 peer-checked:ring-yellow-500/20
                                                    bg-gray-50 text-gray-500 hover:bg-gray-100 peer-disabled:opacity-50 peer-disabled:cursor-not-allowed">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-current transition-colors opacity-40 peer-checked:opacity-100"></span>
                                                    Tardanza
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.971 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-sm font-semibold text-gray-900">Sin estudiantes</h3>
                                            <p class="mt-1 text-sm text-gray-500">No hay estudiantes inscritos en esta sección.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pie de la Tarjeta (Acciones) -->
                <div class="border-t border-gray-100 bg-gray-50 px-6 py-4 flex items-center justify-end">
                    @if($isLocked)
                        <div class="flex items-center gap-2 rounded-lg bg-amber-50 px-4 py-2.5 text-sm font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20">
                            <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                            Asistencia guardada y bloqueada para hoy.
                        </div>
                    @else
                        <button type="button" 
                                wire:click="saveAttendance"
                                wire:loading.attr="disabled"
                                wire:target="saveAttendance"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="saveAttendance" class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Guardar Asistencia
                            </span>
                            <span wire:loading wire:target="saveAttendance" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Guardando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>

        </div>

        <!-- Columna Lateral (Historial) -->
        <div class="space-y-6 lg:col-span-1">
            
            <!-- 3. Tarjeta de Historial -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">
                        Historial de Pases
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Registro de días completados.
                    </p>
                </div>
                
                <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                    @if($this->completedDates->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 px-6 text-center">
                            <div class="rounded-full bg-gray-50 p-3 mb-3">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-900">Sin registros</p>
                            <p class="mt-1 text-xs text-gray-500">Aún no has tomado asistencia para esta sección.</p>
                        </div>
                    @else
                        <ul role="list" class="divide-y divide-gray-100">
                            @foreach($this->completedDates as $date)
                                <li wire:key="date-{{ $date->format('Y-m-d') }}" class="group relative flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100 transition-colors">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-900">
                                                {{ $date->format('d \d\e F') }}
                                            </span>
                                            <span class="text-xs text-gray-500">{{ $date->year }}</span>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        wire:click="$set('attendanceDate', '{{ $date->format('Y-m-d') }}')"
                                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all">
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

    {{-- --- MODAL DE PDF (ESTILOS MEJORADOS) --- --}}
    <div
        x-data="{ show: false, pdfUrl: '' }"
        @open-pdf-modal.window="
            console.log('Evento open-pdf-modal recibido.');
            pdfUrl = $event.detail.url;
            show = true;
        "
        x-show="show"
        x-on:keydown.escape.window="show = false; pdfUrl = ''"
        class="relative z-[100]"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" 
             @click="show = false; pdfUrl = ''" 
             aria-hidden="true"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                
                <!-- Panel del Modal -->
                <div
                    x-show="show"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-6xl border border-gray-200"
                >
                    <!-- Header Modal -->
                    <div class="bg-white px-4 py-4 sm:px-6 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-red-50 p-2">
                                <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">
                                Vista Previa del Reporte
                            </h3>
                        </div>
                        <button @click="show = false; pdfUrl = ''" class="rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 transition-colors">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body Modal -->
                    <div class="bg-gray-50 p-0">
                        <div class="relative w-full h-[75vh]">
                            <iframe :src="pdfUrl" class="absolute inset-0 w-full h-full border-0" allowfullscreen>
                                <div class="flex h-full flex-col items-center justify-center text-center">
                                    <p class="text-sm text-gray-500">Tu navegador no soporta la visualización de PDFs.</p>
                                    <a :href="pdfUrl" class="mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-500">Descargar archivo</a>
                                </div>
                            </iframe>
                        </div>
                    </div>

                    <!-- Footer Modal -->
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200">
                        <a :href="pdfUrl" download class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition-colors">
                            <svg class="-ml-0.5 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Descargar PDF
                        </a>
                        <button type="button" @click="show = false; pdfUrl = ''" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>