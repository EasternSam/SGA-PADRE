<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-indigo-600 pb-4 flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 uppercase">Elegibles para Graduación / Certificación</h2>
            @if($data['course'])
                <p class="text-sm text-gray-500 font-bold mt-1">Filtro aplicado: Curso {{ $data['course']->name }}</p>
            @else
                <p class="text-sm text-gray-500">Listado General de toda la institución.</p>
            @endif
        </div>
        <div class="text-right">
            <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-3 py-1 rounded-full border border-indigo-200">
                Lógica: Materias Aprobadas + Balance RD$ 0.00
            </span>
        </div>
    </div>

    @if($data['eligible']->isEmpty())
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No hay candidatos que cumplan con los requisitos de aprobación y deuda cero en este filtro.
        </div>
    @else
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Estudiante / Matrícula</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Módulos Aprobados Recientes</th>
                        <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Estatus Financiero</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['eligible'] as $student)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-black text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-1">ID: {{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }} | Cédula: {{ $student->identification_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div><svg class="h-3 w-3 inline mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>{{ $student->mobile_phone ?? 'N/A' }}</div>
                                @if($student->email)
                                    <div class="mt-1"><svg class="h-3 w-3 inline mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>{{ $student->email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                <ul class="list-disc pl-4 space-y-1">
                                    @php
                                        // Filtramos los completados/aprobados para mostrar
                                        $approved = $student->enrollments->filter(function($enr) {
                                            return in_array($enr->status, ['Aprobado', 'Completado']);
                                        })->take(3); // Mostrar máximo 3
                                    @endphp
                                    @foreach($approved as $enr)
                                        <li>{{ $enr->courseSchedule?->module?->name ?? 'Módulo Desconocido' }} <span class="text-emerald-500 font-bold">({{ $enr->final_score ?? 'N/A' }})</span></li>
                                    @endforeach
                                    @if($student->enrollments->count() > 3)
                                        <li class="text-gray-400 italic">Y otros {{ $student->enrollments->count() - 3 }} más...</li>
                                    @endif
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">
                                    AL DÍA / LIBRE
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
