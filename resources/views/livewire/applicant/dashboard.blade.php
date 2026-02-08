<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Portal del Aspirante</h1>
            <p class="text-gray-500">Gestiona tu solicitud de ingreso a la universidad.</p>
        </div>

        @if($existing_application)
            {{-- VISTA DE ESTADO DE SOLICITUD --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- Estado General --}}
                    <div class="flex items-center justify-between mb-8 p-4 rounded-lg 
                        @if($admission->status == 'pending') bg-yellow-50 border border-yellow-200
                        @elseif($admission->status == 'approved') bg-green-50 border border-green-200
                        @elseif($admission->status == 'rejected') bg-red-50 border border-red-200
                        @endif">
                        
                        <div class="flex items-center">
                            <div class="mr-4">
                                @if($admission->status == 'pending')
                                    <svg class="w-8 h-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @elseif($admission->status == 'approved')
                                    <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @elseif($admission->status == 'rejected')
                                    <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Estado de la Solicitud: 
                                    @switch($admission->status)
                                        @case('pending') Pendiente de Revisión @break
                                        @case('approved') Aprobada e Inscrita @break
                                        @case('rejected') Rechazada / Requiere Cambios @break
                                    @endswitch
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Enviada el: {{ $admission->created_at->format('d/m/Y h:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Notas del Administrador (Feedback en tiempo real) --}}
                    @if($admission->notes)
                        <div class="mb-8 bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Mensaje de Admisiones:</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>{{ $admission->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Lista de Documentos Subidos --}}
                    <h3 class="text-md font-bold text-gray-800 mb-4">Documentación Entregada</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($admission->documents as $key => $path)
                            @if($path)
                                <div class="border rounded-lg p-4 flex items-center space-x-3 bg-gray-50">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                        </p>
                                        <p class="text-xs text-green-600">Almacenado Seguro</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>
            </div>
        @else
            {{-- FORMULARIO DE SOLICITUD --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form wire:submit.prevent="save" class="space-y-6">
                        
                        {{-- Datos Personales --}}
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Información Personal</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="first_name" value="Nombres" />
                                    <x-text-input id="first_name" class="block mt-1 w-full" type="text" wire:model="first_name" />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="last_name" value="Apellidos" />
                                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" wire:model="last_name" />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="identification_id" value="Cédula / Pasaporte" />
                                    <x-text-input id="identification_id" class="block mt-1 w-full" type="text" wire:model="identification_id" />
                                    <x-input-error :messages="$errors->get('identification_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                                    <x-text-input id="birth_date" class="block mt-1 w-full" type="date" wire:model="birth_date" />
                                    <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="nationality" value="Nacionalidad" />
                                    <x-text-input id="nationality" class="block mt-1 w-full" type="text" wire:model="nationality" />
                                    <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Teléfono" />
                                    <x-text-input id="phone" class="block mt-1 w-full" type="text" wire:model="phone" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="address" value="Dirección Completa" />
                                    <x-text-input id="address" class="block mt-1 w-full" type="text" wire:model="address" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- Información Académica --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Información Académica</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="course_id" value="Carrera de Interés" />
                                    <select id="course_id" wire:model="course_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Seleccione...</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="previous_school" value="Escuela de Procedencia" />
                                    <x-text-input id="previous_school" class="block mt-1 w-full" type="text" wire:model="previous_school" />
                                    <x-input-error :messages="$errors->get('previous_school')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="previous_gpa" value="Promedio (Opcional)" />
                                    <x-text-input id="previous_gpa" class="block mt-1 w-full" type="number" step="0.01" wire:model="previous_gpa" />
                                </div>
                            </div>
                        </div>

                        {{-- Carga de Documentos --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Documentación Requerida</h3>
                            <div class="text-xs text-indigo-600 mb-4 bg-indigo-50 p-3 rounded border border-indigo-100">
                                <p><strong>Nota:</strong> Solo se permiten archivos en formato <strong>PDF, JPG, PNG</strong>. Tamaño máximo: 5MB.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                {{-- Acta de Nacimiento --}}
                                <div>
                                    <x-input-label value="Acta de Nacimiento" />
                                    <input type="file" wire:model="file_birth_certificate" 
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    @error('file_birth_certificate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Cédula --}}
                                <div>
                                    <x-input-label value="Cédula de Identidad" />
                                    <input type="file" wire:model="file_id_card" 
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    @error('file_id_card') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Récord de Notas --}}
                                <div>
                                    <x-input-label value="Récord de Notas" />
                                    <input type="file" wire:model="file_high_school_record" 
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    @error('file_high_school_record') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Foto 2x2 --}}
                                <div>
                                    <x-input-label value="Foto 2x2" />
                                    <input type="file" wire:model="file_photo" 
                                           accept="image/png, image/jpeg, image/jpg"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    @error('file_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <div wire:loading class="mr-4 text-gray-600">Subiendo archivos de forma segura...</div>
                            <x-primary-button wire:loading.attr="disabled">
                                Enviar Solicitud
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>