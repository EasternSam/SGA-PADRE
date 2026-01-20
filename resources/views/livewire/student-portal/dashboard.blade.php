<div class="min-h-screen bg-gray-50 pb-8">
    {{-- 
        =================================================================
        ENCABEZADO (HEADER) - Estilo Admin
        ================================================================= 
    --}}
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold leading-tight text-gray-900">
                    {{ __('Mi Expediente') }}
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-md shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-gray-600">Estudiante Activo</span>
                </div>
                <span class="hidden sm:block text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-md border border-gray-200 shadow-sm">
                    {{ now()->locale('es')->isoFormat('D [de] MMM, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8 space-y-6 sm:space-y-8">

        {{-- MENSAJES FLASH --}}
        @if (session()->has('message'))
            <div class="mb-4 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700 shadow-sm" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm" role="alert">
                <strong class="font-bold">¡Atención!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ALERTA DE PAGOS --}}
        @if($pendingPayments->count() > 0)
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-red-100 opacity-50 blur-xl"></div>
                <div class="flex relative z-10">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.433-.813 1.601-.813 2.034 0l6.28 11.752c.433.813-.207 1.755-1.017 1.755H3.22c-.81 0-1.45-.942-1.017-1.755L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-red-800">Tienes {{ $pendingPayments->count() }} Pago(s) Pendiente(s)</h3>
                        <div class="mt-1 text-sm text-red-700">
                            <p>Por favor regulariza tu situación financiera para evitar bloqueos. <a href="{{ route('student.payments') }}" class="font-bold underline hover:text-red-900">Ir a Pagar &rarr;</a></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 
            =================================================================
            1. TARJETAS DE ESTADÍSTICAS (KPIs)
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Card: Cursos Activos -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-50 opacity-50 blur-xl transition-all group-hover:bg-blue-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-blue-50 p-3 text-blue-600 ring-1 ring-blue-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Cursos Activos</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $activeEnrollments->count() }}</p>
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">En curso</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Cursos Completados -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl transition-all group-hover:bg-emerald-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-emerald-50 p-3 text-emerald-600 ring-1 ring-emerald-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.499 5.221 50.59 50.59 0 00-2.658.814m-15.482 0A50.617 50.617 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Completados</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $completedEnrollments->count() }}</p>
                            <span class="text-xs text-gray-400 font-medium">Histórico</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Pagos Pendientes -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-orange-50 opacity-50 blur-xl transition-all group-hover:bg-orange-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-orange-50 p-3 text-orange-600 ring-1 ring-orange-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Pagos Pendientes</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">{{ $pendingPayments->count() }}</p>
                            <span class="text-xs text-gray-400 font-medium">Facturas</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Deuda Total -->
            <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-red-50 opacity-50 blur-xl transition-all group-hover:bg-red-100"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="shrink-0 rounded-lg bg-red-50 p-3 text-red-600 ring-1 ring-red-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-500 truncate">Deuda Total</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold text-gray-900 tracking-tight">${{ number_format($pendingPayments->sum('amount'), 0) }}</p>
                            <span class="text-xs text-gray-400 font-medium">DOP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
            =================================================================
            2. TARJETA DE PERFIL (Estilo "Graph Section" del Admin)
            ================================================================= 
        --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
            <!-- Decoración de fondo -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-gradient-to-br from-indigo-50 to-blue-50 blur-3xl opacity-60 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <!-- Avatar -->
                <div class="shrink-0 relative group">
                    <div class="h-28 w-28 rounded-full ring-4 ring-white shadow-lg overflow-hidden bg-gray-100">
                        <img class="h-full w-full object-cover"
                             src="https://placehold.co/200x200/e2e8f0/64748b?text={{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}"
                             alt="Avatar">
                    </div>
                    <button wire:click="openProfileModal" class="absolute bottom-0 right-0 bg-indigo-600 text-white p-1.5 rounded-full shadow-md hover:bg-indigo-700 transition-colors" title="Editar Perfil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left space-y-1">
                    <h2 class="text-2xl font-bold text-gray-900">Hola, {{ $student->first_name }} 👋</h2>
                    <p class="text-gray-500 text-sm">Bienvenido a tu portal estudiantil. Aquí puedes gestionar tus cursos y pagos.</p>
                    
                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            {{ $student->mobile_phone ?? $student->phone ?? 'Sin teléfono' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $student->city ?? 'Sin ciudad' }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            {{ $student->email }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
            =================================================================
            3. SECCIÓN PRINCIPAL (TABLA + SIDEBAR)
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            
            {{-- COLUMNA IZQUIERDA: Tabla de Cursos Activos --}}
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
                    {{-- Header de la Tabla --}}
                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold leading-6 text-gray-900">Mis Inscripciones Activas</h3>
                                <p class="text-sm text-gray-500 mt-1">Listado de cursos en progreso o pendientes</p>
                            </div>
                        </div>
                    </div>

                    {{-- Cuerpo de la Tabla --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50/50 text-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Curso / Módulo</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Profesor</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Horario</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-center">Estado</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($activeEnrollments as $enrollment)
                                    <tr class="hover:bg-gray-50/80 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-gray-900 font-medium">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</span>
                                                <span class="text-gray-500 text-xs">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            {{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-xs">
                                            {{ $enrollment->courseSchedule->days_of_week ? implode(', ', $enrollment->courseSchedule->days_of_week) : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $badgeClass = match($enrollment->status) {
                                                    'Activo', 'Cursando' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'Pendiente' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                                    'Completado' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                    default => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                                                {{ $enrollment->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($enrollment->status !== 'Pendiente')
                                                <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-colors">
                                                    Ver Detalle
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs cursor-not-allowed">No disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </div>
                                                <p class="text-gray-500 text-sm">No tienes cursos activos actualmente.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <a href="{{ route('student.requests') }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-gray-900 group-hover:text-indigo-700 transition-colors">Solicitudes</span>
                                    <span class="text-xs text-gray-500">Diplomas, Retiros...</span>
                                </div>
                            </div>
                            <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-indigo-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </a>

                        <a href="{{ route('student.payments') }}" class="group flex items-center justify-between rounded-xl border border-gray-100 bg-white p-3 hover:border-emerald-200 hover:bg-emerald-50/30 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform duration-300 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-gray-900 group-hover:text-emerald-700 transition-colors">Mis Pagos</span>
                                    <span class="text-xs text-gray-500">Historial y Pendientes</span>
                                </div>
                            </div>
                            <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-emerald-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Panel: Historial Financiero Reciente -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-gray-100 text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        Estado de Cuenta (Reciente)
                    </h3>
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($paymentHistory->take(5) as $payment)
                                <li>
                                    <div class="relative pb-8">
                                        @unless($loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-100" aria-hidden="true"></span>
                                        @endunless
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @php
                                                    $iconClass = $payment->status == 'Pendiente' 
                                                        ? 'bg-red-100 text-red-600 ring-red-100' 
                                                        : 'bg-green-100 text-green-600 ring-green-100';
                                                @endphp
                                                <span class="h-8 w-8 rounded-full {{ $iconClass }} ring-4 ring-white flex items-center justify-center shadow-sm">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-xs text-gray-600 font-medium">
                                                        {{ $payment->description ?? 'Pago' }}
                                                    </p>
                                                    <span class="block text-[10px] text-gray-400 mt-0.5">
                                                        {{ $payment->enrollment->courseSchedule->module->course->name ?? '' }}
                                                    </span>
                                                </div>
                                                <div class="text-right">
                                                    <span class="block text-xs font-bold {{ $payment->status == 'Pendiente' ? 'text-red-600' : 'text-green-600' }}">
                                                        ${{ number_format($payment->amount, 0) }}
                                                    </span>
                                                    <div class="whitespace-nowrap text-[10px] text-gray-400">
                                                        {{ $payment->created_at->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li><p class="text-sm text-gray-500 text-center">Sin movimientos recientes.</p></li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL DE COMPLETAR PERFIL (Mantenemos el mismo diseño limpio del modal) --}}
    <x-modal name="complete-profile-modal" :show="$showProfileModal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                📝 Completa tu Perfil
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Para brindarte un mejor servicio, por favor actualiza la siguiente información.
            </p>

            <form wire:submit.prevent="saveProfile" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Teléfono Móvil -->
                    <div>
                        <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                        <x-text-input id="mobile_phone" type="text" class="mt-1 block w-full" 
                                      wire:model="mobile_phone" 
                                      placeholder="809-555-5555" />
                        @error('mobile_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                        <x-text-input id="birth_date" type="date" class="mt-1 block w-full" 
                                      wire:model="birth_date" />
                        @error('birth_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Género -->
                    <div>
                        <x-input-label for="gender" value="Género" />
                        <select id="gender" wire:model="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccionar...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Ciudad -->
                    <div>
                        <x-input-label for="city" value="Ciudad" />
                        <x-text-input id="city" type="text" class="mt-1 block w-full" 
                                      wire:model="city" placeholder="Ej: Santo Domingo" />
                        @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sector -->
                    <div>
                        <x-input-label for="sector" value="Sector" />
                        <x-text-input id="sector" type="text" class="mt-1 block w-full" 
                                      wire:model="sector" placeholder="Ej: Gazcue" />
                        @error('sector') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <x-input-label for="address" value="Dirección Completa" />
                        <x-text-input id="address" type="text" class="mt-1 block w-full" 
                                      wire:model="address" placeholder="Calle, Número, Edificio..." />
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="closeProfileModal">
                        Más tarde
                    </x-secondary-button>

                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        Guardar Información
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>