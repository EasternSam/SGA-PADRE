<div class="py-6">
    <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <svg class="w-8 h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Gestión de Reportes Dinámicos
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    {{-- Sidebar de Tipos de Reporte --}}
                    <div class="col-span-1 space-y-2">
                        <h3 class="font-semibold text-gray-500 uppercase text-xs tracking-wider mb-3 px-2">Seleccionar Reporte</h3>
                        
                        @foreach([
                            'attendance' => '📅 Asistencia',
                            'grades' => '🎓 Calificaciones',
                            'payments' => '💰 Pagos y Deudas',
                            'students' => '👥 Listado Estudiantes',
                            'calendar' => '🗓️ Calendario Académico',
                            'assignments' => '👨‍🏫 Cargas Académicas'
                        ] as $key => $label)
                            <button wire:click="$set('reportType', '{{ $key }}')" 
                                class="w-full text-left px-4 py-3 rounded-lg transition-all duration-200 text-sm font-medium
                                {{ $reportType === $key 
                                    ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100 ring-1 ring-indigo-200' 
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Área de Configuración y Vista Previa --}}
                    <div class="col-span-1 lg:col-span-3 border-l border-gray-100 pl-0 lg:pl-6">
                        
                        {{-- Panel de Filtros --}}
                        <div class="bg-gray-50 rounded-xl p-5 mb-8 border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4 uppercase tracking-wide flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                Filtros: @switch($reportType)
                                    @case('attendance') Asistencia @break
                                    @case('grades') Notas @break
                                    @case('payments') Financiero @break
                                    @default General
                                @endswitch
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {{-- Filtros de Fecha --}}
                                @if(in_array($reportType, ['attendance', 'payments', 'calendar']))
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Desde</label>
                                        <input type="date" wire:model="date_from" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Hasta</label>
                                        <input type="date" wire:model="date_to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                @endif

                                {{-- Filtros de Curso/Sección --}}
                                @if(in_array($reportType, ['attendance', 'grades', 'students', 'assignments', 'payments']))
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Curso</label>
                                        <select wire:model.live="course_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <option value="">-- Todos los Cursos --</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                @if(in_array($reportType, ['attendance', 'grades', 'students']) && $course_id)
                                    <div class="col-span-2 md:col-span-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Sección / Horario</label>
                                        <select wire:model="schedule_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <option value="">-- Seleccionar Sección --</option>
                                            @foreach($schedules as $schedule)
                                                <option value="{{ $schedule->id }}">
                                                    {{ $schedule->section_name ?? 'Sección Única' }} 
                                                    ({{ $schedule->start_time ?? '' }} - {{ $schedule->end_time ?? '' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                {{-- Filtros Específicos Pagos/Asignaciones --}}
                                @if(in_array($reportType, ['payments', 'assignments']))
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Profesor</label>
                                        <select wire:model="teacher_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <option value="">-- Todos --</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                @if($reportType === 'payments')
                                    <div class="col-span-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                                        <select wire:model="payment_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            <option value="all">Todo (Pagos y Deudas)</option>
                                            <option value="pending">Solo Pendientes/Deuda</option>
                                            <option value="paid">Solo Pagados</option>
                                        </select>
                                    </div>
                                @endif

                                {{-- Botón Generar --}}
                                <div class="col-span-1 md:col-span-3 flex justify-end mt-4 pt-4 border-t border-gray-200">
                                    <button wire:click="generateReport" wire:loading.attr="disabled" class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 shadow-md">
                                        <span wire:loading.remove wire:target="generateReport">Generar Vista Previa</span>
                                        <span wire:loading wire:target="generateReport" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Procesando...
                                        </span>
                                    </button>
                                </div>
                            </div>
                            @error('schedule_id') <span class="text-red-500 text-xs block mt-2">{{ $message }}</span> @enderror
                            @error('course_id') <span class="text-red-500 text-xs block mt-2">{{ $message }}</span> @enderror
                        </div>

                        {{-- Área de Vista Previa --}}
                        <div class="border border-gray-200 rounded-lg shadow-sm bg-gray-50 min-h-[500px]">
                            @if($reportData)
                                {{-- Toolbar --}}
                                <div class="bg-white px-4 py-3 border-b border-gray-200 flex justify-between items-center rounded-t-lg shadow-sm">
                                    <span class="text-xs font-bold text-gray-500 uppercase flex items-center">
                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                        Vista Previa Generada
                                    </span>
                                    <button onclick="printReport()" class="inline-flex items-center px-3 py-1 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Imprimir / Guardar PDF
                                    </button>
                                </div>

                                {{-- Contenedor del Reporte (Scrollable) --}}
                                <div class="p-6 overflow-auto max-h-[800px] bg-white rounded-b-lg">
                                    {{-- Incluimos dinámicamente la vista según el tipo --}}
                                    @if($generatedReportType === 'attendance')
                                        @include('reports.attendance-report', ['data' => $reportData])
                                    @elseif($generatedReportType === 'grades')
                                        @include('reports.grades-report', ['data' => $reportData])
                                    @elseif($generatedReportType === 'payments')
                                        @include('reports.financial-report', ['data' => $reportData])
                                    @elseif($generatedReportType === 'students')
                                        @include('reports.student-list-report', ['data' => $reportData])
                                    @elseif($generatedReportType === 'calendar')
                                        @include('reports.calendar-report', ['data' => $reportData])
                                    @elseif($generatedReportType === 'assignments')
                                        @include('reports.assignment-report', ['data' => $reportData])
                                    @endif
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-[500px] text-gray-400">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <p class="text-sm font-medium">Configura los filtros y haz clic en "Generar Vista Previa"</p>
                                </div>
                            @endif
                        </div>
                    </div>
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
            window.location.reload(); 
        }
    </script>
</div>