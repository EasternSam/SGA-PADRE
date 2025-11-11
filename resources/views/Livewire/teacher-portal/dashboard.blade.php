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
                        <img class="h-20 w-20 rounded-full" 
                             src="https://placehold.co/200x200/e2e8f0/64748b?text={{ substr(Auth::user()->name, 0, 1) }}" 
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

        <!-- 2. Contenido (Secciones Asignadas) -->
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
                                                <div class="text-sga-text-light">{{ $schedule->start_time }} - {{ $schedule->end_time }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                {{ $schedule->enrollments_count }} Inscritos
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <div class_ ="flex gap-4">
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
</div>