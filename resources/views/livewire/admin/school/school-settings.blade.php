<div class="p-6 max-w-4xl mx-auto">
    <div class="workspace-panel shadow-md border border-gray-200/80 bg-white rounded-2xl p-8">
        <div class="mb-8 pb-4 border-b border-gray-100">
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Configuración del Centro Educativo</h1>
            <p class="text-sm text-gray-500 mt-1">Datos oficiales del centro ante el Ministerio de Educación (MINERD)</p>
        </div>

        @if(session()->has('message'))
            <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-4 text-sm font-semibold text-green-800 flex items-center gap-2 animate-fade-in" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">

            {{-- Datos del Centro --}}
            <div class="p-6 bg-slate-50/50 rounded-xl border border-gray-200/80">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[rgb(var(--color-primary))]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    Identificación del Centro
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Nombre del Centro Educativo *</label>
                        <input type="text" wire:model="school_name" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="Ej: Colegio San José" />
                        @error('school_name') <span class="text-xs text-red-500 mt-1 block font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Código MINERD</label>
                        <input type="text" wire:model="minerd_code" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="Ej: 15-01-0234" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">RNC</label>
                        <input type="text" wire:model="rnc" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="Ej: 130-12345-6" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Tipo de Centro *</label>
                        <select wire:model="school_type" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150">
                            @foreach($schoolTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Nivel Educativo *</label>
                        <select wire:model="level" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150">
                            @foreach($levels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Tanda *</label>
                        <select wire:model="shift" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150">
                            @foreach($shifts as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Lema</label>
                        <input type="text" wire:model="motto" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="Ej: Formando líderes del mañana" />
                    </div>
                </div>
            </div>

            {{-- Ubicación MINERD --}}
            <div class="p-6 bg-slate-50/50 rounded-xl border border-gray-200/80">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[rgb(var(--color-primary))]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Ubicación y Regional MINERD
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Regional Educativa</label>
                        <select wire:model="regional" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150">
                            <option value="">Seleccionar...</option>
                            @foreach($regionals as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Distrito Educativo</label>
                        <input type="text" wire:model="district" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="Ej: 15-01" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Provincia</label>
                        <select wire:model="province" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150">
                            <option value="">Seleccionar...</option>
                            @foreach($provinces as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Ciudad/Municipio</label>
                        <input type="text" wire:model="city" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Dirección</label>
                        <input type="text" wire:model="address" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="Calle, número, sector" />
                    </div>
                </div>
            </div>

            {{-- Director --}}
            <div class="p-6 bg-slate-50/50 rounded-xl border border-gray-200/80">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[rgb(var(--color-primary))]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Director/a del Centro
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Nombre Completo</label>
                        <input type="text" wire:model="director_name" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Cédula</label>
                        <input type="text" wire:model="director_cedula" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="000-0000000-0" />
                    </div>
                </div>
            </div>

            {{-- Contacto --}}
            <div class="p-6 bg-slate-50/50 rounded-xl border border-gray-200/80">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="h-4 w-4 text-[rgb(var(--color-primary))]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    Contacto
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Teléfono</label>
                        <input type="text" wire:model="phone" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" placeholder="809-000-0000" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Email</label>
                        <input type="email" wire:model="email" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Sitio Web</label>
                        <input type="url" wire:model="website" class="w-full mt-1 px-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-200 rounded-lg shadow-sm focus:border-[rgb(var(--color-primary))] focus:ring-4 focus:ring-[rgb(var(--color-primary))]/15 focus:outline-none transition duration-150" />
                    </div>
                </div>
            </div>

            {{-- Logo --}}
            <div class="p-6 bg-slate-50/50 rounded-xl border border-gray-200/80">
                <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">Logo del Centro</h2>
                <div class="flex flex-col sm:flex-row items-center gap-6 p-4 bg-white border border-gray-200 rounded-xl">
                    @if($currentConfig?->logo_path)
                        <div class="shrink-0 p-2 bg-gray-50 rounded-lg border border-gray-100">
                            <img src="{{ asset('storage/' . $currentConfig->logo_path) }}" class="h-20 w-20 object-contain" alt="Logo actual" />
                        </div>
                    @endif
                    <div class="flex-1">
                        <input type="file" wire:model="new_logo" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-[rgb(var(--color-primary))] hover:file:bg-blue-100 cursor-pointer" />
                        <p class="text-xs text-gray-400 mt-2">Formatos aceptados: PNG, JPG o WEBP. Máximo 2MB. Aparecerá en boletines y documentos oficiales.</p>
                    </div>
                </div>
            </div>

            {{-- Guardar --}}
            <div class="flex justify-end pt-4">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-8 py-3 text-sm font-bold text-white shadow-md hover:opacity-95 focus:ring-4 focus:ring-[rgb(var(--color-primary))]/40 transition duration-150" style="background-color: rgb(var(--color-primary)) !important; background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0%, rgba(0, 0, 0, 0.12) 100%) !important; border: 1px solid rgba(0, 0, 0, 0.15) !important;">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd" /></svg>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
