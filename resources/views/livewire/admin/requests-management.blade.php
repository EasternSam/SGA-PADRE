<div class="min-h-screen bg-gray-50/50 pb-12">
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Módulo de Solicitudes</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Gestiona tus trámites académicos, retiros y solicitudes de documentos.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 text-gray-700 px-4 py-1.5 rounded-full text-sm font-medium flex items-center gap-2 shadow-sm">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                    Sistema Operativo
                </div>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto w-full max-w-[98%] px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- COLUMNA IZQUIERDA: Formulario de Nueva Solicitud --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                    <div class="mb-6 pb-4 border-b border-gray-50">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Nueva Solicitud
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Completa el formulario para iniciar un trámite.</p>
                    </div>

                    @if (session()->has('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm transition-all">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="submitRequest" class="space-y-5">
                        
                        <!-- Tipo de Solicitud -->
                        <div>
                            <x-input-label for="typeId" :value="__('Tipo de Trámite')" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5" />
                            <div class="relative">
                                <select wire:model.live="typeId" id="typeId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5 pl-3 pr-10">
                                    <option value="">Seleccione una opción...</option>
                                    @foreach($requestTypes as $rType)
                                        <option value="{{ $rType->id }}">{{ $rType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-error :messages="$errors->get('typeId')" class="mt-1" />
                        </div>

                        <!-- INFORMACIÓN DINÁMICA DEL TIPO -->
                        @if($selectedType)
                            <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100 space-y-2 animate-in fade-in slide-in-from-top-1">
                                <p class="text-xs text-indigo-800">{{ $selectedType->description }}</p>
                                
                                @if($selectedType->requires_payment)
                                    <div class="flex items-center gap-2 text-xs font-bold text-indigo-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Costo del trámite: RD$ {{ number_format($selectedType->payment_amount, 2) }}
                                    </div>
                                @endif
                            </div>

                            <!-- SELECTOR DE CURSO (Si Aplica) -->
                            @if($selectedType->requires_enrolled_course || $selectedType->requires_completed_course)
                                <div class="animate-in fade-in slide-in-from-top-2 duration-300">
                                    <x-input-label for="selectedTargetId" :value="$selectedType->requires_enrolled_course ? 'Seleccione Materia en Curso' : 'Seleccione Materia Aprobada'" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5" />
                                    
                                    <select wire:model="selectedTargetId" id="selectedTargetId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                        <option value="">-- Seleccionar --</option>
                                        @forelse($availableEnrollments as $enrollment)
                                            <option value="{{ $enrollment->id }}">
                                                {{ $enrollment->courseSchedule->module->course->name ?? 'Curso' }} 
                                                ({{ $enrollment->status }})
                                            </option>
                                        @empty
                                            <option value="" disabled>{{ __('No hay cursos disponibles para este trámite.') }}</option>
                                        @endforelse
                                    </select>
                                    <x-input-error :messages="$errors->get('selectedTargetId')" class="mt-1" />
                                </div>
                            @endif
                        @endif

                        <!-- Detalles / Motivo -->
                        <div>
                            <x-input-label for="details" :value="__('Detalles Adicionales')" class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5" />
                            <textarea wire:model="details" id="details" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2" placeholder="Escriba aquí los detalles o motivos..."></textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-1" />
                        </div>

                        <div class="pt-2">
                            <x-primary-button wire:loading.attr="disabled" class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-md transition-all text-sm font-semibold">
                                <span wire:loading wire:target="submitRequest" class="animate-spin mr-2">
                                    <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                {{ __('Enviar Solicitud') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Historial de Solicitudes --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Historial de Solicitudes</h3>
                            <p class="text-sm text-gray-500">Seguimiento de tus trámites realizados</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trámite / Curso</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pago</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($studentRequests as $request)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->created_at->format('d/m/Y') }}
                                            <div class="text-xs text-gray-400">{{ $request->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">{{ $request->requestType->name ?? 'Tipo Desconocido' }}</span>
                                                @if($request->course)
                                                    <span class="text-xs text-gray-500 mt-0.5">{{ $request->course->name }}</span>
                                                @endif
                                            </div>
                                            <!-- Mostrar nota del admin si existe -->
                                            @if($request->admin_notes)
                                                <div class="mt-2 p-2 bg-gray-50 rounded border border-gray-100 text-xs text-gray-600 italic flex gap-2 items-start">
                                                    <svg class="w-3 h-3 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                    </svg>
                                                    {{ $request->admin_notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $statusClass = match($request->status) {
                                                    'aprobado' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                    'rechazado' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    default => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                                };
                                                $dotClass = match($request->status) {
                                                    'aprobado' => 'bg-green-500',
                                                    'rechazado' => 'bg-red-500',
                                                    default => 'bg-yellow-500',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusClass }}">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $dotClass }}"></span>
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($request->payment)
                                                @php
                                                    $payStatusClass = match($request->payment->status) {
                                                        'Pagado', 'Completado' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                        'Pendiente' => 'bg-orange-50 text-orange-700 border-orange-100',
                                                        default => 'bg-gray-50 text-gray-600 border-gray-100',
                                                    };
                                                @endphp
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium border {{ $payStatusClass }}">
                                                        {{ $request->payment->status }}
                                                    </span>
                                                    <span class="text-xs font-semibold text-gray-700">
                                                        RD$ {{ number_format($request->payment->amount, 2) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            {{-- BOTÓN PAGO O DESCARGA --}}
                                            @if($request->payment && $request->payment->status == 'Pendiente')
                                                <a href="{{ route('student.payments') }}" class="inline-flex items-center text-white bg-gray-900 hover:bg-gray-800 font-medium text-xs px-3 py-1.5 rounded-lg shadow-sm transition-colors">
                                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                    Pagar
                                                </a>
                                            @elseif($request->status == 'aprobado' && $request->requestType && $request->requestType->name == 'Solicitud de Diploma')
                                                 @if(!$request->payment || $request->payment->status == 'Pagado')
                                                    <a href="#" class="text-indigo-600 hover:underline text-xs">Descargar</a>
                                                 @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 rounded-full bg-gray-50 flex items-center justify-center mb-3 border border-gray-100">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <p class="text-gray-500 text-sm font-medium">No tienes solicitudes registradas.</p>
                                                <p class="text-gray-400 text-xs mt-1">Usa el formulario para crear una nueva.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($studentRequests->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                            {{ $studentRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>