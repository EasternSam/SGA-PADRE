<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Solicitudes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session()->has('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="mb-4">
                        <x-input-label for="filterStatus" :value="__('Filtrar por Estado')" />
                        <select wire:model.live="filterStatus" id="filterStatus" class="block mt-1 w-full md:w-1/3 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="pendiente">{{ __('Pendientes') }}</option>
                            <option value="aprobado">{{ __('Aprobadas') }}</option>
                            <option value="rechazado">{{ __('Rechazadas') }}</option>
                            <option value="">{{ __('Todas') }}</option>
                        </select>
                    </div>

                    <!-- Tabla de Solicitudes -->
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($requests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $request->student->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($request->status == 'aprobado') bg-green-100 text-green-800
                                                @elseif($request->status == 'rechazado') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <!-- El botón no cambia, ya pasa el ID correctamente -->
                                            <x-primary-button wire:click="viewRequest({{ $request->id }})">
                                                {{ __('Ver / Gestionar') }}
                                            </x-primary-button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No se encontraron solicitudes con ese estado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Gestionar Solicitud -->
    
    {{-- El modal se vincula a la propiedad $showingModal del componente Livewire. --}}
    {{-- @close se encarga de poner $showingModal en `false` cuando se cierra desde el frontend (ej. con la tecla ESC). --}}
    <x-modal :show="$showingModal" @close="showingModal = false" max-width="lg">
        
        @if($selectedRequest)
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ __('Detalles de la Solicitud') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <strong>{{ __('Estudiante') }}:</strong>
                        <span>{{ $selectedRequest->student->user->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong>{{ __('Email') }}:</strong>
                        <span>{{ $selectedRequest->student->user->email ?? 'N/A' }}</span>
                    </div>
                     <div>
                        <strong>{{ __('Fecha') }}:</strong>
                        <span>{{ $selectedRequest->created_at->format('d/m/Y H:i A') }}</span>
                    </div>
                    <div>
                        <strong>{{ __('Tipo') }}:</strong>
                        <span>{{ $selectedRequest->type }}</span>
                    </div>
                    <div>
                        <strong>{{ __('Detalles del Estudiante') }}:</strong>
                        {{-- Usamos {!! nl2br(e(...)) !!} para preservar saltos de línea de forma segura --}}
                        <p class="mt-1 p-2 bg-gray-50 rounded-md border border-gray-200" style="white-space: pre-wrap; word-break: break-word;">{!! nl2br(e($selectedRequest->details)) !!}</p>
                    </div>
                    <div>
                        <strong>{{ __('Estado Actual') }}:</strong>
                        <span>{{ ucfirst($selectedRequest->status) }}</span>
                    </div>

                    <hr>

                    <!-- Formulario de Admin -->
                    <div>
                        <x-input-label for="adminNotes" :value="__('Notas del Administrador (Respuesta)')" />
                        <textarea wire:model="adminNotes" id="adminNotes" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="{{ __('Escriba aquí la respuesta o notas internas...') }}"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button @click="showingModal = false">
                        {{ __('Cancelar') }}
                    </x-secondary-button>
                    
                    <x-danger-button wire:click="updateRequest('rechazado')">
                        {{ __('Rechazar') }}
                    </x-danger-button>

                    <x-primary-button class="bg-green-600 hover:bg-green-700" wire:click="updateRequest('aprobado')">
                        {{ __('Aprobar') }}
                    </x-primary-button>
                </div>
            </div>
        @endif
    </x-modal>

</div>