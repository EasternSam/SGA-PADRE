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
                    <div class="mb-8">
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
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Tipo de Solicitud -->
                                <div>
                                    <x-input-label for="type" :value="__('Tipo de Solicitud')" />
                                    <!-- USAMOS wire:model.live PARA QUE LA VISTA SE ACTUALICE AL CAMBIAR -->
                                    <select wire:model.live="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">{{ __('Seleccione un tipo') }}</option>
                                        @foreach($requestTypes as $key => $value)
                                            @if($key === 'solicitar_diploma')
                                                <!-- Lógica condicional para deshabilitar diploma -->
                                                <option value="{{ $key }}" @disabled(!$canRequestDiploma) title="{{ !$canRequestDiploma ? 'Debe tener cursos completados para solicitar diploma' : '' }}">
                                                    {{ $value }} @if(!$canRequestDiploma) (No elegible) @endif
                                                </option>
                                            @else
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                                </div>

                                <!-- [NUEVO] Dropdown Condicional para Cursos -->
                                <!-- <-- CORREGIDO -->
                                @if ($type === 'retiro_curso' || $type === 'cambio_seccion')
                                    <div x-data x-transition class="animate-in fade-in duration-300">
                                        <x-input-label for="selectedEnrollmentId" :value="__('Curso / Sección Afectada')" />
                                        <select wire:model="selectedEnrollmentId" id="selectedEnrollmentId" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">{{ __('Seleccione el curso...') }}</option>
                                            @forelse($activeEnrollments as $enrollment)
                                                <option value="{{ $enrollment->id }}">
                                                    <!-- --- CORRECCIÓN AQUÍ --- -->
                                                    {{ $enrollment->courseSchedule->module->course->name ?? 'Curso' }} - ({{ $enrollment->courseSchedule->section_name ?? 'Sección' }})
                                                </option>
                                            @empty
                                                <option value="" disabled>{{ __('No tiene cursos activos inscritos.') }}</option>
                                            @endforelse
                                        </select>
                                        <x-input-error :messages="$errors->get('selectedEnrollmentId')" class="mt-2" />
                                    </div>
                                @endif

                                <!-- Detalles / Motivo -->
                                <div>
                                    <!-- Label dinámico -->
                                    <!-- <-- CORREGIDO -->
                                    @if ($type === 'retiro_curso' || $type === 'cambio_seccion')
                                        <x-input-label for="details" :value="__('Motivo de la Solicitud')" />
                                    @else
                                        <x-input-label for="details" :value="__('Detalles de la Solicitud')" />
                                    @endif
                                    
                                    <textarea wire:model="details" id="details" rows="5" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                              placeholder="{{ ($type === 'retiro_curso' || $type === 'cambio_seccion') ? __('Explique brevemente el motivo de su solicitud...') : __('Por favor, explique los detalles de su solicitud...') }}"></textarea>
                                    <x-input-error :messages="$errors->get('details')" class="mt-2" />
                                </div>

                                <div>
                                    <!-- Deshabilitar botón mientras se procesa -->
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
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Mis Solicitudes') }}</h3>
                        
                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas Admin</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($studentRequests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $requestTypes[$request->type] ?? $request->type }}</td>
                                            <!-- Usamos nl2br y e() para formatear y escapar los detalles -->
                                            <td class="px-6 py-4 text-sm text-gray-500" style="white-space: pre-wrap; word-break: break-word;">{!! nl2br(e($request->details)) !!}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($request->status == 'aprobado') bg-green-100 text-green-800
                                                    @elseif($request->status == 'rechazado') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500" style="white-space: pre-wrap; word-break: break-word;">{!! nl2br(e($request->admin_notes ?? 'N/A')) !!}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No tiene solicitudes.</td>
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