<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Portal del Aspirante</h1>
            <p class="text-gray-500 mt-2">Gestiona tu solicitud de ingreso a la universidad.</p>
        </div>

        <x-action-message on="message" class="mb-4" />

        @if($existing_application)
            {{-- VISTA DE DETALLE DE SOLICITUD --}}
            
            <!-- 1. Estado General -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl mb-6 border border-gray-100">
                <div class="p-6 md:p-8">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-full 
                                @if($admission->status == 'pending') bg-yellow-100 text-yellow-600
                                @elseif($admission->status == 'approved') bg-green-100 text-green-600
                                @elseif($admission->status == 'rejected') bg-red-100 text-red-600
                                @endif">
                                @if($admission->status == 'pending')
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @elseif($admission->status == 'approved')
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @elseif($admission->status == 'rejected')
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">
                                    @switch($admission->status)
                                        @case('pending') Solicitud en Revisión @break
                                        @case('approved') ¡Admitido Exitosamente! @break
                                        @case('rejected') Solicitud Requiere Atención @break
                                    @endswitch
                                </h2>
                                <p class="text-sm text-gray-500">Folio: #{{ str_pad($admission->id, 6, '0', STR_PAD_LEFT) }} • Fecha: {{ $admission->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($admission->notes)
                        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold text-blue-800">Mensaje del Departamento de Admisiones:</h3>
                                    <div class="mt-1 text-sm text-blue-700">
                                        <p>{{ $admission->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 2. Información del Aspirante (Columna Izquierda) -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Información Personal</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500">Nombre Completo</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->full_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Documento ID</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->identification_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Correo Electrónico</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Teléfono(s)</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Fecha Nacimiento</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->birth_date->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Dirección</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->address }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Información Académica</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500">Carrera Solicitada</dt>
                                <dd class="font-medium text-indigo-600">{{ $admission->course->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">Escuela de Procedencia</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->previous_school }}</dd>
                            </div>
                            @if($admission->previous_gpa)
                            <div>
                                <dt class="text-gray-500">Promedio Anterior</dt>
                                <dd class="font-medium text-gray-900">{{ $admission->previous_gpa }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- 3. Estado de Documentos (Columna Derecha - Ancha) -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-900">Documentación Entregada</h3>
                            <p class="text-xs text-gray-500">Estado de revisión de tus archivos.</p>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            @foreach($admission->documents as $key => $path)
                                @php
                                    $status = $admission->document_status[$key] ?? 'pending';
                                    $labels = [
                                        'birth_certificate' => 'Acta de Nacimiento',
                                        'id_card' => 'Cédula de Identidad',
                                        'high_school_record' => 'Récord de Notas',
                                        'medical_certificate' => 'Certificado Médico',
                                        'payment_receipt' => 'Recibo de Pago',
                                        'bachelor_certificate' => 'Certificado de Bachiller',
                                        'photo' => 'Fotografía 2x2'
                                    ];
                                    $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
                                @endphp

                                @if($path)
                                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start gap-4 mb-4 sm:mb-0">
                                        <!-- Icono según estado -->
                                        <div class="flex-shrink-0 mt-1">
                                            @if($status == 'approved')
                                                <div class="bg-green-100 p-2 rounded-full">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                </div>
                                            @elseif($status == 'rejected')
                                                <div class="bg-red-100 p-2 rounded-full">
                                                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </div>
                                            @else
                                                <div class="bg-yellow-100 p-2 rounded-full">
                                                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">{{ $label }}</h4>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                    @if($status == 'approved') bg-green-100 text-green-800
                                                    @elseif($status == 'rejected') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                                    @if($status == 'approved') Aprobado
                                                    @elseif($status == 'rejected') Rechazado / Requiere Corrección
                                                    @else En Revisión
                                                    @endif
                                                </span>
                                                <a href="{{ asset('storage/'.$path) }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-900 underline">Ver archivo actual</a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acción de Re-subida si es rechazado -->
                                    <div class="flex-shrink-0 w-full sm:w-auto">
                                        @if($status == 'rejected')
                                            <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                                                <label class="block text-xs font-medium text-red-700 mb-2">Subir corrección:</label>
                                                <div class="flex gap-2">
                                                    <input type="file" wire:model="reupload_files.{{ $key }}" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-100 file:text-red-700 hover:file:bg-red-200">
                                                    <button wire:click="reuploadDocument('{{ $key }}')" 
                                                            wire:loading.attr="disabled"
                                                            class="bg-red-600 text-white px-3 py-1 rounded-md text-xs font-bold hover:bg-red-700 transition">
                                                        Enviar
                                                    </button>
                                                </div>
                                                @error('reupload_files.'.$key) <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                            </div>
                                        @elseif($status == 'approved')
                                            <span class="text-green-500 text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                Validado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- FORMULARIO DE SOLICITUD ORIGINAL (Sin cambios drásticos aquí, solo estilo) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-8">
                    <form wire:submit.prevent="save" class="space-y-8">
                        {{-- (Mantener formulario de creación igual que antes, si lo necesitas te lo paso completo) --}}
                        {{-- ... Código del formulario de creación ... --}}
                        {{-- He omitido el formulario largo de creación para no saturar, asumiendo que ya lo tienes del paso anterior. Si lo necesitas, avísame --}}
                        @include('livewire.admissions.partials.create-form') 
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>