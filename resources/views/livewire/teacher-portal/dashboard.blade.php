<div class="min-h-screen bg-gray-50 pb-8">

    {{-- 
        =================================================================
        ENCABEZADO (HEADER)
        ================================================================= 
    --}}
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-gray-900">
                    {{ __('Portal Docente') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Gestiona tus clases y estudiantes desde aquí.
                </p>
            </div>
            <div class="flex items-center gap-3">
                 {{-- Oculto en móviles para ahorrar espacio --}}
                 <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-md shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-gray-600">Activo</span>
                </div>
                {{-- Fecha --}}
                <span class="hidden sm:block text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md border border-gray-200 shadow-sm">
                    {{ now()->locale('es')->isoFormat('D [de] MMM, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8 space-y-6 sm:space-y-8">

        <!-- 1. Tarjeta de Bienvenida (Rediseñada) -->
        <div class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-sm border border-gray-100">
            <!-- Decoración de fondo -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 h-48 w-48 rounded-full bg-gradient-to-br from-indigo-50 to-blue-50 blur-3xl opacity-60 pointer-events-none"></div>
            
            <div class="flex flex-col items-center gap-6 sm:flex-row relative z-10">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="p-1 rounded-full bg-white shadow-sm ring-1 ring-gray-100">
                        {{-- MODIFICADO: Usar profile_photo_url de Auth::user() --}}
                        <img class="h-20 w-20 rounded-full object-cover"
                            src="{{ Auth::user()->profile_photo_url }}"
                            alt="Avatar de {{ Auth::user()->name }}">
                    </div>
                </div>
                <!-- Información -->
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="text-2xl font-bold text-gray-900 tracking-tight">¡Hola, {{ Auth::user()->name }}!</h3>
                    <p class="text-gray-500 mt-1">Te damos la bienvenida a tu panel de control.</p>
                    <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2">
                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                            Docente
                        </span>
                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                            {{ Auth::user()->email }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Tarjetas de Estadísticas (KPIs) -->
        <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-3">
            
            <!-- Tarjeta 1: Secciones Asignadas -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-indigo-50 opacity-50 blur-xl transition-all group-hover:bg-indigo-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-indigo-50 p-3 text-indigo-600 ring-1 ring-indigo-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Secciones Asignadas</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $totalSchedules }}</p>
                            <span class="text-xs text-gray-400 font-medium">Activas</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Estudiantes Totales -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-green-50 opacity-50 blur-xl transition-all group-hover:bg-green-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-green-50 p-3 text-green-600 ring-1 ring-green-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Total Estudiantes</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $totalStudents }}</p>
                            <span class="text-xs text-gray-400 font-medium">Inscritos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 3: Próxima Clase -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 blur-xl transition-all group-hover:bg-amber-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-amber-50 p-3 text-amber-600 ring-1 ring-amber-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Próxima Clase</p>
                        @if ($nextClassToday)
                            <div class="flex flex-col">
                                <p class="text-lg font-bold text-gray-900 truncate tracking-tight" title="{{ $nextClassToday->module->course->name }}">
                                    {{ \Illuminate\Support\Str::limit($nextClassToday->module->name, 18) }}
                                </p>
                                <span class="text-xs font-medium text-amber-600 bg-amber-50 rounded-full px-2 py-0.5 inline-block w-fit mt-1 border border-amber-100">
                                    Hoy, {{ \Carbon\Carbon::parse($nextClassToday->start_time)->format('h:i A') }}
                                </span>
                            </div>
                        @else
                             <div class="flex flex-col">
                                <p class="text-lg font-bold text-gray-400 truncate tracking-tight">Sin clases pendientes</p>
                                <span class="text-xs text-gray-400 mt-1">Por el día de hoy</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- COLUMNA IZQUIERDA: Gráfico -->
            <div class="lg:col-span-1">
                @if($totalStudents > 0)
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6 h-full flex flex-col">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-gray-50 text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                            </svg>
                        </div>
                        Distribución
                    </h3>
                    <div class="flex-1 flex items-center justify-center min-h-[300px]">
                        <canvas id="studentsChart"></canvas>
                    </div>
                </div>
                @endif
            </div>

            <!-- COLUMNA DERECHA: Tabla de Secciones -->
            <div class="{{ $totalStudents > 0 ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-5 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Mis Secciones Asignadas</h3>
                            <p class="text-sm text-gray-500 mt-1">Gestión de calificaciones y asistencia</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sm:pl-6">Curso / Módulo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Horario</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estudiantes</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider sm:pr-6">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($courseSchedules as $schedule)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold border border-indigo-100 group-hover:scale-105 transition-transform">
                                                    {{ substr($schedule->module->course->name ?? 'C', 0, 1) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $schedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-gray-500 text-xs mt-0.5">{{ $schedule->module->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-700">{{ $schedule->day_of_week }}</span>
                                                <span class="text-xs text-gray-400 mt-0.5">
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                                {{ $schedule->enrollments_count }} Inscritos
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <div class="flex gap-3 justify-end">
                                                <a href="{{ route('teacher.grades', $schedule->id) }}" wire:navigate
                                                   class="text-gray-500 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-indigo-200 transition-all text-xs font-semibold flex items-center gap-1">
                                                    <i class="fas fa-star text-[10px]"></i> Notas
                                                </a>
                                                <a href="{{ route('teacher.attendance', $schedule->id) }}" wire:navigate
                                                   class="text-gray-500 hover:text-emerald-600 bg-gray-50 hover:bg-emerald-50 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-emerald-200 transition-all text-xs font-semibold flex items-center gap-1">
                                                    <i class="fas fa-check-square text-[10px]"></i> Asistencia
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                    </svg>
                                                </div>
                                                <h3 class="mt-2 text-sm font-semibold text-gray-900">Sin asignaciones</h3>
                                                <p class="mt-1 text-sm text-gray-500">No tienes secciones asignadas en este momento.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- MEJORA: Scripts para el Gráfico --}}
    {{-- Importamos Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    {{-- Inicializamos el gráfico --}}
    <script>
        // --- INICIO DE LA CORRECCIÓN ---

        // 1. Creamos una función para dibujar el gráfico
        function initStudentsChart() {
            // Primero, verificamos si un gráfico ya existe en este canvas y lo destruimos
            // Esto previene errores de "canvas ya en uso"
            let existingChart = Chart.getChart("studentsChart");
            if (existingChart) {
                existingChart.destroy();
            }

            // Solo intentamos renderizar el gráfico si hay datos
            @if($totalStudents > 0)
                // Asegurarnos que el elemento canvas exista antes de usarlo
                const canvasElement = document.getElementById('studentsChart');
                if (!canvasElement) return; // Salir si no se encuentra el canvas

                const ctx = canvasElement.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut', // Tipo de gráfico: dona
                    data: {
                        labels: @json($chartLabels), // Etiquetas desde el componente
                        datasets: [{
                            label: 'Nro. de Estudiantes',
                            data: @json($chartData), // Datos desde el componente
                            // Colores de fondo (puedes añadir más si tienes muchas secciones)
                            backgroundColor: [
                                'rgba(79, 70, 229, 0.8)', // indigo-600
                                'rgba(22, 163, 74, 0.8)', // green-600
                                'rgba(217, 119, 6, 0.8)', // amber-600
                                'rgba(220, 38, 38, 0.8)', // red-600
                                'rgba(107, 114, 128, 0.8)', // gray-500
                                'rgba(20, 184, 166, 0.8)', // teal-500
                                'rgba(59, 130, 246, 0.8)', // blue-500
                                'rgba(217, 70, 239, 0.8)' // fuchsia-500
                            ],
                            borderColor: '#fff', // Borde blanco para separar segmentos
                            borderWidth: 2,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false, // Importante: permite que el gráfico llene el div
                        plugins: {
                            legend: {
                                position: 'bottom', // Leyenda abajo para mejor espacio
                                labels: {
                                    // Adaptar color de texto al modo oscuro (si lo tienes)
                                    color: '#64748b',
                                    font: {
                                        family: 'Inter, sans-serif',
                                        size: 11
                                    },
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        },
                        layout: {
                            padding: 10
                        }
                    }
                });
            @endif
        }

        // 2. Escuchamos el evento de navegación de Livewire
        document.addEventListener('livewire:navigated', () => {
            initStudentsChart();
        });

        // 3. Ejecutamos la función en la carga inicial de la página
        // Usamos 'DOMContentLoaded' para asegurar que el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            initStudentsChart();
        });

        // --- FIN DE LA CORRECCIÓN ---
    </script>
</div>