<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Admisiones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <x-action-message on="message" class="mb-4" />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 text-gray-900">
                    
                    <!-- Filtros -->
                    <div class="mb-6 flex flex-col sm:flex-row gap-4">
                        <div class="w-full sm:w-1/3">
                            <x-input-label for="search" :value="__('Buscar Aspirante')" />
                            <x-text-input wire:model.live.debounce.300ms="search" id="search" class="block mt-1 w-full" type="text" placeholder="Nombre o cédula..." />
                        </div>
                        <div class="w-full sm:w-1/3">
                            <x-input-label for="statusFilter" :value="__('Filtrar por Estado')" />
                            <select wire:model.live="statusFilter" id="statusFilter" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Todos') }}</option>
                                <option value="pending">{{ __('Pendientes') }}</option>
                                <option value="approved">{{ __('Aprobados') }}</option>
                                <option value="rejected">{{ __('Con Correcciones') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabla de Admisiones -->
                    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aspirante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($admissions as $adm)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $adm->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                                    {{ substr($adm->first_name, 0, 1) }}{{ substr($adm->last_name, 0, 1) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $adm->full_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $adm->identification_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">
                                                {{ $adm->course->code ?? 'N/A' }}
                                            </span>
                                            <span class="ml-1">{{ Str::limit($adm->course->name ?? '-', 20) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $adm->status == 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($adm->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                @if($adm->status == 'approved') Aprobado
                                                @elseif($adm->status == 'rejected') Corrección
                                                @else Pendiente
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-secondary-button wire:click="openProcessModal({{ $adm->id }})" class="text-xs">
                                                {{ __('Gestionar') }}
                                            </x-secondary-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $admissions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE REVISIÓN DETALLADA (Estilo Request Management) --}}
    @if($showProcessModal)
        <x-modal name="review-modal" :show="$showProcessModal" maxWidth="4xl" x-on:close="$wire.set('showProcessModal', false)">
            @if($selectedAdmission)
            <div class="bg-white rounded-lg overflow-hidden flex flex-col max-h-[90vh]">
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center sticky top-0 z-10">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Solicitud #{{ str_pad($selectedAdmission->id, 6, '0', STR_PAD_LEFT) }}</h3>
                        <p class="text-sm text-gray-500">Aspirante: {{ $selectedAdmission->full_name }}</p>
                    </div>
                    <button wire:click="$set('showProcessModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Content Scrollable -->
                <div class="p-6 overflow-y-auto flex-1 bg-white">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Columna Izquierda: Datos del Aspirante -->
                        <div class="space-y-6">
                            
                            <!-- Info Personal -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
                                <h4 class="font-bold text-gray-700 mb-3 border-b border-gray-300 pb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    Datos Personales
                                </h4>
                                <div class="grid grid-cols-1 gap-2">
                                    <div class="flex justify-between border-b border-gray-200 pb-1">
                                        <span class="text-gray-500">Documento ID:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedAdmission->identification_id }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-200 pb-1">
                                        <span class="text-gray-500">Nacionalidad:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedAdmission->nationality ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-200 pb-1">
                                        <span class="text-gray-500">Email:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedAdmission->email }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-200 pb-1">
                                        <span class="text-gray-500">Teléfono:</span>
                                        <span class="font-medium text-gray-900">{{ $selectedAdmission->phone }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block mb-1">Dirección:</span>
                                        <span class="font-medium text-gray-900 block text-xs bg-white p-2 rounded border">{{ $selectedAdmission->address }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Académica -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-sm">
                                <h4 class="font-bold text-gray-700 mb-3 border-b border-gray-300 pb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    Datos Académicos
                                </h4>
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-xs text-gray-500 uppercase font-bold block">Carrera de Interés</span>
                                        <div class="text-indigo-700 font-bold bg-indigo-50 px-2 py-1 rounded border border-indigo-100">
                                            {{ $selectedAdmission->course->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase block">Escuela Anterior</span>
                                            <span class="font-medium text-gray-900">{{ $selectedAdmission->previous_school }}</span>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 uppercase block">Promedio (GPA)</span>
                                            <span class="font-medium text-gray-900">{{ $selectedAdmission->previous_gpa ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notas del Admin -->
                            <div>
                                <x-input-label for="admissionNotes" :value="__('Notas de Revisión (Visible para el Aspirante)')" />
                                <textarea wire:model="admissionNotes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-yellow-50" placeholder="Escribe aquí las razones de rechazo o instrucciones para el estudiante..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">Si rechazas algún documento, explica el motivo aquí.</p>
                            </div>
                        </div>

                        <!-- Columna Derecha: Validación de Documentos -->
                        <div class="border-l border-gray-200 pl-0 lg:pl-8">
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center bg-gray-100 p-2 rounded">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Validación de Documentos
                            </h4>
                            
                            <div class="space-y-4">
                                @foreach($selectedAdmission->documents as $key => $path)
                                    @if($path)
                                    <div class="border rounded-lg p-3 transition-all duration-200 
                                        {{ isset($tempDocStatus[$key]) && $tempDocStatus[$key] == 'approved' ? 'bg-green-50 border-green-300 ring-1 ring-green-300' : 
                                          (isset($tempDocStatus[$key]) && $tempDocStatus[$key] == 'rejected' ? 'bg-red-50 border-red-300 ring-1 ring-red-300' : 'bg-white hover:shadow-md') }}">
                                        
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="text-sm font-bold text-gray-700 flex items-center gap-2">
                                                @if(isset($tempDocStatus[$key]) && $tempDocStatus[$key] == 'approved')
                                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                @elseif(isset($tempDocStatus[$key]) && $tempDocStatus[$key] == 'rejected')
                                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                                @endif
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </span>
                                            
                                            <a href="{{ asset('storage/'.$path) }}" target="_blank" class="text-xs bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded hover:bg-gray-50 flex items-center shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                Ver Archivo
                                            </a>
                                        </div>
                                        
                                        <div class="flex gap-2">
                                            <button wire:click="setDocStatus('{{ $key }}', 'approved')" 
                                                class="flex-1 py-1.5 text-xs font-bold rounded shadow-sm transition-all
                                                {{ isset($tempDocStatus[$key]) && $tempDocStatus[$key] == 'approved' 
                                                    ? 'bg-green-600 text-white shadow-inner' 
                                                    : 'bg-white text-gray-600 border border-gray-300 hover:bg-green-50 hover:text-green-700 hover:border-green-300' }}">
                                                ✓ Aprobar
                                            </button>
                                            <button wire:click="setDocStatus('{{ $key }}', 'rejected')" 
                                                class="flex-1 py-1.5 text-xs font-bold rounded shadow-sm transition-all
                                                {{ isset($tempDocStatus[$key]) && $tempDocStatus[$key] == 'rejected' 
                                                    ? 'bg-red-600 text-white shadow-inner' 
                                                    : 'bg-white text-gray-600 border border-gray-300 hover:bg-red-50 hover:text-red-700 hover:border-red-300' }}">
                                                ✕ Rechazar
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Sticky -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center sticky bottom-0 z-10">
                    <div class="text-xs text-gray-500">
                        * Guardar revisión actualiza el estado. 
                        <strong>Aprobar e Inscribir</strong> finaliza el proceso inmediatamente.
                    </div>
                    <div class="flex gap-3">
                        <x-secondary-button wire:click="$set('showProcessModal', false)">Cancelar</x-secondary-button>
                        
                        <!-- BOTÓN NUEVO: APROBACIÓN MANUAL -->
                        @if($selectedAdmission->status !== 'approved')
                            <button wire:click="approveAdmission" 
                                    wire:confirm="¿Está seguro de aprobar e inscribir manualmente a este estudiante? Esto omitirá validaciones pendientes."
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-900 transition ease-in-out duration-150">
                                Aprobar e Inscribir
                            </button>
                        @endif

                        <x-primary-button wire:click="saveReview">
                            Guardar Revisión
                        </x-primary-button>
                    </div>
                </div>
            </div>
            @endif
        </x-modal>
    @endif
</div>