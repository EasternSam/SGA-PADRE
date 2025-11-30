<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Reportes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Mensajes de Éxito --}}
                    @if (session()->has('message'))
                        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 text-green-700">
                            <div class="flex">
                                <div class="py-1"><svg class="h-6 w-6 text-green-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                <div><p>{{ session('message') }}</p></div>
                            </div>
                        </div>
                    @endif

                    {{-- 1. SELECCIÓN DE TIPO DE REPORTE (Pestañas Superiores) --}}
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('Seleccione el Tipo de Reporte') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'attendance' => 'Asistencia',
                                'grades' => 'Calificaciones',
                                'payments' => 'Pagos y Deudas',
                                'students' => 'Estudiantes',
                                'calendar' => 'Calendario',
                                'assignments' => 'Cargas Académicas'
                            ] as $key => $label)
                                <button 
                                    wire:click="$set('reportType', '{{ $key }}')" 
                                    class="px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 border 
                                    {{ $reportType === $key 
                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' 
                                        : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50 hover:text-gray-900' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. ÁREA DE FILTROS (Panel Gris) --}}
                    <div class="bg-gray-50 rounded-xl p-5 mb-8 border border-gray-200 shadow-inner">
                        <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-2">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                {{ __('Configuración del Reporte') }}
                            </h3>
                            
                            {{-- Indicador del reporte activo --}}
                            <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">
                                @switch($reportType)
                                    @case('attendance') Módulo de Asistencia @break
                                    @case('grades') Módulo de Notas @break
                                    @case('payments') Módulo Financiero @break
                                    @default Vista General
                                @endswitch
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                            {{-- Filtros de Fecha --}}
                            @if(in_array($reportType, ['payments', 'calendar']))
                                <div class="md:col-span-3">
                                    <x-input-label for="date_from" :value="__('Desde')" />
                                    <input type="date" id="date_from" wire:model="date_from" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                </div>
                                <div class="md:col-span-3">
                                    <x-input-label for="date_to" :value="__('Hasta')" />
                                    <input type="date" id="date_to" wire:model="date_to" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                </div>
                            @endif

                            {{-- Filtros de Curso/Sección --}}
                            @if(in_array($reportType, ['attendance', 'grades', 'students', 'assignments', 'payments']))
                                <div class="md:col-span-3">
                                    <x-input-label for="course_id" :value="__('Curso')" />
                                    <select id="course_id" wire:model.live="course_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">-- Todos los Cursos --</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('course_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- NUEVO: Filtro de Módulo (Intermedio) --}}
                            @if(in_array($reportType, ['attendance', 'grades', 'students']) && $course_id)
                                <div class="md:col-span-3">
                                    <x-input-label for="module_id" :value="__('Módulo')" />
                                    <select id="module_id" wire:model.live="module_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">-- Seleccionar Módulo --</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('module_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- Filtro de Sección (Ahora depende de module_id) --}}
                            @if(in_array($reportType, ['attendance', 'grades', 'students']) && $module_id)
                                <div class="md:col-span-3">
                                    <x-input-label for="schedule_id" :value="__('Sección / Horario')" />
                                    <select id="schedule_id" wire:model="schedule_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">-- Seleccionar Sección --</option>
                                        @foreach($schedules as $schedule)
                                            <option value="{{ $schedule->id }}">
                                                {{ $schedule->section_name ?? 'Sección Única' }} 
                                                ({{ $schedule->start_time ?? '' }} - {{ $schedule->end_time ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('schedule_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- Filtros Profesor/Estado --}}
                            @if(in_array($reportType, ['payments', 'assignments']))
                                <div class="md:col-span-3">
                                    <x-input-label for="teacher_id" :value="__('Profesor')" />
                                    <select id="teacher_id" wire:model="teacher_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">-- Todos --</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($reportType === 'payments')
                                <div class="md:col-span-3">
                                    <x-input-label for="payment_status" :value="__('Estado de Pago')" />
                                    <select id="payment_status" wire:model="payment_status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="all">Todo (Pagos y Deudas)</option>
                                        <option value="pending">Solo Pendientes/Deuda</option>
                                        <option value="paid">Solo Pagados</option>
                                    </select>
                                </div>
                            @endif

                            {{-- Botón Generar (Ocupa el espacio restante o se alinea a la derecha) --}}
                            <div class="md:col-span-12 flex justify-end mt-4 pt-4 border-t border-gray-200">
                                <x-primary-button wire:click="generateReport" wire:loading.attr="disabled" class="ml-3">
                                    <span wire:loading.remove wire:target="generateReport">{{ __('Generar Vista Previa') }}</span>
                                    <span wire:loading wire:target="generateReport" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ __('Procesando...') }}
                                    </span>
                                </x-primary-button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. RESULTADOS / VISTA PREVIA --}}
                    @if($reportData)
                        <div class="border border-gray-200 rounded-lg shadow overflow-hidden">
                            <div class="bg-gray-100 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('Vista Previa del Documento') }}</h3>
                                
                                @if($generatedReportType === 'attendance' && isset($reportData['schedule']))
                                    {{-- Botón PDF para Asistencia --}}
                                    <a href="{{ route('reports.attendance.pdf', $reportData['schedule']->id) }}" 
                                       onclick="window.open(this.href, 'ReporteAsistenciaPDF', 'width=1000,height=800,scrollbars=yes,resizable=yes'); return false;"
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Descargar PDF') }}
                                    </a>
                                @elseif($generatedReportType === 'grades' && isset($reportData['schedule']))
                                    {{-- Botón PDF para Calificaciones --}}
                                    <a href="{{ route('reports.grades.pdf', $reportData['schedule']->id) }}" 
                                       onclick="window.open(this.href, 'ReporteCalificacionesPDF', 'width=1000,height=800,scrollbars=yes,resizable=yes'); return false;"
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Descargar PDF') }}
                                    </a>
                                @else
                                    {{-- Botón JS Print para otros reportes --}}
                                    <button onclick="printReport()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        {{ __('Imprimir / PDF') }}
                                    </button>
                                @endif
                            </div>
                            <div class="bg-white p-6 overflow-x-auto">
                                {{-- Inclusión dinámica de vistas con chequeo de existencia --}}
                                @if($generatedReportType === 'attendance')
                                    @if(view()->exists('reports.attendance-report')) @include('reports.attendance-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'grades')
                                    @if(view()->exists('reports.grades-report')) @include('reports.grades-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'payments')
                                    @if(view()->exists('reports.financial-report')) @include('reports.financial-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'students')
                                    @if(view()->exists('reports.student-list-report')) @include('reports.student-list-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'calendar')
                                    @if(view()->exists('reports.calendar-report')) @include('reports.calendar-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'assignments')
                                    @if(view()->exists('reports.assignment-report')) @include('reports.assignment-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Estado Vacío (Placeholder) --}}
                        <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="mt-2 block text-sm font-medium text-gray-900">
                                {{ __('Seleccione un tipo de reporte y los filtros para generar una vista previa.') }}
                            </span>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script>
        function printReport() {
            var printContents = document.getElementById('printable-area').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            // Recargar para restaurar los eventos de Livewire/Alpine
            window.location.reload(); 
        }
    </script>
</div>