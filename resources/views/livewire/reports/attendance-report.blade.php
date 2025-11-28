{{-- Plantilla del Reporte de Asistencia --}}
<div class="bg-white p-8" id="printable-area">
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-xs font-bold">
                    LOGO
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-wide">Reporte de Asistencia</h1>
                    <p class="text-sm text-gray-600">Sistema de Gestión Académica</p>
                </div>
            </div>
            <div class="text-right text-sm text-gray-600">
                <p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p><span class="font-bold text-gray-900">Curso:</span> {{ $data['schedule']->course->name ?? 'N/A' }}</p>
                <p><span class="font-bold text-gray-900">Sección:</span> {{ $data['schedule']->section_name ?? 'Única' }}</p>
            </div>
            <div class="text-right">
                <p><span class="font-bold text-gray-900">Profesor:</span> {{ $data['schedule']->teacher->name ?? 'Sin asignar' }}</p>
                <p><span class="font-bold text-gray-900">Desde:</span> {{ $data['start_date']->format('d/m/Y') }} <span class="font-bold text-gray-900">Hasta:</span> {{ $data['end_date']->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300 text-xs">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-2 py-2 text-left font-bold w-10">#</th>
                    <th class="border border-gray-300 px-2 py-2 text-left font-bold min-w-[200px]">Estudiante</th>
                    @foreach($data['dates'] as $date)
                        <th class="border border-gray-300 px-1 py-1 text-center font-bold h-24 whitespace-nowrap">
                            <div class="transform -rotate-90 w-6">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
                        </th>
                    @endforeach
                    <th class="border border-gray-300 px-2 py-2 text-center font-bold bg-gray-50">T</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['students'] as $index => $student)
                    @php $presentCount = 0; @endphp
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="border border-gray-300 px-2 py-1 text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-2 py-1 font-medium text-gray-800 uppercase">
                            {{ $student->last_name }}, {{ $student->first_name }}
                        </td>
                        
                        @foreach($data['dates'] as $date)
                            @php
                                $status = $data['matrix'][$student->id][$date] ?? null;
                                $display = '';
                                $class = '';
                                if ($status === 'present') { $display = '•'; $class = 'text-green-600 font-bold text-lg'; $presentCount++; }
                                elseif ($status === 'absent') { $display = 'F'; $class = 'text-red-600 font-bold'; }
                                elseif ($status === 'late') { $display = 'T'; $class = 'text-yellow-600 font-bold'; }
                            @endphp
                            <td class="border border-gray-300 px-1 py-1 text-center {{ $class }}">
                                {{ $display }}
                            </td>
                        @endforeach
                        <td class="border border-gray-300 px-2 py-1 text-center font-bold bg-gray-50">{{ $presentCount }}</td>
                    </tr>
                @empty
                    <tr><td colspan="100%" class="text-center p-4">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>