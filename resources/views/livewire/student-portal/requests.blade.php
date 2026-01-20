<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Módulo de Solicitudes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Formulario de Nueva Solicitud -->
                    <div class="mb-8 border-b pb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Crear Nueva Solicitud') }}</h3>
                        
                        @if (session()->has('success'))
                            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="submitRequest">
                            <div class="grid grid-cols-1 gap-6 max-w-2xl">
                                <!-- Tipo de Solicitud -->
                                <div>
                                    <x-input-label for="type" :value="__('Tipo de Solicitud')" />
                                    <select wire:model.live="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">{{ __('Seleccione un tipo') }}</option>
                                        @foreach($requestTypes as $key => $value)
                                            @if($key === 'solicitar_diploma')
                                                <option value="{{ $key }}" @disabled(!$canRequestDiploma)>
                                                    {{ $value }} @if(!$canRequestDiploma) (No elegible - Requiere cursos completados) @endif
                                                </option>
                                            @else
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                                </div>

                                <!-- LÓGICA CONDICIONAL DE INPUTS -->
                                
                                <!-- Caso 1: Retiro o Cambio (Requiere Curso Activo) -->
                                @if ($type === 'retiro_curso' || $type === 'cambio_seccion')
                                    <div class="animate-in fade-in slide-in-from-top-2 duration-300">
                                        <x-input-label for="selectedTargetId" :value="__('Seleccione el Curso Activo')" />
                                        <select wire:model="selectedTargetId" id="selectedTargetId" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">{{ __('Seleccione curso...') }}</option>
                                            @forelse($activeEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    {{ $enrollment->courseSchedule->module->course->name ?? 'Curso' }} 
                                                    ({{ $enrollment->courseSchedule->section_name ?? 'Sección' }})
                                                </option>
                                            @empty
                                                <option value="" disabled>{{ __('No hay cursos activos disponibles.') }}</option>
                                            @endforelse
                                        </select>
                                        <x-input-error :messages="$errors->get('selectedTargetId')" class="mt-2" />
                                    </div>
                                @endif

                                <!-- Caso 2: Diploma (Requiere Curso Completado) -->
                                @if ($type === 'solicitar_diploma')
                                    <div class="animate-in fade-in slide-in-from-top-2 duration-300">
                                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                                            <div class="flex">
                                                <div class="ml-3">
                                                    <p class="text-sm text-blue-700">
                                                        Al ser aprobada esta solicitud, se generará un cobro administrativo. Una vez pagado, podrá descargar su diploma.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <x-input-label for="selectedTargetId" :value="__('Seleccione el Curso Completado')" />
                                        <select wire:model="selectedTargetId" id="selectedTargetId" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">{{ __('Seleccione curso completado...') }}</option>
                                            @forelse($completedEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    {{ $enrollment->courseSchedule->module->course->name ?? 'Curso' }} 
                                                    (Finalizado: {{ $enrollment->updated_at->format('d/m/Y') }})
                                                </option>
                                            @empty
                                                <option value="" disabled>{{ __('No hay cursos completados disponibles.') }}</option>
                                            @endforelse
                                        </select>
                                        <x-input-error :messages="$errors->get('selectedTargetId')" class="mt-2" />
                                    </div>
                                @endif

                                <!-- Detalles / Motivo -->
                                <div>
                                    <x-input-label for="details" :value="__('Comentarios Adicionales')" />
                                    <textarea wire:model="details" id="details" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                              placeholder="{{ __('Escriba aquí cualquier detalle adicional...') }}"></textarea>
                                    <x-input-error :messages="$errors->get('details')" class="mt-2" />
                                </div>

                                <div>
                                    <x-primary-button wire:loading.attr="disabled">
                                        <span wire:loading wire:target="submitRequest" class="animate-spin mr-2">...</span>
                                        {{ __('Enviar Solicitud') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Historial de Solicitudes -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Mis Solicitudes e Historial') }}</h3>
                        
                        <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Solicitud</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Pago</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($studentRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $requestTypes[$request->type] ?? $request->type }}
                                                @if($request->course)
                                                    <div class="text-xs text-gray-500 font-normal">{{ $request->course->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($request->status == 'aprobado') bg-green-100 text-green-800
                                                    @elseif($request->status == 'rechazado') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($request->payment)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($request->payment->status == 'Pagado') bg-green-100 text-green-800
                                                        @elseif($request->payment->status == 'Pendiente') bg-orange-100 text-orange-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ $request->payment->status }}
                                                        ({{ number_format($request->payment->amount, 2) }} {{ $request->payment->currency }})
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <!-- LÓGICA DE ACCIONES SEGÚN ESTADO Y PAGO -->
                                                
                                                @if($request->type == 'solicitar_diploma' && $request->status == 'aprobado')
                                                    @if($request->payment && $request->payment->status == 'Pagado')
                                                        <!-- Botón Habilitado para generar diploma -->
                                                        <a href="#" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                            Descargar Diploma
                                                        </a>
                                                    @elseif($request->payment && $request->payment->status == 'Pendiente')
                                                        <!-- Botón para ir a pagar -->
                                                        <a href="{{ route('student.payments') }}" class="text-orange-600 hover:text-orange-900 font-bold flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                                            Pagar Ahora
                                                        </a>
                                                    @else
                                                        <span class="text-gray-500 italic">Procesando cobro...</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        <!-- Fila de detalles/notas admin si existen -->
                                        @if($request->admin_notes)
                                            <tr class="bg-gray-50">
                                                <td colspan="5" class="px-6 py-2 text-xs text-gray-600 italic border-b">
                                                    <strong>Nota Admin:</strong> {{ $request->admin_notes }}
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No tiene solicitudes registradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $studentRequests->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>