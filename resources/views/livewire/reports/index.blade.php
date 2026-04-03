<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Reportes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8">
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
                        <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('Seleccione el Tipo de Reporte (Categorizados)') }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- ACADÉMICO -->
                            <div class="bg-blue-50 bg-opacity-50 p-4 rounded-xl border border-blue-100 flex flex-col h-full">
                                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-3">Registro y Académico</h4>
                                <div class="flex flex-col gap-2">
                                    @foreach(['attendance' => 'Asistencia', 'grades' => 'Calificaciones', 'students' => 'Estudiantes', 'calendar' => 'Calendario', 'assignments' => 'Cargas Académicas', 'cohort_stats' => 'Retención (Estadísticas)', 'graduation_eligibility' => 'Elegibles Graduación', 'transcript' => 'Récord de Notas'] as $key => $label)
                                        <button wire:click="$set('reportType', '{{ $key }}')" class="text-left px-4 py-2 text-sm font-medium transition-colors duration-200 border rounded-lg {{ $reportType === $key ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- CAJA Y TESORERÍA -->
                            <div class="bg-emerald-50 bg-opacity-50 p-4 rounded-xl border border-emerald-100 flex flex-col h-full">
                                <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">Caja y Tesorería</h4>
                                <div class="flex flex-col gap-2">
                                    @foreach(['cash_closing' => 'Arqueo / Cierre de Caja Diario', 'debtors' => 'Deudores (Cuentas por Cobrar)', 'income_by_concept' => 'Ingresos por Conceptos'] as $key => $label)
                                        <button wire:click="$set('reportType', '{{ $key }}')" class="text-left px-4 py-2 text-sm font-medium transition-colors duration-200 border rounded-lg {{ $reportType === $key ? 'bg-emerald-600 text-white border-emerald-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- CONTABILIDAD -->
                            <div class="bg-purple-50 bg-opacity-50 p-4 rounded-xl border border-purple-100 flex flex-col h-full">
                                <h4 class="text-xs font-bold text-purple-800 uppercase tracking-wider mb-3">Contabilidad y Finanzas</h4>
                                <div class="flex flex-col gap-2">
                                    @foreach(['payments' => 'Balance Gral. por Inscripción', 'dgii_billing' => 'Facturación DGII (607)', 'scholarships_granted' => 'Subsidios y Becas', 'cash_flow' => 'Flujo de Efectivo'] as $key => $label)
                                        <button wire:click="$set('reportType', '{{ $key }}')" class="text-left px-4 py-2 text-sm font-medium transition-colors duration-200 border rounded-lg {{ $reportType === $key ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
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
                                    @case('attendance') 
                                    @case('grades')
                                    @case('students') 
                                    @case('calendar') 
                                    @case('assignments') 
                                        Módulo de Registro @break
                                    @case('cash_closing')
                                    @case('debtors')
                                    @case('income_by_concept')
                                        Módulo de Caja/Tesorería @break
                                    @case('payments') 
                                    @case('dgii_billing') 
                                    @case('scholarships_granted') 
                                    @case('cash_flow')
                                        Módulo Contable @break
                                    @default Vista General
                                @endswitch
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                            {{-- Filtros de Fecha --}}
                            @if(in_array($reportType, ['payments', 'calendar', 'cash_closing', 'income_by_concept', 'dgii_billing', 'cash_flow', 'scholarships_granted']))
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
                            @if(in_array($reportType, ['attendance', 'grades', 'students', 'assignments', 'payments', 'cohort_stats', 'graduation_eligibility']))
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
                            @if(in_array($reportType, ['attendance', 'grades', 'students', 'cohort_stats']) && $course_id)
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
                            @if(in_array($reportType, ['attendance', 'grades', 'students', 'cohort_stats']) && $module_id)
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

                            {{-- Filtro de Estudiante (Para Récord de Notas) --}}
                            @if($reportType === 'transcript')
                                <div class="md:col-span-6">
                                    <x-input-label for="student_id" :value="__('Buscar Estudiante')" />
                                    <select id="student_id" wire:model="student_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">-- Seleccionar Estudiante --</option>
                                        @foreach($students_list as $student)
                                            <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('student_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                                @elseif($generatedReportType === 'students' && isset($reportData['schedule']))
                                    {{-- Botón PDF para Lista de Estudiantes --}}
                                    <a href="{{ route('reports.students.pdf', $reportData['schedule']->id) }}" 
                                       onclick="window.open(this.href, 'ReporteListaEstudiantesPDF', 'width=1000,height=800,scrollbars=yes,resizable=yes'); return false;"
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Descargar PDF') }}
                                    </a>
                                @elseif($generatedReportType === 'payments')
                                    {{-- Botón PDF para Reporte Financiero --}}
                                    <a href="{{ route('reports.financial.pdf', ['date_from' => $date_from, 'date_to' => $date_to, 'course_id' => $course_id, 'teacher_id' => $teacher_id, 'status' => $payment_status]) }}" 
                                       onclick="window.open(this.href, 'ReporteFinancieroPDF', 'width=1000,height=800,scrollbars=yes,resizable=yes'); return false;"
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Descargar PDF') }}
                                    </a>
                                @else
                                    {{-- Botón Generic PDF Export --}}
                                    <button wire:click="exportToPdf" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        {{ __('Descargar PDF') }}
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
                                @elseif($generatedReportType === 'cash_closing')
                                    @if(view()->exists('reports.cash-closing-report')) @include('reports.cash-closing-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'debtors')
                                    @if(view()->exists('reports.debtors-report')) @include('reports.debtors-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'income_by_concept')
                                    @if(view()->exists('reports.income-report')) @include('reports.income-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'dgii_billing')
                                    @if(view()->exists('reports.dgii-billing-report')) @include('reports.dgii-billing-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'scholarships_granted')
                                    @if(view()->exists('reports.scholarships-granted-report')) @include('reports.scholarships-granted-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'cash_flow')
                                    @if(view()->exists('reports.cash-flow-report')) @include('reports.cash-flow-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'cohort_stats')
                                    @if(view()->exists('reports.cohort-stats-report')) @include('reports.cohort-stats-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'graduation_eligibility')
                                    @if(view()->exists('reports.graduation-report')) @include('reports.graduation-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
                                @elseif($generatedReportType === 'transcript')
                                    @if(view()->exists('reports.transcript-report')) @include('reports.transcript-report', ['data' => $reportData]) @else <div class="text-red-500">Vista no encontrada.</div> @endif
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