<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-sga-text">
            {{ __('Portal Docente') }}
        </h2>
    </x-slot>

    <!-- --- ¡NUEVO DISEÑO DE PÁGINA! --- -->
    <div class="space-y-6">

        <!-- 1. Tarjeta de Bienvenida -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-6">
                <div class="flex flex-col items-center gap-6 sm:flex-row">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        {{-- MEJORA: Usando ui-avatars para un avatar más limpio --}}
                        <img class="h-20 w-20 rounded-full"
                            src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4f46e5&color=ffffff&size=128"
                            alt="Avatar de {{ Auth::user()->name }}">
                    </div>
                    <!-- Información -->
                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-2xl font-bold text-sga-text">¡Hola, {{ Auth::user()->name }}!</h3>
                        <p class="text-sga-text-light">Te damos la bienvenida a tu portal de docente.</p>
                        <p class="mt-1 text-sm text-sga-text-light">Aquí puedes gestionar tus secciones, tomar asistencia y registrar calificaciones.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. MEJORA: Tarjetas de Estadísticas (KPIs) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Tarjeta 1: Cursos Asignados -->
            <div class="bg-sga-card overflow-hidden shadow-lg sm:rounded-lg p-6 flex items-center">
                <div class="p-4 bg-indigo-500 rounded-full text-white">
                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                </div>
                <div class="ml-4">
                    <div class="text-sga-text-light uppercase text-sm font-bold">Secciones Asignadas</div>
                    <div class="text-2xl font-bold text-sga-text">{{ $totalSchedules }}</div>
                </div>
            </div>

            <!-- Tarjeta 2: Estudiantes Totales -->
            <div class="bg-sga-card overflow-hidden shadow-lg sm:rounded-lg p-6 flex items-center">
                <div class="p-4 bg-green-500 rounded-full text-white">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <div class="ml-4">
                    <div class="text-sga-text-light uppercase text-sm font-bold">Inscripciones Totales</div>
                    <div class="text-2xl font-bold text-sga-text">{{ $totalStudents }}</div>
                </div>
            </div>

            <!-- Tarjeta 3: Próxima Clase (¡MEJORA: AHORA ES DINÁMICA!) -->
            <div class="bg-sga-card overflow-hidden shadow-lg sm:rounded-lg p-6 flex items-center">
                <div class="p-4 bg-yellow-500 rounded-full text-white">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <div class="ml-4">
                    <div class="text-sga-text-light uppercase text-sm font-bold">Próxima Clase</div>
                    @if ($nextClassToday)
                        <div class="text-lg font-bold text-sga-text" title="{{ $nextClassToday->module->course->name }} ({{ $nextClassToday->module->name }})">
                            {{-- Acortamos el nombre del módulo para que quepa --}}
                            {{ \Illuminate\Support\Str::limit($nextClassToday->module->name, 20) }}
                        </div>
                        <div class="text-sm text-sga-text-light">
                            Hoy a las {{ \Carbon\Carbon::parse($nextClassToday->start_time)->format('h:i A') }}
                        </div>
                    @else
                        <div class="text-lg font-bold text-sga-text">No tienes más clases hoy</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- MEJORA: Gráfico de Estudiantes -->
        @if($totalStudents > 0)
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-sga-text mb-4">
                    Distribución de Estudiantes
                </h3>
                {{-- Definimos una altura fija para el contenedor del canvas --}}
                <div class="h-72 w-full max-w-full">
                    <canvas id="studentsChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        <!-- 3. Contenido (Secciones Asignadas) -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-sga-text">
                    Mis Secciones Asignadas
                </h3>

                <!-- Tabla de Secciones -->
                <div class="mt-4 flow-root">
                    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                            <table class="min-w-full divide-y divide-sga-gray">
                                <thead class="bg-sga-bg">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Curso / Módulo</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Horario</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estudiantes</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Acciones</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-sga-gray bg-sga-card">
                                    @forelse ($courseSchedules as $schedule)
                                        <tr wire:key="schedule-{{ $schedule->id }}">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                <div class="font-medium text-sga-text">{{ $schedule->module->course->name ?? 'N/A' }}</div>
                                                <div class="text-sga-text-light">{{ $schedule->module->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                <div class="font-medium text-sga-text">{{ $schedule->day_of_week }}</div>
                                                {{-- MEJORA: Formato de hora --}}
                                                <div class="text-sga-text-light">{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                {{ $schedule->enrollments_count }} Inscritos
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                {{-- CORRECCIÓN: 'class_' a 'class=' --}}
                                                <div class="flex gap-4 justify-end">
                                                    <a href="{{ route('teacher.grades', $schedule->id) }}" wire:navigate
                                                        class="text-sga-secondary hover:text-blue-700">Calificaciones</a>
                                                    <a href="{{ route('teacher.attendance', $schedule->id) }}" wire:navigate
                                                        class="text-sga-secondary hover:text-blue-700">Asistencia</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                                No tienes secciones asignadas.
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
                                position: 'right', // Poner la leyenda a la derecha
                                labels: {
                                    // Adaptar color de texto al modo oscuro (si lo tienes)
                                    color: document.body.classList.contains('dark') ? '#e5e7eb' : '#374151' 
                                }
                            }
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