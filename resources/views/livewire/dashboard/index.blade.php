<div class="min-h-screen bg-gray-50 pb-8" wire:init="loadStats">
    
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
            </div>
            <div class="flex items-center gap-3">
                {{-- Oculto en móviles para ahorrar espacio --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-md shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-gray-600">Sistema en línea</span>
                </div>
                {{-- AHORA TAMBIÉN OCULTO EN MÓVILES (hidden sm:block) --}}
                <span class="hidden sm:block text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md border border-gray-200 shadow-sm">
                    {{ now()->locale('es')->isoFormat('D [de] MMM, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8 space-y-6 sm:space-y-8">

        {{-- 
            =================================================================
            1. TARJETAS DE ESTADÍSTICAS (KPIs) - SE CARGAN DE INMEDIATO
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Card: Estudiantes -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-50 opacity-50 blur-xl transition-all group-hover:bg-blue-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-blue-50 p-3 text-blue-600 ring-1 ring-blue-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Estudiantes</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $totalStudents }}</p>
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Activos</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.students.index') }}" wire:navigate class="absolute inset-0 z-20 focus:outline-none"></a>
            </div>

            <!-- Card: Cursos -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-indigo-50 opacity-50 blur-xl transition-all group-hover:bg-indigo-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-indigo-50 p-3 text-indigo-600 ring-1 ring-indigo-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.499 5.221 50.59 50.59 0 00-2.658.814m-15.482 0A50.617 50.617 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Cursos</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $totalCourses }}</p>
                            <span class="text-xs text-gray-400 font-medium">Catálogo</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.courses.index') }}" wire:navigate class="absolute inset-0 z-20 focus:outline-none"></a>
            </div>

            <!-- Card: Profesores -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-purple-50 opacity-50 blur-xl transition-all group-hover:bg-purple-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-purple-50 p-3 text-purple-600 ring-1 ring-purple-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-1.294-1.579 6.721 6.721 0 01-1.294 1.579 2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 00-2.25 2.25v.75h15v-.75a2.25 2.25 0 00-2.25-2.25h-1.5a2.25 2.25 0 01-2.25-2.25z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Profesores</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $totalTeachers }}</p>
                            <span class="text-xs text-gray-400 font-medium">Docentes</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.teachers.index') }}" wire:navigate class="absolute inset-0 z-20 focus:outline-none"></a>
            </div>

            <!-- Card: Inscripciones -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl transition-all group-hover:bg-emerald-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-emerald-50 p-3 text-emerald-600 ring-1 ring-emerald-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Inscripciones</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $totalEnrollments }}</p>
                            <span class="text-xs text-gray-400 font-medium">Totales</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
            =================================================================
            0. SECCIÓN NUEVA: GRÁFICO DE TENDENCIAS (LAZY LOADED)
            ================================================================= 
        --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 relative overflow-hidden">
            <!-- Decoración -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-gradient-to-br from-indigo-50 to-blue-50 blur-3xl opacity-60 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-gradient-to-tr from-emerald-50 to-teal-50 blur-3xl opacity-60 pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 relative z-10">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Tendencia de Inscripciones</h2>
                    <p class="text-sm text-gray-500 mt-1">Comparativa de flujo Web vs Sistema (Últimos 12 meses)</p>
                </div>
                
                <div class="mt-4 sm:mt-0 flex gap-3 bg-gray-50/80 backdrop-blur-sm p-1.5 rounded-lg border border-gray-100">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white shadow-sm border border-gray-100">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.5)]"></span>
                        <span class="text-xs font-semibold text-gray-700">Web</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white shadow-sm border border-gray-100">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        <span class="text-xs font-semibold text-gray-700">Sistema</span>
                    </div>
                </div>
            </div>

            <!-- Contenedor del Gráfico con Lazy Loading -->
            <div class="w-full h-[380px] relative">
                @if(!$readyToLoad)
                    <!-- ESQUELETO DE CARGA -->
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-50/50 rounded-lg animate-pulse border border-gray-100 backdrop-blur-sm z-20">
                        <div class="flex flex-col items-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mb-3"></div>
                            <span class="text-sm text-gray-600 font-medium bg-white px-3 py-1 rounded-full shadow-sm">Obteniendo datos de WordPress...</span>
                        </div>
                    </div>
                    <!-- Placeholder visual del gráfico -->
                    <div class="absolute inset-0 flex items-end justify-between px-4 pb-4 opacity-30">
                        @for($i = 0; $i < 12; $i++)
                            <div class="w-full bg-gray-200 mx-1 rounded-t-sm h-[{{ rand(20, 80) }}%]"></div>
                        @endfor
                    </div>
                @else
                    <div id="enrollmentChart" wire:ignore></div>
                @endif
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
                
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                    {{-- Header con TABS FUNCIONALES --}}
                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Inscripciones Recientes</h3>
                                <p class="text-sm text-gray-500 mt-1">Gestión de últimos registros</p>
                            </div>
                            <div>
                                <nav class="flex p-1 space-x-1 bg-gray-50/80 rounded-lg border border-gray-100" aria-label="Tabs">
                                    <button wire:click="setFilter('all')" 
                                            class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $enrollmentFilter === 'all' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
                                        Todas
                                    </button>
                                    <button wire:click="setFilter('Pendiente')" 
                                            class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $enrollmentFilter === 'Pendiente' ? 'bg-white text-yellow-700 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
                                        Pendientes
                                    </button>
                                    <button wire:click="setFilter('Activo')" 
                                            class="rounded-md px-3 py-1.5 text-xs font-medium transition-all {{ $enrollmentFilter === 'Activo' ? 'bg-white text-green-700 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
                                        Activas
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>

                    {{-- Cuerpo de la Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50/50 text-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Curso</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Estado</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-right">Fecha</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($recentEnrollments as $enrollment)
                                    <tr class="hover:bg-gray-50/80 transition-colors group">
                                        <td class="px-6 py-4">
                                            @php
                                                // Lógica unificada para Nombre e Iniciales
                                                $student = $enrollment->student ?? null;
                                                $user = $student->user ?? null;
                                                $studentName = 'N/A';
                                                
                                                if ($student) {
                                                    $first = $student->first_name ?? $student->name ?? $student->nombres ?? $student->firstname ?? $user->first_name ?? $user->name ?? '';
                                                    $last = $student->last_name ?? $student->apellidos ?? $student->lastname ?? $user->last_name ?? $user->lastname ?? '';
                                                    $studentName = trim($first . ' ' . $last);
                                                    if (empty($studentName)) {
                                                        $studentName = $student->full_name ?? $student->fullname ?? $user->full_name ?? $user->fullname ?? '';
                                                    }
                                                    if (empty($studentName) && $student) {
                                                         $studentName = $student->email ?? $user->email ?? 'Sin Nombre';
                                                    }
                                                    $initialFirst = !empty($first) ? substr($first, 0, 1) : 'U';
                                                    $initialLast = !empty($last) ? substr($last, 0, 1) : '';
                                                    $initials = strtoupper($initialFirst . $initialLast);
                                                } else {
                                                    $initials = 'NA';
                                                }
                                            @endphp

                                            <div class="flex items-center">
                                                <div class="h-9 w-9 flex-shrink-0 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 font-bold border border-white shadow-sm ring-1 ring-gray-100 text-xs">
                                                    {{ $initials }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                        {{ $studentName }}
                                                    </div>
                                                    <div class="text-gray-500 text-xs">{{ $enrollment->student->email ?? '' }}</div>
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
                                                $badgeClass = match($enrollment->status) {
                                                    'Activo' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'Pendiente' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                                    'Cancelado' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    default => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                                                {{ $enrollment->status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-500 font-mono text-xs">
                                            {{ $enrollment->created_at ? $enrollment->created_at->format('d M, Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($enrollment->student)
                                                <a href="{{ route('admin.students.profile', $enrollment->student->id) }}" wire:navigate class="text-gray-400 hover:text-indigo-600 transition-colors p-1.5 hover:bg-indigo-50 rounded-lg inline-block">
                                                    <span class="sr-only">Ver</span>
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </a>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                    </svg>
                                                </div>
                                                <p class="text-gray-500 text-sm">No se encontraron inscripciones.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <span class="text-xs text-gray-500 font-medium">Mostrando los últimos 5 registros</span>
                        <a href="{{ route('admin.students.index') }}" wire:navigate class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 flex items-center gap-1 group">
                            Ver directorio completo 
                            <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Sidebar --}}
            <div class="space-y-6">
                
                <!-- Panel: Accesos Directos -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        Accesos Rápidos
                    </h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.students.index') }}" wire:navigate class="group flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-5.033a5.971 5.971 0 00-2.139-.549c-3.218 0-5.8 2.686-5.8 6.002 0 2.213 1.139 4.168 2.875 5.253a5.971 5.971 0 011.666-.341 5.971 5.971 0 014.2 0" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-gray-900 group-hover:text-indigo-700 transition-colors">Nuevo Estudiante</span>
                                    <span class="text-xs text-gray-500">Registrar alumno</span>
                                </div>
                            </div>
                            <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-indigo-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </a>

                        <a href="{{ route('admin.finance.concepts') }}" wire:navigate class="group flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 hover:border-emerald-200 hover:bg-emerald-50/30 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-gray-900 group-hover:text-emerald-700 transition-colors">Registrar Pago</span>
                                    <span class="text-xs text-gray-500">Gestión financiera</span>
                                </div>
                            </div>
                            <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-emerald-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </a>

                        <a href="{{ route('admin.courses.index') }}" wire:navigate class="group flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 hover:border-purple-200 hover:bg-purple-50/30 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-gray-900 group-hover:text-purple-700 transition-colors">Gestionar Cursos</span>
                                    <span class="text-xs text-gray-500">Administrar catálogo</span>
                                </div>
                            </div>
                            <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-purple-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Panel: Timeline de Actividad -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-gray-100 text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Actividad Reciente
                    </h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($recentActivities as $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @unless($loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                                        @endunless
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                    $desc = strtolower($activity->description ?? '');
                                                    $iconClass = match(true) {
                                                        str_contains($desc, 'creado') || str_contains($desc, 'registrado') => 'bg-green-100 text-green-600 ring-green-100',
                                                        str_contains($desc, 'eliminado') => 'bg-red-100 text-red-600 ring-red-100',
                                                        str_contains($desc, 'actualizado') || str_contains($desc, 'editado') => 'bg-blue-100 text-blue-600 ring-blue-100',
                                                        default => 'bg-gray-100 text-gray-600 ring-gray-100'
                                                    };
                                                @endphp
                                                <span class="h-8 w-8 rounded-full {{ $iconClass }} ring-4 ring-white flex items-center justify-center shadow-sm">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-xs text-gray-600 font-medium">
                                                        {{ $activity->description }} 
                                                    </p>
                                                    @if(isset($activity->causer))
                                                        <span class="block text-[10px] text-gray-400 mt-0.5">por <span class="font-semibold text-gray-500">{{ $activity->causer->name ?? 'Sistema' }}</span></span>
                                                    @endif
                                                </div>
                                                <div class="whitespace-nowrap text-right text-[10px] text-gray-400 font-medium">
                                                    {{ $activity->created_at->diffForHumans(null, true, true) }}
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
                                                <span class="h-8 w-8 rounded-full bg-gray-50 flex items-center justify-center ring-4 ring-white">
                                                    <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-500 font-medium">No hay actividad reciente.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
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
        window.initDashboardChart = function(chartData) {
            const chartElement = document.querySelector("#enrollmentChart");
            
            if (!chartElement) return;

            const dataWeb = chartData?.web || [];
            const dataSystem = chartData?.system || [];
            const labels = chartData?.labels || [];

            // Limpiar si ya existe algo para evitar duplicados en SPA
            chartElement.innerHTML = '';

            const options = {
                series: [{
                    name: 'Web (API)',
                    data: dataWeb || []
                }, {
                    name: 'Físico (Sistema)',
                    data: dataSystem || []
                }],
                chart: {
                    type: 'area',
                    height: 380, // Altura aumentada
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.05
                    }
                },
                colors: ['#6366f1', '#10b981'], // Indigo-500 y Emerald-500
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.6, // Más opaco al inicio
                        opacityTo: 0.1,   // Más transparente al final
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: labels || [],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { 
                            colors: '#64748b', 
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif'
                        },
                        offsetY: 5
                    },
                    tooltip: {
                        enabled: false
                    }
                },
                yaxis: {
                    labels: {
                        style: { 
                            colors: '#64748b', 
                            fontSize: '12px',
                            fontFamily: 'Inter, sans-serif'
                        },
                        formatter: (val) => { return val ? val.toFixed(0) : 0 },
                        offsetX: -10
                    },
                    min: 0 // Asegura que empiece en 0
                },
                grid: {
                    borderColor: '#f1f5f9', // Gray-100 más suave
                    strokeDashArray: 5,     // Líneas discontinuas
                    yaxis: { lines: { show: true } },
                    xaxis: { lines: { show: false } },
                    padding: { top: 0, right: 20, bottom: 10, left: 20 }
                },
                tooltip: {
                    theme: 'light',
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Inter, sans-serif',
                    },
                    x: {
                        show: true
                    },
                    y: {
                        formatter: function (val) {
                            return val + " alumnos";
                        }
                    },
                    marker: {
                        show: true,
                    },
                },
                legend: { show: false }, // Oculta leyenda default porque hicimos una personalizada
                markers: {
                    size: 0,
                    hover: {
                        size: 6,
                        sizeOffset: 3
                    }
                }
            };

            const chart = new ApexCharts(chartElement, options);
            chart.render();
        };

        // Escuchar evento personalizado
        document.addEventListener('livewire:init', () => {
            Livewire.on('stats-loaded', (event) => {
                // Livewire v3 pasa los params dentro de un array en event[0]
                const chartData = event[0]; 
                window.initDashboardChart(chartData);
            });
        });
    </script>
</div>