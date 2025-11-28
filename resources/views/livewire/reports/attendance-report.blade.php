{{-- Plantilla del Reporte de Asistencia (Optimizada para Impresión) --}}
@if($data)
    <div class="bg-white p-8" id="printable-area">
        {{-- Encabezado del Reporte --}}
        <div class="border-b-2 border-gray-800 pb-4 mb-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    {{-- Logo Placeholder --}}
                    <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs font-bold">
                        LOGO
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Reporte de Asistencia</h1>
                        <p class="text-sm text-gray-600">Sistema de Gestión Académica</p>
                    </div>
                </div>
                <div class="text-right text-sm text-gray-600">
                    <p><strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            {{-- Datos del Curso/Sección --}}
            <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p><span class="font-bold text-gray-900">Curso:</span> {{ $data['schedule']->course->name ?? 'N/A' }}</p>
                    <p><span class="font-bold text-gray-900">Sección:</span> {{ $data['schedule']->section_name ?? 'Única' }}</p>
                    <p><span class="font-bold text-gray-900">Horario:</span> {{ $data['schedule']->days }} ({{ $data['schedule']->start_time }} - {{ $data['schedule']->end_time }})</p>
                </div>
                <div class="text-right">
                    <p><span class="font-bold text-gray-900">Profesor:</span> {{ $data['schedule']->teacher->name ?? 'Sin asignar' }}</p>
                    <p><span class="font-bold text-gray-900">Desde:</span> {{ $data['start_date']->format('d/m/Y') }}</p>
                    <p><span class="font-bold text-gray-900">Hasta:</span> {{ $data['end_date']->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Tabla de Asistencia --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300 text-xs">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 py-2 text-left font-bold w-10">#</th>
                        <th class="border border-gray-300 px-2 py-2 text-left font-bold min-w-[200px]">Estudiante</th>
                        {{-- Generar Columnas de Fechas --}}
                        @foreach($data['dates'] as $date)
                            <th class="border border-gray-300 px-1 py-1 text-center font-bold rotate-header h-24 whitespace-nowrap">
                                <div class="transform -rotate-90 w-6">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
                            </th>
                        @endforeach
                        <th class="border border-gray-300 px-2 py-2 text-center font-bold bg-gray-50">Total<br>Asis.</th>
                        <th class="border border-gray-300 px-2 py-2 text-center font-bold bg-gray-50">Total<br>Faltas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['students'] as $index => $student)
                        @php
                            $presentCount = 0;
                            $absentCount = 0;
                        @endphp
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="border border-gray-300 px-2 py-1 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-1 font-medium text-gray-800 uppercase">
                                {{ $student->last_name }}, {{ $student->first_name }}
                            </td>
                            
                            {{-- Celdas de Asistencia --}}
                            @foreach($data['dates'] as $date)
                                @php
                                    $status = $data['matrix'][$student->id][$date] ?? null;
                                    $cellClass = '';
                                    $display = '';

                                    if ($status === 'present') {
                                        $display = 'P';
                                        $cellClass = 'text-green-600 font-bold bg-green-50';
                                        $presentCount++;
                                    } elseif ($status === 'absent') {
                                        $display = 'F';
                                        $cellClass = 'text-red-600 font-bold bg-red-50';
                                        $absentCount++;
                                    } elseif ($status === 'late') {
                                        $display = 'T';
                                        $cellClass = 'text-yellow-600 font-bold bg-yellow-50';
                                        // A veces T cuenta como presente
                                    } elseif ($status === 'excused') {
                                        $display = 'J'; // Justificado
                                        $cellClass = 'text-blue-600 font-bold bg-blue-50';
                                    } else {
                                        $display = '-';
                                        $cellClass = 'text-gray-300';
                                    }
                                @endphp
                                <td class="border border-gray-300 px-1 py-1 text-center {{ $cellClass }}">
                                    {{ $display }}
                                </td>
                            @endforeach

                            {{-- Totales --}}
                            <td class="border border-gray-300 px-2 py-1 text-center font-bold bg-gray-50">{{ $presentCount }}</td>
                            <td class="border border-gray-300 px-2 py-1 text-center font-bold text-red-600 bg-gray-50">{{ $absentCount }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($data['dates']) + 4 }}" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                No hay estudiantes inscritos en este curso/sección para el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pie de página / Leyenda --}}
        <div class="mt-8 flex justify-between items-end text-xs text-gray-500">
            <div>
                <p class="font-bold mb-1">Leyenda:</p>
                <div class="flex gap-4">
                    <span class="flex items-center"><span class="w-3 h-3 bg-green-50 border border-green-200 text-green-600 font-bold flex items-center justify-center mr-1 text-[10px]">P</span> Presente</span>
                    <span class="flex items-center"><span class="w-3 h-3 bg-red-50 border border-red-200 text-red-600 font-bold flex items-center justify-center mr-1 text-[10px]">F</span> Falta</span>
                    <span class="flex items-center"><span class="w-3 h-3 bg-yellow-50 border border-yellow-200 text-yellow-600 font-bold flex items-center justify-center mr-1 text-[10px]">T</span> Tardanza</span>
                    <span class="flex items-center"><span class="w-3 h-3 bg-blue-50 border border-blue-200 text-blue-600 font-bold flex items-center justify-center mr-1 text-[10px]">J</span> Justificado</span>
                </div>
            </div>
            <div class="text-center pt-8 border-t border-gray-400 w-64">
                Firma del Profesor
            </div>
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500">
        No hay datos para mostrar.
    </div>
@endif