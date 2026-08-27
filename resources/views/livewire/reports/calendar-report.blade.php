{{-- Reporte Calendario Académico --}}
<div id="printable-area" class="bg-white text-black p-4 md:p-8 font-sans">
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <h1 class="text-2xl font-bold uppercase">Calendario de Cursos Activos</h1>
        <p class="text-sm text-gray-600">Periodo: {{ $data['start_date'] }} al {{ $data['end_date'] }}</p>
    </div>

    <table class="w-full border-collapse border border-gray-300 text-xs">
        <thead class="bg-indigo-50">
            <tr>
                <th class="border border-gray-300 p-2 text-left">Curso / Módulo</th>
                <th class="border border-gray-300 p-2 text-left">Sección</th>
                <th class="border border-gray-300 p-2 text-center">Fecha Inicio</th>
                <th class="border border-gray-300 p-2 text-center">Fecha Fin</th>
                <th class="border border-gray-300 p-2 text-left">Días y Horario</th>
                <th class="border border-gray-300 p-2 text-left">Profesor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['schedules'] as $schedule)
                <tr>
                    <td class="border border-gray-300 p-2 font-bold">
                        {{ $schedule->module->course->name ?? 'N/A' }}
                        <div class="text-[10px] font-normal text-gray-500">{{ $schedule->module->name }}</div>
                    </td>
                    <td class="border border-gray-300 p-2">{{ $schedule->section_name }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }}</td>
                    <td class="border border-gray-300 p-2 text-center">{{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}</td>
                    <td class="border border-gray-300 p-2">
                        @if(is_array($schedule->days_of_week))
                            {{ implode(', ', $schedule->days_of_week) }}
                        @else
                            {{ $schedule->days_of_week }}
                        @endif
                        <br>
                        {{ $schedule->start_time }} - {{ $schedule->end_time }}
                    </td>
                    <td class="border border-gray-300 p-2">{{ $schedule->teacher->name ?? 'Por Asignar' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>