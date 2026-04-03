<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-blue-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Estadísticas de Cohorte y Retención</h2>
        <p class="text-sm text-gray-500">Sección: {{ $data['schedule']->section_name }} | Módulo: {{ $data['schedule']->module->name }}</p>
    </div>

    @if($data['total'] == 0)
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No hay estudiantes inscritos en esta sección.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-blue-50 rounded-xl p-6 border border-blue-200 shadow-sm flex flex-col items-center justify-center">
                <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest text-center">Matrícula Total</h4>
                <p class="text-3xl font-black text-blue-900 mt-2">{{ $data['total'] }}</p>
            </div>
            
            @php
                $aprobados = ($data['stats']['Aprobado'] ?? 0) + ($data['stats']['Completado'] ?? 0);
                $retirados = $data['stats']['Retirado'] ?? 0;
                $activos = $data['stats']['Activo'] ?? 0;
            @endphp

            <div class="bg-emerald-50 rounded-xl p-6 border border-emerald-200 shadow-sm flex flex-col items-center justify-center">
                <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-widest text-center">Éxito Académico</h4>
                <p class="text-2xl font-black text-emerald-900 mt-2">{{ number_format(($aprobados / $data['total']) * 100, 1) }}%</p>
                <p class="text-xs text-emerald-600 mt-1">{{ $aprobados }} Aprobados</p>
            </div>

            <div class="bg-amber-50 rounded-xl p-6 border border-amber-200 shadow-sm flex flex-col items-center justify-center">
                <h4 class="text-xs font-bold text-amber-800 uppercase tracking-widest text-center">En Proceso</h4>
                <p class="text-2xl font-black text-amber-900 mt-2">{{ number_format(($activos / $data['total']) * 100, 1) }}%</p>
                <p class="text-xs text-amber-600 mt-1">{{ $activos }} Activos/Pendientes</p>
            </div>

            <div class="bg-red-50 rounded-xl p-6 border border-red-200 shadow-sm flex flex-col items-center justify-center">
                <h4 class="text-xs font-bold text-red-800 uppercase tracking-widest text-center">Índice de Deserción</h4>
                <p class="text-2xl font-black text-red-900 mt-2">{{ number_format(($retirados / $data['total']) * 100, 1) }}%</p>
                <p class="text-xs text-red-600 mt-1">{{ $retirados }} Retirados</p>
            </div>
        </div>

        <!-- Barras de Progreso Detalladas -->
        <h3 class="text-lg font-bold text-gray-900 mb-4 uppercase mt-8 border-b pb-2">Distribución Exacta de Estatus</h3>
        <div class="space-y-4">
            @foreach($data['stats'] as $status => $count)
                @php
                    $percent = ($count / $data['total']) * 100;
                    $colorClass = match(strtolower($status)) {
                        'aprobado', 'completado' => 'bg-emerald-500',
                        'retirado' => 'bg-red-500',
                        'activo' => 'bg-blue-500',
                        'reprobado' => 'bg-orange-500',
                        default => 'bg-gray-400'
                    };
                @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-bold text-gray-700">{{ strtoupper($status) }}</span>
                        <span class="text-gray-600">{{ $count }} estudiantes ({{ round($percent, 1) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="{{ $colorClass }} h-3 rounded-full" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
