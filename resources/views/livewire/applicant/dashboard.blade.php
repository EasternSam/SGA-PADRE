<div class="container mx-auto p-4 md:p-6 lg:p-8" x-data="{ activeTab: 'info' }">

    <x-action-message on="message" class="fixed top-24 right-6 z-50" />

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Portal del Aspirante</h1>
        <p class="text-gray-500 mt-2">Gestiona tu solicitud de ingreso a la universidad.</p>
    </div>

    @if($existing_application)
        {{-- Encabezado del Perfil de Solicitud --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="md:flex">
                <!-- Avatar e Info Básica -->
                <div class="md:w-1/3 p-6 bg-gray-50 border-b md:border-b-0 md:border-r border-gray-200 text-center">
                    <div class="relative inline-block">
                        <img class="h-32 w-32 rounded-full mx-auto shadow-md mb-4 object-cover"
                             src="{{ isset($admission->documents['photo']) && $admission->documents['photo'] ? asset('storage/'.$admission->documents['photo']) : 'https://ui-avatars.com/api/?name='.urlencode($admission->full_name).'&background=4f46e5&color=ffffff&size=128' }}"
                             alt="Avatar">
                        
                        <div class="absolute bottom-2 right-2 p-2 rounded-full bg-white shadow-sm border border-gray-200">
                             @if($admission->status == 'pending')
                                <svg class="w-6 h-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @elseif($admission->status == 'approved')
                                <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @elseif($admission->status == 'rejected')
                                <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            @endif
                        </div>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900">{{ $admission->full_name }}</h1>
                    <p class="text-sm text-gray-600">{{ $admission->email }}</p>
                    <p class="text-sm text-gray-600 font-mono mt-1">{{ $admission->identification_id }}</p>
                    
                    <span @class([
                        'mt-3 inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium',
                        'bg-yellow-100 text-yellow-800' => $admission->status === 'pending',
                        'bg-green-100 text-green-800' => $admission->status === 'approved',
                        'bg-red-100 text-red-800' => $admission->status === 'rejected',
                    ])>
                        @switch($admission->status)
                            @case('pending') En Revisión @break
                            @case('approved') Admitido @break
                            @case('rejected') Requiere Atención @break
                        @endswitch
                    </span>
                </div>

                <!-- Información Detallada y Feedback -->
                <div class="md:w-2/3 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Detalles de la Solicitud</h2>
                        <span class="text-xs text-gray-500">Folio #{{ str_pad($admission->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    @if($admission->notes)
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold text-blue-800">Mensaje de Admisiones:</h3>
                                    <div class="mt-1 text-sm text-blue-700">
                                        <p>{!! nl2br(e($admission->notes)) !!}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong class="text-gray-500 block">Carrera Solicitada:</strong>
                            <span class="text-gray-900 font-semibold text-lg">{{ $admission->course->name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-500 block">Fecha Solicitud:</strong>
                            <span class="text-gray-900">{{ $admission->created_at->format('d/m/Y h:i A') }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-500 block">Teléfono:</strong>
                            <span class="text-gray-900">{{ $admission->phone }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-500 block">Escuela Procedencia:</strong>
                            <span class="text-gray-900">{{ $admission->previous_school }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pestañas y Contenido --}}
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Pestañas -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button @click="activeTab = 'info'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'info', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'info' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Información Personal
                    </button>
                    <button @click="activeTab = 'docs'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'docs', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'docs' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                        Documentación
                        @if(is_array($admission->document_status) && collect($admission->document_status)->contains('rejected'))
                            <span class="ml-2 bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs font-bold">!</span>
                        @endif
                    </button>
                </nav>
            </div>

            <!-- Tab: Información Personal -->
            <div class="p-6 md:p-8" x-show="activeTab === 'info'" x-cloak>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos del Aspirante</h3>
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $admission->first_name }} {{ $admission->last_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Cédula / Pasaporte</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $admission->identification_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de Nacimiento</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $admission->birth_date->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $admission->address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Enfermedad / Condición</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $admission->disease ?? 'Ninguna especificada' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Lugar de Trabajo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $admission->work_place ?? 'No aplica' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Tab: Documentación -->
            <div class="p-6" x-show="activeTab === 'docs'" x-cloak>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Estado de Documentos</h3>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Los documentos rechazados deben volverse a subir.</span>
                </div>

                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($admission->documents as $key => $path)
                                @if($path)
                                    @php
                                        $status = isset($admission->document_status) && is_array($admission->document_status) ? ($admission->document_status[$key] ?? 'pending') : 'pending';
                                        $label = match($key) {
                                            'birth_certificate' => 'Acta de Nacimiento',
                                            'id_card' => 'Cédula de Identidad',
                                            'high_school_record' => 'Récord de Notas',
                                            'medical_certificate' => 'Certificado Médico',
                                            'payment_receipt' => 'Recibo de Pago',
                                            'bachelor_certificate' => 'Certificado de Bachiller',
                                            'photo' => 'Fotografía 2x2',
                                            default => ucwords(str_replace('_', ' ', $key))
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $label }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span @class([
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-green-100 text-green-800' => $status === 'approved',
                                                'bg-red-100 text-red-800' => $status === 'rejected',
                                                'bg-yellow-100 text-yellow-800' => $status === 'pending',
                                            ])>
                                                @if($status == 'approved') Aprobado
                                                @elseif($status == 'rejected') Rechazado
                                                @else En Revisión
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($status == 'rejected')
                                                <div class="flex items-center justify-end gap-2">
                                                    <!-- Input con wire:model -->
                                                    <div class="relative">
                                                        <input type="file" 
                                                               wire:model="reupload_files.{{ $key }}" 
                                                               class="block w-48 text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                                                        
                                                        <!-- Mensaje de Carga (Subiendo...) -->
                                                        <div wire:loading wire:target="reupload_files.{{ $key }}" class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center">
                                                            <span class="text-xs text-indigo-600 font-bold">Subiendo...</span>
                                                        </div>
                                                    </div>

                                                    <!-- Botón Reenviar (Se bloquea mientras sube) -->
                                                    <button wire:click="reuploadDocument('{{ $key }}')" 
                                                            wire:loading.attr="disabled"
                                                            wire:target="reupload_files.{{ $key }}"
                                                            class="text-red-600 hover:text-red-900 font-bold text-xs underline disabled:opacity-50 disabled:cursor-not-allowed">
                                                        Reenviar
                                                    </button>
                                                </div>
                                                @error('reupload_files.'.$key) <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                            @else
                                                <a href="{{ asset('storage/'.$path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 flex items-center justify-end gap-1">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                    Ver
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        {{-- FORMULARIO DE CREACIÓN (Se mantiene el original encapsulado) --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8">
                <form wire:submit.prevent="save" class="space-y-8">
                    @include('livewire.admissions.partials.create-form')
                </form>
            </div>
        </div>
    @endif
</div>