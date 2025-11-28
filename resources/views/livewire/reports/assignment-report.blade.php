{{-- Reporte de Carga Académica (Asignaciones) --}}
<div id="printable-area" class="bg-white text-black p-4 md:p-8 font-sans">
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase">Reporte de Asignación Docente</h1>
        <p class="text-sm text-gray-600">Listado de Cursos y Profesores</p>
    </div>

    @php
        $currentTeacher = null;
    @endphp

    <table class="w-full border-collapse border border-gray-300 text-xs">
        <thead class="bg-gray-100">
            <tr>
                <th class="border border-gray-300 p-2 text-left">Profesor</th>
                <th class="border border-gray-300 p-2 text-left">Asignación (Curso / Módulo / Sección)</th>
                <th class="border border-gray-300 p-2 text-left">Horario</th>
                <th class="border border-gray-300 p-2 text-center">Periodo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['assignments'] as $assign)
                <tr>
                    <td class="border border-gray-300 p-2 font-bold uppercase bg-gray-50 align-top">
                        {{-- Mostrar nombre solo si cambia --}}
                        @if($currentTeacher !== $assign->teacher_id)
                            {{ $assign->teacher->name ?? 'SIN PROFESOR' }}
                            @php $currentTeacher = $assign->teacher_id; @endphp
                        @endif
                    </td>
                    <td class="border border-gray-300 p-2 align-top">
                        {{-- CORRECCIÓN: Orden Curso -> Módulo -> Sección --}}
                        <div class="font-bold text-gray-900 text-sm mb-1">
                            {{ $assign->module->course->name ?? 'Curso N/A' }}
                        </div>
                        <div class="text-xs text-gray-600 uppercase mb-1">
                            <span class="font-semibold">Módulo:</span> {{ $assign->module->name ?? 'General' }}
                        </div>
                        <div class="text-[11px] text-gray-500 italic bg-gray-50 inline-block px-1 rounded">
                            Sección: {{ $assign->section_name }}
                        </div>
                    </td>
                    <td class="border border-gray-300 p-2 align-top">
                         @if(is_array($assign->days_of_week))
                            {{ implode(', ', $assign->days_of_week) }}
                        @else
                            {{ $assign->days_of_week }}
                        @endif
                        <br>
                        <span class="font-semibold">({{ $assign->start_time }} - {{ $assign->end_time }})</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center align-top">
                        {{ \Carbon\Carbon::parse($assign->start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($assign->end_date)->format('d/m/Y') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>