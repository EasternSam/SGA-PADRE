<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 pb-12">
    <div class="w-full sm:max-w-3xl mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
        
        <div class="mb-8 text-center">
            {{-- <x-application-logo class="w-20 h-20 fill-current text-gray-500 mx-auto" /> --}}
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Solicitud de Admisión</h2>
            <p class="text-gray-600">Completa el formulario y adjunta los documentos requeridos.</p>
        </div>

        @if($success)
            <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-green-800 mb-2">¡Solicitud Enviada con Éxito!</h3>
                <p class="text-green-700 mb-6">Hemos recibido tu documentación. El departamento de admisiones revisará tus archivos y te contactará vía correo electrónico.</p>
                <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    Volver al Inicio
                </a>
            </div>
        @else
            <form wire:submit.prevent="save" class="space-y-8" enctype="multipart/form-data">
                
                {{-- 1. Datos Personales --}}
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">
                        1. Datos Personales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="first_name" value="Nombres" />
                            <x-text-input id="first_name" class="block mt-1 w-full" type="text" wire:model="first_name" required />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="last_name" value="Apellidos" />
                            <x-text-input id="last_name" class="block mt-1 w-full" type="text" wire:model="last_name" required />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="identification_id" value="Cédula o Pasaporte (Sin guiones)" />
                            <x-text-input id="identification_id" class="block mt-1 w-full" type="text" wire:model="identification_id" required placeholder="00000000000" />
                            <x-input-error :messages="$errors->get('identification_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                            <x-text-input id="birth_date" class="block mt-1 w-full" type="date" wire:model="birth_date" required />
                            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nationality" value="Nacionalidad" />
                            <x-text-input id="nationality" class="block mt-1 w-full" type="text" wire:model="nationality" required placeholder="Dominicana" />
                            <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- 2. Contacto y Dirección --}}
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">
                        2. Contacto y Dirección
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <x-input-label for="address" value="Dirección Completa (Calle, Número, Sector, Ciudad)" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" wire:model="address" required />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Correo Electrónico" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" wire:model="email" required placeholder="ejemplo@correo.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <x-input-label for="phone" value="Teléfono 1" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="text" wire:model="phone" required placeholder="809-000-0000" />
                            </div>
                            <div>
                                <x-input-label for="phone2" value="Teléfono 2 (Opcional)" />
                                <x-text-input id="phone2" class="block mt-1 w-full" type="text" wire:model="phone2" placeholder="809-000-0000" />
                            </div>
                            <x-input-error :messages="$errors->get('phone')" class="mt-2 col-span-2" />
                        </div>
                    </div>
                </div>

                {{-- 3. Información Adicional --}}
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">
                        3. Información Adicional
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-input-label value="¿Trabaja actualmente?" />
                            <div class="mt-2 flex gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model.live="works" value="si" class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <span class="ml-2">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model.live="works" value="no" class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>
                        
                        @if($works === 'si')
                        <div>
                            <x-input-label for="work_place" value="Lugar de Trabajo" />
                            <x-text-input id="work_place" class="block mt-1 w-full" type="text" wire:model="work_place" />
                            <x-input-error :messages="$errors->get('work_place')" class="mt-2" />
                        </div>
                        @endif

                        <div class="md:col-span-2">
                            <x-input-label for="disease" value="Enfermedad congénita o de alto riesgo (Opcional)" />
                            <x-text-input id="disease" class="block mt-1 w-full" type="text" wire:model="disease" placeholder="Especifique si aplica" />
                        </div>
                    </div>
                </div>

                {{-- 4. Información Académica --}}
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">
                        4. Información Académica
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <x-input-label for="course_id" value="Carrera de Interés" />
                            <select id="course_id" wire:model="course_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Seleccione una carrera...</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="previous_school" value="Centro Educativo de Procedencia" />
                            <x-text-input id="previous_school" class="block mt-1 w-full" type="text" wire:model="previous_school" required />
                        </div>
                        <div>
                            <x-input-label for="previous_gpa" value="Promedio Bachillerato (Opcional)" />
                            <x-text-input id="previous_gpa" class="block mt-1 w-full" type="number" step="0.01" wire:model="previous_gpa" />
                        </div>
                    </div>
                </div>

                {{-- 5. Documentación (Archivos) --}}
                <div class="bg-indigo-50 p-5 rounded-xl border border-indigo-100">
                    <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-4 border-b border-indigo-200 pb-2">
                        5. Requisitos y Documentación
                    </h3>
                    <div class="text-xs text-indigo-600 mb-4 bg-white p-3 rounded border border-indigo-100">
                        <p><strong>Nota:</strong> Sube los archivos en formato PDF, JPG o PNG. Tamaño máximo: 5MB por archivo.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Acta de Nacimiento --}}
                        <div>
                            <x-input-label value="Acta de Nacimiento (Original, Legalizada)" />
                            <input type="file" wire:model="file_birth_certificate" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_birth_certificate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Cédula --}}
                        <div>
                            <x-input-label value="Copia de Cédula de Identidad" />
                            <input type="file" wire:model="file_id_card" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_id_card') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Récord de Notas --}}
                        <div>
                            <x-input-label value="Récord de Notas Secundaria (Firmado)" />
                            <input type="file" wire:model="file_high_school_record" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_high_school_record') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Certificado Médico --}}
                        <div>
                            <x-input-label value="Certificado Médico Vigente" />
                            <input type="file" wire:model="file_medical_certificate" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_medical_certificate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Recibo Pago --}}
                        <div>
                            <x-input-label value="Recibo Pago Derecho Admisión" />
                            <input type="file" wire:model="file_payment_receipt" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_payment_receipt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Certificación Bachiller --}}
                        <div>
                            <x-input-label value="Certificación de Bachiller" />
                            <input type="file" wire:model="file_bachelor_certificate" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_bachelor_certificate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Foto 2x2 --}}
                        <div class="md:col-span-2">
                            <x-input-label value="Fotografía 2x2 (Fondo claro)" />
                            <input type="file" wire:model="file_photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('file_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            
                            @if ($file_photo) 
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">Vista previa:</p>
                                    <img src="{{ $file_photo->temporaryUrl() }}" class="h-24 w-24 object-cover rounded-md border" />
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                    <div wire:loading class="mr-4 text-indigo-600 font-medium text-sm">
                        Enviando solicitud, por favor espera...
                    </div>
                    <x-primary-button class="ml-4" wire:loading.attr="disabled">
                        {{ __('Enviar Solicitud de Admisión') }}
                    </x-primary-button>
                </div>
            </form>
        @endif
    </div>
</div>