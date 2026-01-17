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
                    {{ __('Panel de Control') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Bienvenido al sistema de gestión de {{ config('app.name', 'SGA') }}.
                </p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Oculto en móviles para ahorrar espacio --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-md shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                    <span class="text-sm font-medium text-gray-600">Sistema en línea</span>
                </div>
                {{-- AHORA TAMBIÉN OCULTO EN MÓVILES (hidden sm:block) --}}
                <span class="hidden sm:block text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md border border-gray-200 shadow-sm">
                    {{ now()->format('d M, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8 space-y-6 sm:space-y-8">

        {{-- 
            =================================================================
            0. SECCIÓN NUEVA: GRÁFICO DE TENDENCIAS
            ================================================================= 
        --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
            <!-- Decoración de fondo sutil -->
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 rounded-full bg-indigo-50 blur-3xl opacity-50 pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 relative z-10">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">Tendencia de Inscripciones</h2>
                    <p class="text-sm text-gray-500 mt-1">Comparativa de inscripciones Web (API) vs Físicas (Sistema) de los últimos 7 meses.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Web
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Físico
                    </span>
                </div>
            </div>

            <!-- Contenedor del Gráfico -->
            <div id="enrollmentChart" class="w-full h-[350px]"></div>
        </div>

        {{-- 
            =================================================================
            1. TARJETAS DE ESTADÍSTICAS (KPIs)
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Card: Estudiantes -->
            <div class="relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-md border-t-4 border-blue-500">
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-md bg-blue-50 p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Estudiantes</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalStudents }}</p>
                            <p class="ml-2 text-xs font-medium text-gray-400">Registrados</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.students.index') }}" wire:navigate class="absolute inset-0 z-10 focus:outline-none"></a>
            </div>

            <!-- Card: Cursos -->
            <div class="relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-md border-t-4 border-indigo-500">
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-md bg-indigo-50 p-3">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.499 5.221 50.59 50.59 0 00-2.658.814m-15.482 0A50.617 50.617 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Cursos</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalCourses }}</p>
                            <p class="ml-2 text-xs font-medium text-gray-400">En catálogo</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.courses.index') }}" wire:navigate class="absolute inset-0 z-10 focus:outline-none"></a>
            </div>

            <!-- Card: Profesores -->
            <div class="relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-md border-t-4 border-purple-500">
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-md bg-purple-50 p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-1.294-1.579 6.721 6.721 0 01-1.294 1.579 2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 00-2.25 2.25v.75h15v-.75a2.25 2.25 0 00-2.25-2.25h-1.5a2.25 2.25 0 01-2.25-2.25z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Profesores</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalTeachers }}</p>
                            <p class="ml-2 text-xs font-medium text-gray-400">Activos</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.teachers.index') }}" wire:navigate class="absolute inset-0 z-10 focus:outline-none"></a>
            </div>

            <!-- Card: Inscripciones -->
            <div class="relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-md border-t-4 border-green-500">
                <div class="flex items-center gap-4">
                    <div class="shrink-0 rounded-md bg-green-50 p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Inscripciones</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-semibold text-gray-900">{{ $totalEnrollments }}</p>
                            <p class="ml-2 text-xs font-medium text-gray-400">Totales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
            =================================================================
            2. SECCIÓN PRINCIPAL (TABLA + SIDEBAR)
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            
            {{-- COLUMNA IZQUIERDA: Tabla Detallada --}}
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-900/5">
                    {{-- Header con TABS FUNCIONALES --}}
                    <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                        <div class="-ml-4 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap">
                            <div class="ml-4 mt-2">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Inscripciones Recientes</h3>
                            </div>
                            <div class="ml-4 mt-2 flex-shrink-0">
                                <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                                    <button wire:click="setFilter('all')" 
                                            class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $enrollmentFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                                        Todas
                                    </button>
                                    <button wire:click="setFilter('Pendiente')" 
                                            class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $enrollmentFilter === 'Pendiente' ? 'bg-yellow-50 text-yellow-800' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                                        Pendientes
                                    </button>
                                    <button wire:click="setFilter('Activo')" 
                                            class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $enrollmentFilter === 'Activo' ? 'bg-green-50 text-green-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                                        Activas
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>

                    {{-- Cuerpo de la Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium text-gray-900">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-gray-900">Curso</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-gray-900">Estado</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-gray-900 text-right">Fecha</th>
                                    <th scope="col" class="px-6 py-3 font-medium text-gray-900 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($recentEnrollments as $enrollment)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            @php
                                                $student = $enrollment->student ?? null;
                                                $firstInitial = strtoupper(substr($student->name ?? $student->first_name ?? 'U', 0, 1));
                                                $lastInitial = strtoupper(substr($student->last_name ?? '', 0, 1));
                                                if ($lastInitial === '') {
                                                    // try to get initial from middle/second given name if available
                                                    $lastInitial = strtoupper(substr($student->middle_name ?? $student->second_name ?? '', 0, 1));
                                                }

                                                if ($student) {
                                                    if (!empty($student->full_name)) {
                                                        $studentName = $student->full_name;
                                                    } elseif (!empty($student->name) && !empty($student->last_name)) {
                                                        $studentName = trim($student->name . ' ' . $student->last_name);
                                                    } elseif (!empty($student->first_name) || !empty($student->last_name)) {
                                                        $studentName = trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));
                                                    } else {
                                                        $studentName = $student->name ?? $student->last_name ?? $student->email ?? 'N/A';
                                                    }
                                                } else {
                                                    $studentName = 'N/A';
                                                }
                                            @endphp

                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold border border-gray-200">
                                                    {{ $firstInitial }}{{ $lastInitial }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900">
                                                        {{ $studentName }}
                                                    </div>
                                                    <div class="text-gray-500 text-xs">{{ $student->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-gray-900 font-medium">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</span>
                                                <span class="text-gray-500 text-xs">{{ $enrollment->courseSchedule->module->name ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $badgeStyle = match($enrollment->status) {
                                                    'Activo' => 'bg-green-50 text-green-700 border-green-200',
                                                    'Pendiente' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
                                                    'Cancelado' => 'bg-red-50 text-red-700 border-red-200',
                                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badgeStyle }}">
                                                {{ $enrollment->status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-500">
                                            {{ $enrollment->created_at ? $enrollment->created_at->format('d M, Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($enrollment->student)
                                                <a href="{{ route('admin.students.profile', $enrollment->student->id) }}" wire:navigate class="text-gray-400 hover:text-indigo-600 transition-colors">
                                                    <span class="sr-only">Ver</span>
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </a>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                                            No hay inscripciones {{ $enrollmentFilter === 'all' ? 'recientes' : strtolower($enrollmentFilter).'s' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <span class="text-xs text-gray-500 text-center sm:text-left">Mostrando últimos registros</span>
                        <a href="{{ route('admin.students.index') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-500 text-center sm:text-right">
                            Ver directorio completo &rarr;
                        </a>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Sidebar --}}
            <div class="space-y-6">
                
                <!-- Panel: Accesos Directos -->
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-900/5 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Accesos Rápidos</h3>
                    <div class="space-y-2">
                        <a href="{{ route('admin.students.index') }}" wire:navigate class="group flex items-center justify-between rounded-md border border-gray-200 p-3 hover:border-indigo-500 hover:bg-indigo-50 hover:ring-1 hover:ring-indigo-500 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded bg-indigo-100 text-indigo-600 group-hover:bg-white">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 group-hover:text-indigo-900">Nuevo Estudiante</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>

                        <a href="{{ route('admin.finance.concepts') }}" wire:navigate class="group flex items-center justify-between rounded-md border border-gray-200 p-3 hover:border-green-500 hover:bg-green-50 hover:ring-1 hover:ring-green-500 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded bg-green-100 text-green-600 group-hover:bg-white">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 group-hover:text-green-900">Registrar Pago</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 group-hover:text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>

                        <a href="{{ route('admin.courses.index') }}" wire:navigate class="group flex items-center justify-between rounded-md border border-gray-200 p-3 hover:border-purple-500 hover:bg-purple-50 hover:ring-1 hover:ring-purple-500 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded bg-purple-100 text-purple-600 group-hover:bg-white">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <span class="font-medium text-gray-700 group-hover:text-purple-900">Gestionar Cursos</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 group-hover:text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>

                <!-- Panel: Timeline de Actividad -->
                <div class="bg-white rounded-lg shadow-sm ring-1 ring-gray-900/5 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Actividad Reciente</h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($recentActivities as $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @unless($loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endunless
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                    $desc = strtolower($activity->description ?? '');
                                                    $iconClass = match(true) {
                                                        str_contains($desc, 'creado') || str_contains($desc, 'registrado') => 'bg-green-100 text-green-600',
                                                        str_contains($desc, 'eliminado') => 'bg-red-100 text-red-600',
                                                        str_contains($desc, 'actualizado') || str_contains($desc, 'editado') => 'bg-blue-100 text-blue-600',
                                                        default => 'bg-gray-100 text-gray-600'
                                                    };
                                                @endphp
                                                <span class="h-8 w-8 rounded-full {{ $iconClass }} flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $activity->description }} 
                                                        @if(isset($activity->causer))
                                                            por <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'Sistema' }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500">No hay actividad reciente registrada.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    
                    <div class="mt-4 border-t border-gray-100 pt-3">
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 block text-center sm:text-left">Ver historial completo</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script de Gráficos (ApexCharts) -->
    <!-- Usamos CDN para evitar problemas de compilación local y errores de sintaxis module/export -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Función segura de inicialización
        window.initDashboardChart = function() {
            const chartElement = document.querySelector("#enrollmentChart");
            
            if (!chartElement) return;


            const chartDataWeb = @json($chartDataWeb ?? []);
            const chartDataSystem = @json($chartDataSystem ?? []);
            const chartLabels = @json($chartLabels ?? []);

            // Limpiar si ya existe algo para evitar duplicados en SPA
            chartElement.innerHTML = '';

            const options = {
                series: [{
                    name: 'Web (API)',
                    data: chartDataWeb || []
                }, {
                    name: 'Físico (Sistema)',
                    data: chartDataSystem || []
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: ['#6366f1', '#10b981'],
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: chartLabels || [],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#9ca3af', fontSize: '12px' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9ca3af', fontSize: '12px' },
                        formatter: (val) => { return val ? val.toFixed(0) : 0 }
                    }
                },
                grid: {
                    borderColor: '#f3f4f6',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } },
                    padding: { top: 0, right: 0, bottom: 0, left: 10 }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return val + " alumnos";
                        }
                    }
                },
                legend: { show: false }
            };

            const chart = new ApexCharts(chartElement, options);
            chart.render();
        };

        // Ejecutar en navegación Livewire
        document.addEventListener('livewire:navigated', () => {
            if (typeof window.initDashboardChart === 'function') {
                window.initDashboardChart();
            }
        });

        // Ejecutar en carga inicial (si no es SPA/Livewire 3 completo)
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.initDashboardChart === 'function') {
                window.initDashboardChart();
            }
        });
    </script>
</div>