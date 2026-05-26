<div class="min-h-screen bg-gray-50/50 pb-12">
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 font-outfit">Solicitudes Académicas</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Realiza trámites escolares, retiros de asignaturas y solicitudes de documentos oficiales.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 text-gray-700 px-4 py-1.5 rounded-full text-xs font-semibold flex items-center gap-2 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Portal Escolar Activo
                </div>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto w-full max-w-[98%] px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- COLUMNA IZQUIERDA: Formulario de Nueva Solicitud --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                    <div class="mb-6 pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 font-outfit">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Iniciar Trámite
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Completa los campos para procesar tu solicitud formal.</p>
                    </div>

                    @if (session()->has('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm transition-all duration-300">
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
                            <label for="typeId" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Tipo de Trámite
                            </label>
                            <div class="relative">
                                <select wire:model.live="typeId" id="typeId" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-gray-700 bg-white transition-all text-sm py-2.5 pl-3.5 pr-10 outline-none">
                                    <option value="">Seleccione el tipo de trámite...</option>
                                    @foreach($requestTypes as $rType)
                                        <option value="{{ $rType->id }}">{{ $rType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-error :messages="$errors->get('typeId')" class="mt-1.5" />
                        </div>

                        <!-- INFORMACIÓN DINÁMICA DEL TIPO -->
                        @if($selectedType)
                            <div class="bg-indigo-50/60 rounded-xl p-4 border border-indigo-100/60 space-y-2.5 animate-in fade-in slide-in-from-top-1 duration-200">
                                <p class="text-xs text-indigo-900 leading-relaxed">{{ $selectedType->description }}</p>
                                
                                @if($selectedType->requires_payment)
                                    <div class="flex items-center gap-2 text-xs font-bold text-indigo-700">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Costo del trámite: RD$ {{ number_format($selectedType->payment_amount, 2) }}
                                    </div>
                                @endif
                            </div>

                            <!-- SELECTOR DE MATERIA (Si Aplica) -->
                            @if($selectedType->requires_enrolled_course || $selectedType->requires_completed_course)
                                <div class="animate-in fade-in slide-in-from-top-2 duration-300">
                                    <label for="selectedTargetId" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                        {{ $selectedType->requires_enrolled_course ? 'Seleccione Asignatura Escolar' : 'Seleccione Asignatura Aprobada' }}
                                    </label>
                                    
                                    <select wire:model="selectedTargetId" id="selectedTargetId" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-gray-700 bg-white transition-all text-sm py-2.5 pl-3.5 pr-10 outline-none">
                                        <option value="">-- Seleccionar asignatura --</option>
                                        @forelse($availableEnrollments as $enrollment)
                                            <option value="{{ $enrollment->id }}">
                                                {{ $enrollment->subject->name ?? 'Asignatura' }} ({{ $enrollment->teacher->name ?? 'Docente no asignado' }})
                                            </option>
                                        @empty
                                            <option value="" disabled>{{ __('No hay asignaturas activas disponibles en tu sección.') }}</option>
                                        @endforelse
                                    </select>
                                    <x-input-error :messages="$errors->get('selectedTargetId')" class="mt-1.5" />
                                </div>
                            @endif
                        @endif

                        <!-- Detalles / Motivo -->
                        <div>
                            <label for="details" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                Detalles o Justificación (Opcional)
                            </label>
                            <textarea wire:model="details" id="details" rows="4" class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-gray-700 bg-white transition-all text-sm py-2.5 px-3.5 outline-none resize-none" placeholder="Escribe aquí los motivos o información requerida..."></textarea>
                            <x-input-error :messages="$errors->get('details')" class="mt-1.5" />
                        </div>

                        <div class="pt-2">
                            <button type="submit" wire:loading.attr="disabled" class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm hover:shadow transition-all text-sm font-semibold flex items-center justify-center gap-2 transform active:scale-[0.98] duration-200">
                                <span wire:loading wire:target="submitRequest" class="animate-spin">
                                    <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span>Enviar Solicitud</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Historial de Solicitudes --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 font-outfit">Historial de Solicitudes</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Seguimiento en tiempo real de tus trámites realizados</p>
                        </div>
                        
                        {{-- Filtros --}}
                        <div class="flex items-center gap-2.5 self-start md:self-auto">
                            <input 
                                wire:model.live="search" 
                                type="search" 
                                placeholder="Buscar trámite..." 
                                class="w-44 text-xs rounded-lg border-gray-200 focus:border-indigo-600 focus:ring-indigo-600/20 py-1.5 px-3 outline-none transition-all shadow-sm"
                            />
                            <select wire:model.live="statusFilter" class="border-gray-200 rounded-lg text-xs text-gray-600 focus:border-indigo-600 focus:ring-indigo-600/20 py-1.5 px-3 outline-none shadow-sm bg-white">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="aprobado">Aprobadas</option>
                                <option value="rechazado">Rechazadas</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trámite / Asignatura</th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Pago</th>
                                    <th scope="col" class="px-6 py-3.5 class=text-right text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($studentRequests as $request)
                                    <tr class="hover:bg-gray-50/30 transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            <span class="font-medium text-gray-700 block">{{ $request->created_at->format('d/m/Y') }}</span>
                                            <span class="text-[10px] text-gray-400 block mt-0.5">{{ $request->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-semibold text-gray-900">{{ $request->requestType->name ?? $request->type ?? 'Desconocido' }}</span>
                                                
                                                {{-- Extraer y mostrar el nombre de la asignatura si está codificado en el detalle --}}
                                                @php
                                                    $displaySubject = '';
                                                    if ($request->details) {
                                                        if (preg_match('/Asignatura Relacionada:\s*(.*?)\n/', $request->details, $matches)) {
                                                            $displaySubject = trim($matches[1]);
                                                        }
                                                    }
                                                @endphp

                                                @if($displaySubject)
                                                    <span class="text-xs font-medium text-indigo-600 bg-indigo-50/60 rounded px-2 py-0.5 w-max mt-1">{{ $displaySubject }}</span>
                                                @endif

                                                {{-- Notas de administración --}}
                                                @if($request->admin_notes)
                                                    <div class="mt-2 p-2.5 bg-gray-50 rounded-lg border border-gray-100/80 text-[11px] text-gray-600 italic flex gap-2 items-start">
                                                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                        </svg>
                                                        <span>{{ $request->admin_notes }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @php
                                                $normalizedStatus = strtolower($request->status);
                                                $statusClass = match(true) {
                                                    in_array($normalizedStatus, ['aprobado', 'approved']) => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                                    in_array($normalizedStatus, ['rechazado', 'rejected']) => 'bg-rose-50 text-rose-700 ring-rose-600/10',
                                                    default => 'bg-amber-50 text-amber-800 ring-amber-600/10',
                                                };
                                                $dotClass = match(true) {
                                                    in_array($normalizedStatus, ['aprobado', 'approved']) => 'bg-emerald-500',
                                                    in_array($normalizedStatus, ['rechazado', 'rejected']) => 'bg-rose-500',
                                                    default => 'bg-amber-500 animate-pulse',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $dotClass }}"></span>
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($request->payment)
                                                @php
                                                    $normalizedPayStatus = strtolower($request->payment->status);
                                                    $payStatusClass = match(true) {
                                                        in_array($normalizedPayStatus, ['pagado', 'paid', 'completado', 'completed']) => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                        in_array($normalizedPayStatus, ['pendiente', 'pending']) => 'bg-amber-50 text-amber-700 border-amber-100',
                                                        default => 'bg-gray-50 text-gray-600 border-gray-150',
                                                    };
                                                @endphp
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $payStatusClass }}">
                                                        {{ $request->payment->status }}
                                                    </span>
                                                    <span class="text-xs font-bold text-gray-800">
                                                        RD$ {{ number_format($request->payment->amount, 2) }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-xs font-medium">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                            {{-- ACCIONES DE PAGO O DESCARGA --}}
                                            
                                            {{-- 1. Si hay pago y está pendiente -> Botón Pagar --}}
                                            @if($request->payment && in_array(strtolower($request->payment->status), ['pendiente', 'pending']))
                                                <a href="{{ route('student.payments') }}" class="inline-flex items-center text-white bg-gray-900 hover:bg-gray-800 font-bold text-xs px-3.5 py-1.5 rounded-xl shadow-sm transition-all duration-200 transform active:scale-95">
                                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                    </svg>
                                                    Pagar
                                                </a>
                                            
                                            {{-- 2. Si está APROBADO y (No requiere pago O el pago está PAGADO) -> Botón Descargar --}}
                                            @elseif(in_array(strtolower($request->status), ['aprobado', 'approved']))
                                                @if(!$request->payment || in_array(strtolower($request->payment->status), ['pagado', 'paid', 'completado', 'completed']))
                                                    
                                                    <button wire:click="download({{ $request->id }})" 
                                                            wire:loading.attr="disabled"
                                                            class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-bold text-xs bg-indigo-50 px-3.5 py-1.5 rounded-xl hover:bg-indigo-100/80 transition-all duration-200 transform active:scale-95">
                                                        <svg wire:loading.remove wire:target="download({{ $request->id }})" class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                        <svg wire:loading wire:target="download({{ $request->id }})" class="animate-spin w-3.5 h-3.5 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        Descargar
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-gray-400 font-medium text-[11px] italic">En revisión</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-14 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="h-12 w-12 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center mb-3">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <p class="text-gray-500 text-xs font-semibold">No tienes solicitudes registradas.</p>
                                                <p class="text-gray-400 text-[11px] mt-0.5">Usa el formulario de la izquierda para realizar una nueva.</p>
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