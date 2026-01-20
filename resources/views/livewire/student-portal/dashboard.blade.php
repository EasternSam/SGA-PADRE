<div class="min-h-screen bg-gray-50/50 pb-12">
    {{-- 
        =================================================================
        ENCABEZADO (HEADER)
        ================================================================= 
    --}}
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Hola, {{ explode(' ', $student->first_name)[0] }} 👋
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-full shadow-sm">
                    <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-gray-700">Estudiante Activo</span>
                </div>
                <span class="hidden sm:block text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-full border border-gray-200 shadow-sm">
                    {{ now()->locale('es')->isoFormat('D [de] MMMM, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="mx-auto w-full max-w-[98%] px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        {{-- MENSAJES FLASH --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm transition-all duration-500">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                </div>
            </div>
        @endif

        {{-- ALERTA DE PAGOS --}}
        @if($pendingPayments->count() > 0)
            <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-orange-100 opacity-50 blur-xl"></div>
                <div class="flex items-start gap-4 relative z-10">
                    <div class="flex-shrink-0 p-2 bg-orange-100 rounded-lg">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-orange-900">Tienes {{ $pendingPayments->count() }} pago(s) pendiente(s)</h3>
                        <p class="mt-1 text-sm text-orange-800 text-opacity-90">
                            Mantén tu cuenta al día para evitar interrupciones en el servicio.
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('student.payments') }}" class="inline-flex items-center text-sm font-semibold text-orange-700 hover:text-orange-900 transition-colors">
                                Ver detalles y pagar
                                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
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
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Card: Cursos Activos -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Cursos Activos</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $activeEnrollments->count() }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="flex items-center text-green-600 font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-600 mr-2"></span>
                        En progreso
                    </span>
                </div>
            </div>

            <!-- Card: Histórico -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Histórico Finalizado</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $completedEnrollments->count() }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-gray-500">Cursos completados</span>
                </div>
            </div>

            <!-- Card: Pagos Pendientes -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Facturas Pendientes</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingPayments->count() }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <a href="{{ route('student.payments') }}" class="text-orange-600 font-medium hover:underline">Ver pendientes &rarr;</a>
                </div>
            </div>

            <!-- Card: Deuda Total -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Deuda Total</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">${{ number_format($pendingPayments->sum('amount'), 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-gray-400">DOP (Pesos Dominicanos)</span>
                </div>
            </div>
        </div>

        {{-- 
            =================================================================
            2. LAYOUT PRINCIPAL (TABLA + SIDEBAR)
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- COLUMNA IZQUIERDA: Cursos (Ocupa 2/3) --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- 2.1 TABLA DE CURSOS ACTIVOS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Mis Inscripciones Activas</h3>
                            <p class="text-sm text-gray-500">Cursos que estás cursando actualmente</p>
                        </div>
                        <div class="hidden sm:block">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                {{ $activeEnrollments->count() }} Cursos
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50/50 text-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Curso / Módulo</th>
                                    {{-- COLUMNA PROFESOR ELIMINADA --}}
                                    <th scope="col" class="px-6 py-3 font-semibold">Horario</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-center">Estado</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($activeEnrollments as $enrollment)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-lg">
                                                    {{ substr($enrollment->courseSchedule->module->course->name ?? 'C', 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-gray-900 font-semibold">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</span>
                                                    <span class="text-gray-500 text-xs truncate max-w-[200px]" title="{{ $enrollment->courseSchedule->module->name ?? '' }}">
                                                        {{ $enrollment->courseSchedule->module->name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-gray-900 font-medium text-xs">
                                                    {{ $enrollment->courseSchedule->days_of_week ? implode(', ', $enrollment->courseSchedule->days_of_week) : 'N/A' }}
                                                </span>
                                                <span class="text-gray-400 text-[10px]">
                                                    {{ $enrollment->courseSchedule->start_time ? \Carbon\Carbon::parse($enrollment->courseSchedule->start_time)->format('g:i A') : '' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $badgeClass = match($enrollment->status) {
                                                    'Activo', 'Cursando' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                    'Pendiente' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
                                                    'Completado' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                    default => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                                                {{ $enrollment->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if($enrollment->status !== 'Pendiente')
                                                <a href="{{ route('student.course.detail', $enrollment->id) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                    Ver Aula
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Verificando pago...</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-16 w-16 rounded-full bg-gray-50 flex items-center justify-center mb-4">
                                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </div>
                                                <h3 class="text-base font-semibold text-gray-900">No tienes cursos activos</h3>
                                                <p class="mt-1 text-sm text-gray-500">¿Buscas aprender algo nuevo hoy?</p>
                                                <div class="mt-6">
                                                    <button class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                                        Explorar Catálogo
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2.2 HISTORIAL ACADÉMICO (CURSOS COMPLETADOS) --}}
                @if($completedEnrollments->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Historial Académico</h3>
                            <p class="text-sm text-gray-500">Cursos finalizados satisfactoriamente</p>
                        </div>
                        <div class="hidden sm:block">
                            <span class="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                                {{ $completedEnrollments->count() }} Completados
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50/50 text-gray-900">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Curso / Módulo</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-center">Calificación</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-center">Fecha Fin</th>
                                    <th scope="col" class="px-6 py-3 font-semibold text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($completedEnrollments as $enrollment)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 font-bold text-lg">
                                                    {{ substr($enrollment->courseSchedule->module->course->name ?? 'C', 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-gray-900 font-semibold">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</span>
                                                    <span class="text-gray-500 text-xs truncate max-w-[200px]" title="{{ $enrollment->courseSchedule->module->name ?? '' }}">
                                                        {{ $enrollment->courseSchedule->module->name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($enrollment->final_grade)
                                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-sm font-bold text-gray-700 ring-1 ring-inset ring-gray-500/10">
                                                    {{ number_format($enrollment->final_grade, 1) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-500 text-xs">
                                            {{ $enrollment->updated_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('student.course.detail', $enrollment->id) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                                Ver Detalles
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- 2.3 ACTIVIDAD FINANCIERA --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider text-xs">Actividad Financiera Reciente</h3>
                        <a href="{{ route('student.payments') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver todo el historial</a>
                    </div>
                    
                    <div class="relative border-l border-gray-100 ml-3 space-y-6">
                        @forelse($paymentHistory->take(5) as $payment)
                            <div class="relative pl-6">
                                {{-- Punto de la línea de tiempo --}}
                                <div class="absolute -left-1.5 top-1 h-3 w-3 rounded-full border-2 border-white 
                                    {{ $payment->status == 'Pendiente' ? 'bg-red-400' : 'bg-emerald-400' }}"></div>
                                
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1">
                                    <div class="flex-1">
                                        {{-- Descripción del Pago --}}
                                        <span class="text-sm font-semibold text-gray-900 break-words leading-tight">
                                            {{ $payment->paymentConcept->name ?? $payment->description ?? 'Pago General' }}
                                        </span>
                                        <div class="text-xs text-gray-500 mt-0.5 break-words">
                                            {{ $payment->enrollment->courseSchedule->module->course->name ?? 'Sin curso asociado' }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3 sm:text-right mt-1 sm:mt-0">
                                        <span class="text-xs font-bold {{ $payment->status == 'Pendiente' ? 'text-red-600' : 'text-emerald-600' }}">
                                            ${{ number_format($payment->amount, 0) }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 min-w-[60px] text-right">
                                            {{ $payment->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <span class="text-xs text-gray-400">No hay movimientos recientes.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- COLUMNA DERECHA: Perfil + Sidebar --}}
            <div class="space-y-8">
                
                {{-- CARD DE PERFIL (Estilo ID Card) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                    
                    <div class="relative flex flex-col items-center">
                        <div class="h-24 w-24 rounded-full ring-4 ring-white shadow-lg overflow-hidden bg-white mb-3">
                            <img class="h-full w-full object-cover"
                                 src="https://ui-avatars.com/api/?name={{ urlencode($student->fullName) }}&color=7F9CF5&background=EBF4FF"
                                 alt="Avatar">
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $student->fullName }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ $student->email }}</p>
                        
                        <div class="w-full grid grid-cols-2 gap-2 text-center text-xs text-gray-600 bg-gray-50 rounded-lg p-3 border border-gray-100">
                            <div>
                                <span class="block font-bold text-gray-900">{{ $student->mobile_phone ?? $student->phone ?? '-' }}</span>
                                <span class="text-gray-400">Teléfono</span>
                            </div>
                            <div>
                                <span class="block font-bold text-gray-900">{{ $student->city ?? '-' }}</span>
                                <span class="text-gray-400">Ciudad</span>
                            </div>
                        </div>

                        <button wire:click="openProfileModal" class="mt-4 w-full flex justify-center items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Editar Perfil
                        </button>
                    </div>
                </div>

                {{-- ACCESOS RÁPIDOS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 text-xs">Accesos Directos</h3>
                    <div class="space-y-3">
                        <a href="{{ route('student.requests') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-indigo-50 hover:text-indigo-600 transition-all group border border-transparent hover:border-indigo-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-white text-gray-500 group-hover:text-indigo-600 shadow-sm transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-sm">Solicitar Servicios</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-300 group-hover:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>

                        <a href="{{ route('student.payments') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-emerald-50 hover:text-emerald-600 transition-all group border border-transparent hover:border-emerald-100">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-white text-gray-500 group-hover:text-emerald-600 shadow-sm transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <span class="font-medium text-sm">Gestionar Pagos</span>
                            </div>
                            <svg class="h-4 w-4 text-gray-300 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE COMPLETAR PERFIL --}}
    <x-modal name="complete-profile-modal" :show="$showProfileModal" focusable>
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">
                📝 Completa tu Perfil
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                Para brindarte un mejor servicio y generar tus certificados correctamente, por favor actualiza tu información.
            </p>

            <form wire:submit.prevent="saveProfile" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Teléfono Móvil -->
                    <div>
                        <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                        <x-text-input id="mobile_phone" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                      wire:model="mobile_phone" 
                                      placeholder="809-555-5555" />
                        @error('mobile_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                        <x-text-input id="birth_date" type="date" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                      wire:model="birth_date" />
                        @error('birth_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Género -->
                    <div>
                        <x-input-label for="gender" value="Género" />
                        <select id="gender" wire:model="gender" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                        <x-text-input id="city" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                      wire:model="city" placeholder="Ej: Santo Domingo" />
                        @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sector -->
                    <div>
                        <x-input-label for="sector" value="Sector" />
                        <x-text-input id="sector" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                      wire:model="sector" placeholder="Ej: Gazcue" />
                        @error('sector') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <x-input-label for="address" value="Dirección Completa" />
                        <x-text-input id="address" type="text" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                      wire:model="address" placeholder="Calle, Número, Edificio..." />
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-secondary-button wire:click="closeProfileModal">
                        Más tarde
                    </x-secondary-button>

                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                        Guardar Información
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>