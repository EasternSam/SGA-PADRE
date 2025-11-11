<div>
    {{-- Slot del Encabezado (definido en app.blade.php) --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-sga-text">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- --- ¡NUEVO DISEÑO DE DASHBOARD! --- -->
    <div class="space-y-6">
        
        <!-- Saludo -->
        <div>
            <h3 class="text-2xl font-semibold text-sga-text">
                Bienvenido de nuevo, <span class="text-sga-secondary">{{ Auth::user()->name }}</span>
            </h3>
            <p class="text-sga-text-light">
                Aquí tienes un resumen de la actividad reciente.
            </p>
        </div>

        <!-- 1. Tarjetas de Estadísticas -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Card: Estudiantes -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <!-- Icono -->
                            <span class="rounded-md bg-sga-secondary/10 p-3 text-sga-secondary">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372m-10.75 0a9.38 9.38 0 0 0 2.625.372M12 6.875c-1.036 0-1.875.84-1.875 1.875s.84 1.875 1.875 1.875 1.875-.84 1.875-1.875S13.036 6.875 12 6.875Zm0 0v.002v-.002Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15c-1.036 0-1.875.84-1.875 1.875s.84 1.875 1.875 1.875 1.875-.84 1.875-1.875S13.036 15 12 15Zm0 0v.002v-.002Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-sga-text-light">Estudiantes</dt>
                                <dd class="text-3xl font-bold tracking-tight text-sga-text">{{ $totalStudents }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Cursos -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                             <!-- Icono -->
                             <span class="rounded-md bg-sga-accent/10 p-3 text-sga-accent">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.25c2.291 0 4.545-.16 6.731-.462a60.504 60.504 0 0 0-.49-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.75c2.395 0 4.708.16 6.949.462a59.903 59.903 0 0 1-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.5c2.389 0 4.692-.157 6.928-.461" />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-sga-text-light">Cursos</dt>
                                <dd class="text-3xl font-bold tracking-tight text-sga-text">{{ $totalCourses }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Profesores -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                             <!-- Icono -->
                             <span class="rounded-md bg-sga-accent-purple/10 p-3 text-sga-accent-purple">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-sga-text-light">Profesores</dt>
                                <dd class="text-3xl font-bold tracking-tight text-sga-text">{{ $totalTeachers }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Inscripciones -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                             <!-- Icono -->
                             <span class="rounded-md bg-sga-accent-red/10 p-3 text-sga-accent-red">
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-1.5h5.25m-5.25 0h3m-3 0h-3m0 0h1.5M5.625 4.5H18.375a3 3 0 0 1 3 3V16.5a3 3 0 0 1-3 3H5.625a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3Zm0 0V6.25m0 0h5.25m-5.25 0h3m-3 0h-3m0 0h1.5m-1.5 0h.008v.007H4.5Z" />
                                </svg>
                            </span>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="truncate text-sm font-medium text-sga-text-light">Inscripciones</dt>
                                <dd class="text-3xl font-bold tracking-tight text-sga-text">{{ $totalEnrollments }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Gráfica y Tabla -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            <!-- Columna Izquierda: Inscripciones Recientes -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow lg:col-span-2">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-sga-text">
                        Inscripciones Recientes
                    </h3>
                    
                    <!-- Tabla -->
                    <div class="mt-4 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-sga-gray">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-0">Estudiante</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Curso</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-sga-gray bg-sga-card">
                                        @forelse ($recentEnrollments as $enrollment)
                                            <tr wire:key="{{ $enrollment->id }}">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-0">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->student->fullName ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->student->email ?? 'N/A' }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    {{-- Estado (Ejemplo de 'badge') --}}
                                                    <span class="inline-flex items-center rounded-full bg-sga-success/10 px-2.5 py-0.5 text-xs font-medium text-sga-success">
                                                        {{ $enrollment->status ?? 'Activo' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                                    No hay inscripciones recientes.
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

            <!-- Columna Derecha: Gráfica Placeholder -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-sga-text">
                        Estudiantes por Curso
                    </h3>
                    <div class="mt-4 flex h-64 items-center justify-center rounded-lg border-2 border-dashed border-sga-gray">
                        <span class="text-sm text-sga-text-light">
                            (Aquí va la gráfica de donut)
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>