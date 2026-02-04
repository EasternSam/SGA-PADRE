<div class="space-y-8">
    {{-- Sección 1: Información Personal --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500"></div>
        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Información Personal
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <div>
                <x-input-label for="first_name" value="Nombres" />
                <x-text-input wire:model="first_name" id="first_name" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="text" required placeholder="Ej: Juan Alberto" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <!-- Apellido -->
            <div>
                <x-input-label for="last_name" value="Apellidos" />
                <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="text" required placeholder="Ej: Pérez Rodríguez" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" value="Correo Electrónico" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="email" required placeholder="correo@ejemplo.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Teléfono -->
            <div>
                <x-input-label for="phone" value="Teléfono / Celular" />
                <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="text" required placeholder="(809) 000-0000" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <!-- Cédula -->
            <div>
                <x-input-label for="identification_id" value="Cédula de Identidad" />
                <x-text-input wire:model="identification_id" id="identification_id" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="text" required placeholder="00100000001" />
                <p class="text-xs text-gray-500 mt-1">Sin guiones.</p>
                <x-input-error :messages="$errors->get('identification_id')" class="mt-2" />
            </div>

            <!-- Fecha de Nacimiento -->
            <div>
                <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                <x-text-input wire:model="birth_date" id="birth_date" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="date" required />
                <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Sección 2: Información Académica --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
        <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Información Académica
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Carrera -->
            <div class="md:col-span-2">
                <x-input-label for="course_id" value="Carrera de Interés" />
                <div class="relative">
                    <select wire:model="course_id" id="course_id" class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg bg-gray-50 focus:bg-white transition-colors cursor-pointer">
                        <option value="">-- Seleccione una carrera --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-2 bg-blue-50 p-2 rounded border border-blue-100 inline-block">
                    <span class="font-bold text-blue-600">Nota:</span> Selecciona la carrera que deseas cursar. Esto definirá tu plan de estudios.
                </p>
            </div>

            <!-- Escuela Anterior -->
            <div>
                <x-input-label for="previous_school" value="Escuela / Colegio de Procedencia" />
                <x-text-input wire:model="previous_school" id="previous_school" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="text" placeholder="Nombre de la institución" />
                <x-input-error :messages="$errors->get('previous_school')" class="mt-2" />
            </div>

            <!-- Promedio Anterior (Opcional) -->
            <div>
                <x-input-label for="previous_gpa" value="Promedio General (Opcional)" />
                <x-text-input wire:model="previous_gpa" id="previous_gpa" class="block mt-1 w-full bg-gray-50 focus:bg-white transition-colors" type="number" step="0.01" min="0" max="100" placeholder="Ej: 85.5" />
                <x-input-error :messages="$errors->get('previous_gpa')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Botones de Acción --}}
    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
        <x-primary-button class="ml-4 px-6 py-3 text-base bg-indigo-600 hover:bg-indigo-700 shadow-lg transform hover:-translate-y-0.5 transition-all">
            <svg wire:loading.remove wire:target="save" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            {{ __('Enviar Solicitud de Admisión') }}
        </x-primary-button>
    </div>
</div>