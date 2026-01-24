<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Probador de Sistema de Correos</h2>
                <p class="mb-6 text-gray-600">Utilice este formulario para verificar la configuración SMTP o enviar comunicados personalizados individuales.</p>

                <!-- Mensajes Flash -->
                @if (session()->has('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative">
                        <strong class="font-bold">¡Éxito!</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
                        <strong class="font-bold">Error:</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <form wire:submit.prevent="sendEmail" class="space-y-4 max-w-2xl">
                    
                    <!-- Destinatario -->
                    <div>
                        <x-input-label for="emailTo" :value="__('Destinatario (Correo Electrónico)')" />
                        <x-text-input wire:model="emailTo" id="emailTo" class="block mt-1 w-full" type="email" placeholder="ejemplo@correo.com" required />
                        <x-input-error :messages="$errors->get('emailTo')" class="mt-2" />
                    </div>

                    <!-- Asunto -->
                    <div>
                        <x-input-label for="subject" :value="__('Asunto del Correo')" />
                        <x-text-input wire:model="subject" id="subject" class="block mt-1 w-full" type="text" placeholder="Aviso Importante..." required />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>

                    <!-- Mensaje -->
                    <div>
                        <x-input-label for="messageBody" :value="__('Cuerpo del Mensaje')" />
                        <textarea wire:model="messageBody" id="messageBody" rows="6" 
                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                            placeholder="Escriba su mensaje aquí..."></textarea>
                        <x-input-error :messages="$errors->get('messageBody')" class="mt-2" />
                    </div>

                    <!-- Botón de Envío -->
                    <div class="flex items-center gap-4">
                        <x-primary-button wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendEmail">
                                {{ __('Enviar Correo de Prueba') }}
                            </span>
                            <span wire:loading wire:target="sendEmail">
                                Enviando...
                            </span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>