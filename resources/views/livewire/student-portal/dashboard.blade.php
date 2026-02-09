<div class="min-h-screen bg-gray-50/50 pb-12">
    
    {{-- INYECCIÓN DE ESTILOS PARA CROPPER.JS --}}
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
        <style>
            .cropper-view-box, .cropper-face {
                border-radius: 50%; /* Guía visual redonda */
            }
            /* Asegurar que la imagen no se desborde en el modal */
            .img-container img {
                max-width: 100%;
                display: block; /* Importante para Cropper.js */
            }
        </style>
    @endpush

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
                @if($activeCareer)
                    <p class="text-sm text-indigo-600 font-medium mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
                        {{ $activeCareer->name }}
                    </p>
                @endif
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
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $activeDegreeEnrollments->count() + $activeCourseEnrollments->count() }}</p>
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
            2. LAYOUT PRINCIPAL
            ================================================================= 
        --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- COLUMNA IZQUIERDA: Cursos (Ocupa 2/3) --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- TABLA PENDIENTES --}}
                @php $enrollmentPayments = $pendingPayments->whereNotNull('enrollment_id'); @endphp
                @if($enrollmentPayments->count() > 0)
                <div class="bg-yellow-50 rounded-2xl shadow-sm border border-yellow-200 overflow-hidden">
                      <div class="border-b border-yellow-200 px-6 py-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <h3 class="text-lg font-bold text-yellow-800">Inscripciones por Pagar</h3>
                      </div>
                      <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-yellow-100/50 text-yellow-900">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Materia / Curso</th>
                                    <th class="px-6 py-3 font-semibold text-right">Monto</th>
                                    <th class="px-6 py-3 font-semibold text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-yellow-100 bg-white">
                                @foreach ($enrollmentPayments as $payment)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $payment->paymentConcept->name ?? 'Concepto de Pago' }}</div>
                                            <div class="text-xs text-gray-500">{{ $payment->enrollment->courseSchedule->module->name ?? 'Módulo' }} ({{ $payment->enrollment->courseSchedule->module->course->name ?? 'Curso' }})</div>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-900">${{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="$dispatch('payEnrollment', { enrollmentId: {{ $payment->enrollment_id }} })" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded transition font-bold shadow-sm">Pagar Ahora</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                      </div>
                </div>
                @endif

                {{-- TABLA ACTIVOS --}}
                @if($activeDegreeEnrollments->isNotEmpty() || $activeCourseEnrollments->isNotEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="border-b border-gray-100 px-6 py-5">
                            <h3 class="text-lg font-bold text-gray-900">Mis Inscripciones Activas</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full whitespace-nowrap text-left text-sm">
                                <thead class="bg-gray-50/50 text-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Curso / Módulo</th>
                                        <th class="px-6 py-3 font-semibold">Horario</th>
                                        <th class="px-6 py-3 font-semibold text-center">Tipo</th>
                                        <th class="px-6 py-3 font-semibold text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($activeDegreeEnrollments as $enrollment)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="font-semibold">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $enrollment->courseSchedule->days_of_week ? implode(', ', $enrollment->courseSchedule->days_of_week) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-indigo-50 text-indigo-700 ring-indigo-600/20">Carrera</span></td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Ver Aula</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach ($activeCourseEnrollments as $enrollment)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="font-semibold">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                {{ $enrollment->courseSchedule->days_of_week ? implode(', ', $enrollment->courseSchedule->days_of_week) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-green-50 text-green-700 ring-green-600/20">Técnico</span></td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Entrar</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                {{-- HISTORIAL --}}
                @if($completedEnrollments->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-5">
                        <h3 class="text-lg font-bold text-gray-900">Historial Académico</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full whitespace-nowrap text-left text-sm">
                            <thead class="bg-gray-50/50 text-gray-900">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Curso / Módulo</th>
                                    <th class="px-6 py-3 font-semibold text-center">Calificación</th>
                                    <th class="px-6 py-3 font-semibold text-center">Fecha Fin</th>
                                    <th class="px-6 py-3 font-semibold text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($completedEnrollments as $enrollment)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">{{ $enrollment->final_grade ? number_format($enrollment->final_grade, 1) : '-' }}</td>
                                        <td class="px-6 py-4 text-center">{{ $enrollment->updated_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-right"><a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Ver Detalles</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- COLUMNA DERECHA: Perfil + Sidebar --}}
            <div class="space-y-8">
                {{-- CARD DE PERFIL --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                    
                    <div class="relative flex flex-col items-center">
                         {{-- IMAGEN DE PERFIL PRINCIPAL: Usamos object-cover y aspect-square para garantizar 1:1 --}}
                        <div class="h-28 w-28 rounded-full ring-4 ring-white shadow-lg overflow-hidden bg-white mb-3 group relative cursor-pointer" wire:click="openProfileModal">
                            <img class="h-full w-full object-cover aspect-square"
                                 src="{{ $student->profile_photo_url }}" 
                                 alt="{{ $student->fullName }}">
                            
                             <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-full">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
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
                        <a href="{{ route('student.moodle.auth') }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-orange-50 hover:bg-orange-100 hover:text-orange-700 transition-all group border border-transparent hover:border-orange-200">
                             <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-white text-orange-500 group-hover:text-orange-600 shadow-sm transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path d="M12 14l9-5-9-5-9 5 9 5z" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <span class="font-medium text-sm">Aula Virtual (Moodle)</span>
                            </div>
                            <svg class="h-4 w-4 text-orange-300 group-hover:text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                        
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

    {{-- 
        =================================================================
        MODAL DE PERFIL CON CROPPER JS
        ================================================================= 
    --}}
    <x-modal name="complete-profile-modal" :show="$showProfileModal" focusable>
        
        {{-- INICIALIZACIÓN DE ALPINE PARA CROPPER - PASAMOS $wire --}}
        <div x-data="profileCropper()" class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">📝 Tu Perfil de Estudiante</h2>
            <p class="text-sm text-gray-600 mb-6">Mantén tu información actualizada.</p>

            <form wire:submit.prevent="saveProfile" class="space-y-4">
                
                {{-- ZONA DE FOTO: Preview o Crop --}}
                <div class="flex justify-center mb-6">
                    <div class="relative group">
                        
                        {{-- MODO NORMAL: Mostrar foto actual --}}
                        <div x-show="!cropping" class="h-32 w-32 rounded-full ring-4 ring-indigo-50 overflow-hidden bg-gray-100 shadow-md">
                            @if ($photo && !$errors->has('photo'))
                                {{-- Si ya se subió y recortó una, mostrar la temporal --}}
                                <img src="{{ $photo->temporaryUrl() }}" class="h-full w-full object-cover aspect-square">
                            @else
                                {{-- Mostrar la actual (Base de datos o Avatar) --}}
                                <img src="{{ $student->profile_photo_url }}" class="h-full w-full object-cover aspect-square">
                            @endif
                        </div>

                        {{-- BOTÓN CAMBIAR (Solo visible si no estamos recortando) --}}
                        <label x-show="!cropping" for="photo-input" class="absolute bottom-0 right-0 bg-indigo-600 text-white p-2.5 rounded-full cursor-pointer hover:bg-indigo-700 shadow-lg transition-transform hover:scale-110" title="Cambiar Foto">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                            {{-- Input oculto controlado por Alpine --}}
                            <input type="file" id="photo-input" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp" @change="fileChosen">
                        </label>
                    </div>
                </div>

                {{-- MODO RECORTE: Editor CropperJS --}}
                <div x-show="cropping" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full overflow-hidden flex flex-col max-h-[90vh]">
                        <div class="p-4 border-b flex justify-between items-center">
                            <h3 class="font-bold text-gray-800">Ajustar Foto</h3>
                            <button type="button" @click="cancelCrop" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div class="flex-1 bg-gray-100 relative p-4 flex items-center justify-center min-h-[300px]">
                            {{-- Imagen para recortar --}}
                            <div class="img-container">
                                <img x-ref="cropImage" src="" style="display: block; max-width: 100%;">
                            </div>
                        </div>

                        <div class="p-4 border-t bg-gray-50 flex justify-end gap-3">
                            <button type="button" @click="cancelCrop" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium text-sm">Cancelar</button>
                            <button type="button" @click="cropAndSave" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Recortar y Usar
                            </button>
                        </div>
                    </div>
                </div>

                @error('photo') <span class="text-red-500 text-xs block text-center">{{ $message }}</span> @enderror

                {{-- RESTO DEL FORMULARIO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Teléfono Móvil -->
                    <div>
                        <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                        <x-text-input id="mobile_phone" type="text" class="mt-1 block w-full" wire:model="mobile_phone" placeholder="809-555-5555" />
                        @error('mobile_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                        <x-text-input id="birth_date" type="date" class="mt-1 block w-full" wire:model="birth_date" />
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
                        <x-text-input id="city" type="text" class="mt-1 block w-full" wire:model="city" placeholder="Ej: Santo Domingo" />
                        @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sector -->
                    <div>
                        <x-input-label for="sector" value="Sector" />
                        <x-text-input id="sector" type="text" class="mt-1 block w-full" wire:model="sector" placeholder="Ej: Gazcue" />
                        @error('sector') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <x-input-label for="address" value="Dirección Completa" />
                        <x-text-input id="address" type="text" class="mt-1 block w-full" wire:model="address" placeholder="Calle, Número, Edificio..." />
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-secondary-button wire:click="closeProfileModal">
                        Cancelar
                    </x-secondary-button>

                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                        Guardar Cambios
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- SCRIPTS PARA CROPPER JS Y ALPINE --}}
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
        <script>
            // Definir profileCropper en el ámbito global
            function profileCropper() {
                return {
                    cropping: false,
                    cropper: null,
                    file: null,

                    init() {
                        console.log('ProfileCropper debug: Iniciado');
                    },

                    fileChosen(event) {
                        console.log('ProfileCropper debug: Archivo seleccionado');
                        this.file = event.target.files[0];
                        if (this.file) {
                            // Validar tipo manualmente por seguridad
                            if (!['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(this.file.type)) {
                                alert('Por favor selecciona una imagen válida (JPG, PNG, WEBP).');
                                return;
                            }

                            console.log('ProfileCropper debug: Leyendo archivo...', this.file.type);
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                console.log('ProfileCropper debug: Archivo leído');
                                // Asignar src
                                this.$refs.cropImage.src = e.target.result;
                                
                                // Esperar a que la imagen cargue en el DOM antes de iniciar Cropper
                                this.$refs.cropImage.onload = () => {
                                    console.log('ProfileCropper debug: Imagen cargada en DOM, iniciando Cropper');
                                    this.startCropper();
                                }
                            };
                            reader.readAsDataURL(this.file);
                        }
                    },

                    startCropper() {
                        this.cropping = true;
                        // Destruir instancia previa si existe
                        if (this.cropper) {
                            this.cropper.destroy();
                        }
                        // Iniciar Cropper en el siguiente tick (cuando el modal sea visible)
                        this.$nextTick(() => {
                            this.cropper = new Cropper(this.$refs.cropImage, {
                                aspectRatio: 1, // Obligar 1:1
                                viewMode: 1,
                                autoCropArea: 1,
                                responsive: true,
                            });
                        });
                    },

                    cancelCrop() {
                        this.cropping = false;
                        this.file = null;
                        if (this.cropper) {
                            this.cropper.destroy();
                            this.cropper = null;
                        }
                        // Limpiar input file para permitir seleccionar el mismo archivo de nuevo
                        const input = document.getElementById('photo-input');
                        if(input) input.value = '';
                    },

                    cropAndSave() {
                        if (!this.cropper) return;

                        console.log('ProfileCropper debug: Recortando...');
                        // Obtener canvas recortado
                        this.cropper.getCroppedCanvas({
                            width: 500, // Tamaño razonable para perfil
                            height: 500
                        }).toBlob((blob) => {
                            console.log('ProfileCropper debug: Blob generado, subiendo...');
                            // Subir a Livewire manualmente
                            
                            const uploadCallback = (uploadedFilename) => {
                                console.log('ProfileCropper debug: Subida exitosa');
                                this.cropping = false;
                                // Destruir cropper para liberar memoria
                                if (this.cropper) {
                                    this.cropper.destroy();
                                    this.cropper = null;
                                }
                            };

                            const errorCallback = () => {
                                console.error('ProfileCropper debug: Error en subida');
                                alert('Error al subir la imagen. Intenta de nuevo.');
                            };

                            // Usar @this para la subida directa al componente Livewire
                            @this.upload('photo', blob, uploadCallback, errorCallback);

                        }, 'image/jpeg', 0.9); // Calidad 90%
                    }
                }
            }
        </script>
    @endpush
</div>