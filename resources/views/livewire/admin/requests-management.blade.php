<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Solicitudes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- BOTONES DE ACCIÓN SUPERIOR --}}
            <div class="flex justify-end gap-3 mb-6">
                <x-secondary-button wire:click="openTypesModal" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Configurar Tipos y Lógica
                </x-secondary-button>
                <x-primary-button wire:click="openCreateModal">
                    Nueva Solicitud Manual
                </x-primary-button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session()->has('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-200">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border border-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-4">
                        <div class="w-full sm:w-1/3">
                            <x-input-label for="search" :value="__('Buscar Estudiante')" />
                            <x-text-input wire:model.live.debounce.300ms="search" id="search" class="block mt-1 w-full" type="text" placeholder="Nombre o email..." />
                        </div>
                        <div class="w-full sm:w-1/3">
                            <x-input-label for="filterStatus" :value="__('Filtrar por Estado')" />
                            <select wire:model.live="filterStatus" id="filterStatus" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Todas') }}</option>
                                <option value="pendiente">{{ __('Pendientes') }}</option>
                                <option value="aprobado">{{ __('Aprobadas') }}</option>
                                <option value="rechazado">{{ __('Rechazadas') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabla de Solicitudes -->
                    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo / Curso</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cobro Generado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($requests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-bold">{{ $request->student->user->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $request->student->user->email ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <span class="block font-medium text-indigo-600">{{ $request->requestType->name ?? 'Tipo Desconocido' }}</span>
                                            @if($request->course)
                                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $request->course->name }}</span>
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
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-bold {{ $request->payment->status == 'Pagado' ? 'text-green-600' : 'text-orange-600' }}">
                                                        {{ strtoupper($request->payment->status) }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ number_format($request->payment->amount, 2) }} {{ $request->payment->currency }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-secondary-button type="button" wire:click="viewRequest({{ $request->id }})" class="text-xs">
                                                {{ __('Gestionar') }}
                                            </x-secondary-button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No se encontraron solicitudes.</td>
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

    <!-- MODAL 1: GESTIONAR SOLICITUD (Solo se renderiza si hay selectedRequest) -->
    @if($showingModal)
        <x-modal name="request-modal" :show="$showingModal" max-width="lg" x-on:close="$wire.closeModal()">
            @if($selectedRequest)
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex justify-between items-center">
                        <span>{{ $selectedRequest->requestType->name ?? 'Solicitud' }}</span>
                        <span class="text-sm text-gray-500">#{{ $selectedRequest->id }}</span>
                    </h3>

                    <div class="space-y-4 text-sm">
                        <!-- Info Estudiante -->
                        <div class="bg-gray-50 p-3 rounded-md border border-gray-200">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">Estudiante</span>
                                    <span class="font-medium">{{ $selectedRequest->student->user->name ?? 'N/A' }}</span>
                                </div>
                                @if($selectedRequest->course)
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">Curso Relacionado</span>
                                    <span class="font-medium">{{ $selectedRequest->course->name }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Detalles Solicitud -->
                        <div>
                            <span class="block text-xs text-gray-500 uppercase mb-1">Detalles / Motivo</span>
                            <div class="p-3 bg-white border border-gray-200 rounded-md text-gray-700 h-24 overflow-y-auto" style="white-space: pre-wrap;">{!! nl2br(e($selectedRequest->details)) !!}</div>
                        </div>

                        <!-- Información Lógica del Tipo -->
                        <div class="flex gap-2">
                            @if($selectedRequest->requestType)
                                @if($selectedRequest->requestType->requires_payment)
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Genera Cobro: ${{ $selectedRequest->requestType->payment_amount }}</span>
                                @endif
                                @if($selectedRequest->requestType->requires_enrolled_course)
                                    <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">Requiere Materia Cursando</span>
                                @endif
                            @else
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">Sin tipo asignado</span>
                            @endif
                        </div>

                        <hr>

                        <!-- Formulario de Admin -->
                        <div>
                            <x-input-label for="adminNotes" :value="__('Respuesta / Notas Admin')" />
                            <textarea wire:model="adminNotes" id="adminNotes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="{{ __('Escriba la respuesta...') }}"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <x-secondary-button type="button" wire:click="closeModal">
                            {{ __('Cerrar') }}
                        </x-secondary-button>
                        
                        @if($selectedRequest->status !== 'rechazado')
                            <x-danger-button type="button" wire:click="updateRequest('rechazado')" wire:confirm="¿Seguro que desea rechazar esta solicitud?">
                                {{ __('Rechazar') }}
                            </x-danger-button>
                        @endif

                        @if($selectedRequest->status !== 'aprobado')
                            <x-primary-button type="button" class="bg-green-600 hover:bg-green-700" wire:click="updateRequest('aprobado')" wire:confirm="¿Aprobar solicitud? Si aplica, se generarán los cobros configurados.">
                                {{ __('Aprobar y Procesar') }}
                            </x-primary-button>
                        @endif
                    </div>
                </div>
            @endif
        </x-modal>
    @endif

    <!-- MODAL 2: CONFIGURAR TIPOS DE SOLICITUD -->
    @if($showingTypesModal)
        <x-modal name="types-modal" :show="$showingTypesModal" max-width="4xl" x-on:close="$wire.closeModal()">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Configuración de Tipos de Solicitud</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Columna Izquierda: Formulario -->
                    <div class="md:col-span-1 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="font-bold text-sm text-gray-700 mb-3">{{ $editingTypeId ? 'Editar Tipo' : 'Crear Nuevo Tipo' }}</h4>
                        <form wire:submit.prevent="saveType" class="space-y-3">
                            <div>
                                <x-input-label for="type_name" value="Nombre del Trámite" />
                                <x-text-input id="type_name" wire:model="type_name" class="w-full h-8 text-sm" />
                                <x-input-error :messages="$errors->get('type_name')" />
                            </div>
                            <div>
                                <x-input-label for="type_description" value="Descripción" />
                                <textarea id="type_description" wire:model="type_description" class="w-full border-gray-300 rounded text-sm h-16"></textarea>
                            </div>
                            
                            <!-- Lógica -->
                            <div class="space-y-2 pt-2 border-t border-gray-200">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model.live="type_requires_payment" class="rounded text-indigo-600">
                                    <span class="text-sm font-medium text-gray-700">¿Cobra dinero?</span>
                                </label>
                                
                                @if($type_requires_payment)
                                    <div>
                                        <x-input-label for="type_payment_amount" value="Monto (RD$)" />
                                        <x-text-input type="number" step="0.01" wire:model="type_payment_amount" class="w-full h-8 text-sm" />
                                    </div>
                                @endif

                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model="type_requires_enrolled_course" class="rounded text-indigo-600">
                                    <span class="text-sm text-gray-600">Requiere materia cursando</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model="type_requires_completed_course" class="rounded text-indigo-600">
                                    <span class="text-sm text-gray-600">Requiere materia aprobada</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" wire:model="type_is_active" class="rounded text-green-600">
                                    <span class="text-sm text-gray-600">Activo</span>
                                </label>
                            </div>

                            <div class="pt-2 flex justify-end gap-2">
                                @if($editingTypeId)
                                    <x-secondary-button wire:click="resetTypeForm" class="text-xs">Cancelar</x-secondary-button>
                                @endif
                                <x-primary-button class="text-xs">{{ $editingTypeId ? 'Actualizar' : 'Guardar' }}</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Columna Derecha: Lista -->
                    <div class="md:col-span-2 overflow-y-auto max-h-[500px]">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left">Nombre</th>
                                    <th class="px-3 py-2 text-left">Reglas</th>
                                    <th class="px-3 py-2 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($requestTypes as $type)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <div class="font-bold {{ $type->is_active ? 'text-gray-800' : 'text-gray-400' }}">{{ $type->name }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($type->description, 30) }}</div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col gap-1">
                                                @if($type->requires_payment)
                                                    <span class="text-xs bg-green-100 text-green-800 px-1.5 rounded w-max">Cobro: ${{ $type->payment_amount }}</span>
                                                @endif
                                                @if($type->requires_enrolled_course)
                                                    <span class="text-xs bg-blue-100 text-blue-800 px-1.5 rounded w-max">Mat. Cursando</span>
                                                @endif
                                                @if($type->requires_completed_course)
                                                    <span class="text-xs bg-purple-100 text-purple-800 px-1.5 rounded w-max">Mat. Aprobada</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button wire:click="editType({{ $type->id }})" class="text-indigo-600 hover:underline mr-2">Editar</button>
                                            <button wire:click="deleteType({{ $type->id }})" class="text-red-600 hover:underline" wire:confirm="¿Eliminar este tipo?">Borrar</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <x-secondary-button wire:click="closeModal">Cerrar</x-secondary-button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- MODAL 3: CREAR SOLICITUD MANUAL -->
    @if($showingCreateModal)
        <x-modal name="create-request-modal" :show="$showingCreateModal" max-width="md" x-on:close="$wire.closeModal()">
            <form wire:submit.prevent="storeRequest" class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Nueva Solicitud Manual</h2>
                
                <div class="space-y-4">
                    <!-- Buscador Estudiante -->
                    <div class="relative">
                        <x-input-label for="new_student_search" value="Buscar Estudiante" />
                        <x-text-input wire:model.live.debounce.300ms="new_student_search" placeholder="Escribe nombre..." class="w-full" />
                        
                        @if(strlen($new_student_search) > 1 && !$new_student_id)
                            <div class="absolute z-10 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-40 overflow-y-auto">
                                @forelse($this->students as $student)
                                    <div wire:click="selectStudent({{ $student->id }})" class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                                        {{ $student->user->name }}
                                    </div>
                                @empty
                                    <div class="px-4 py-2 text-gray-500 text-xs">No encontrado</div>
                                @endforelse
                            </div>
                        @endif
                        @if($new_student_id)
                            <div class="mt-1 text-sm text-green-600 font-bold flex justify-between">
                                <span>Seleccionado: {{ $new_student_search }}</span>
                                <button type="button" wire:click="$set('new_student_id', null)" class="text-red-500 text-xs underline">Cambiar</button>
                            </div>
                        @endif
                    </div>

                    <!-- Tipo de Solicitud -->
                    <div>
                        <x-input-label for="new_request_type_id" value="Tipo de Trámite" />
                        <select wire:model.live="new_request_type_id" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">-- Seleccionar --</option>
                            @foreach($requestTypes as $type)
                                @if($type->is_active)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Selector de Curso (Condicional) -->
                    @if(count($availableCourses) > 0 || ($new_request_type_id && \App\Models\RequestType::find($new_request_type_id)->requires_enrolled_course))
                        <div>
                            <x-input-label for="new_course_id" value="Curso Relacionado (Requerido)" />
                            <select wire:model="new_course_id" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Seleccionar Materia --</option>
                                @foreach($availableCourses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                            @if(empty($availableCourses))
                                <p class="text-xs text-red-500 mt-1">El estudiante no cumple con los requisitos de cursos para este trámite.</p>
                            @endif
                        </div>
                    @endif

                    <div>
                        <x-input-label for="new_details" value="Detalles Adicionales" />
                        <textarea wire:model="new_details" rows="3" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="closeModal">Cancelar</x-secondary-button>
                    <x-primary-button>Crear Solicitud</x-primary-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>