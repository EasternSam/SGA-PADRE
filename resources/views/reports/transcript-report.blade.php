<div id="printable-area" class="p-8 bg-white shrink-0 border border-gray-200 rounded-lg shadow-sm max-w-5xl mx-auto">
    
    @if(!$data['student'])
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            Debe buscar y seleccionar un estudiante para generar el récord de notas histórico.
        </div>
    @else
        <!-- Header del Transcript / Membrete -->
        <div class="text-center mb-8 border-b-4 border-double border-gray-800 pb-6 relative">
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-widest">Récord de Notas Oficial</h1>
            <h2 class="text-lg font-medium text-gray-600 mt-2 uppercase">Historial Académico Completo</h2>
            <div class="absolute right-0 top-0 text-right">
                <p class="text-xs text-gray-500 font-mono">FECHA EMISIÓN</p>
                <p class="text-sm font-bold">{{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <!-- Datos del Estudiante -->
        <div class="grid grid-cols-2 gap-4 mb-8 bg-gray-50 p-4 rounded border border-gray-200">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Nombre del Estudiante</p>
                <p class="text-lg font-black text-gray-900 uppercase">{{ $data['student']->first_name }} {{ $data['student']->last_name }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Matrícula o Documento</p>
                <p class="text-lg font-bold text-gray-700 font-mono">{{ str_pad($data['student']->id, 6, '0', STR_PAD_LEFT) }} / {{ $data['student']->identification_number ?? 'N/D' }}</p>
            </div>
        </div>

        @if($data['enrollments']->isEmpty())
            <div class="p-8 text-center text-gray-500 italic border border-dashed border-gray-300">
                Este estudiante no posee historial de cursos completados o registrados en el sistema.
            </div>
        @else
            <!-- Tabla Académica -->
            <table class="min-w-full divide-y divide-gray-300 border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left border border-gray-300 text-xs font-bold text-gray-700 uppercase">Programa / Curso</th>
                        <th class="px-4 py-3 text-left border border-gray-300 text-xs font-bold text-gray-700 uppercase">Módulo / Asignatura</th>
                        <th class="px-4 py-3 text-center border border-gray-300 text-xs font-bold text-gray-700 uppercase">Período</th>
                        <th class="px-4 py-3 text-center border border-gray-300 text-xs font-bold text-gray-700 uppercase">Calificación</th>
                        <th class="px-4 py-3 text-center border border-gray-300 text-xs font-bold text-gray-700 uppercase">Estatus Literal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($data['enrollments'] as $enr)
                        @php
                            $isApproved = in_array(strtolower($enr->status), ['aprobado', 'completado']);
                            $score = $enr->final_score ?? 0;
                            // Conversión a literal (ejemplo dominicano estandarizado)
                            $literal = 'F';
                            if ($isApproved) {
                                if ($score >= 90) $literal = 'A';
                                elseif ($score >= 80) $literal = 'B';
                                elseif ($score >= 70) $literal = 'C';
                                else $literal = 'D'; // D puede ser aprobatoria en algunos institutos
                            } elseif(strtolower($enr->status) == 'retirado') {
                                $literal = 'R/T';
                            }
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 border border-gray-300 font-bold">
                                {{ $enr->courseSchedule?->module?->course?->name ?? 'Programa General' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 border border-gray-300">
                                {{ $enr->courseSchedule?->module?->name ?? 'N/D' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 text-center border border-gray-300 whitespace-nowrap">
                                {{ substr($enr->courseSchedule?->start_date, 0, 7) ?? 'N/D' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-black text-center border border-gray-300 {{ $isApproved ? 'text-gray-900' : 'text-red-600' }}">
                                {{ $enr->final_score ?? '--' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-center border border-gray-300 {{ $isApproved ? 'text-gray-900' : 'text-red-600' }}">
                                {{ $literal }}
                                <span class="text-xs font-normal block text-gray-500 mt-1 uppercase">{{ $enr->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Escala y Sellos -->
            <div class="mt-8 grid grid-cols-2 gap-8 text-xs text-gray-600">
                <div>
                    <h5 class="font-bold uppercase border-b border-gray-300 pb-1 mb-2">Escala de Calificaciones</h5>
                    <ul class="space-y-1">
                        <li><strong>A</strong>: 90 - 100 (Excelente)</li>
                        <li><strong>B</strong>: 80 - 89 (Bueno)</li>
                        <li><strong>C</strong>: 70 - 79 (Suficiente)</li>
                        <li><strong>F</strong>: 0 - 69 (Reprobado)</li>
                        <li><strong>R/T</strong>: Retirado</li>
                    </ul>
                </div>
                
                <div class="flex items-end justify-center pb-2">
                    <div class="w-full max-w-xs text-center border-t border-gray-800 pt-2">
                        <p class="font-bold uppercase">Firma / Sello Dirección Académica</p>
                        <p class="text-[10px] mt-1">Este documento carece de validez sin el sello oficial de la institución.</p>
                    </div>
                </div>
            </div>
        @endif
</div>
