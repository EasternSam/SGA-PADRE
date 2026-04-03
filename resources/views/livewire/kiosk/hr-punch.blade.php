<div class="min-h-screen bg-sga-background flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        @if(isset($branding) && !empty($branding->logo_url))
            <img class="mx-auto h-20 w-auto object-contain bg-white rounded-xl p-2 shadow-lg" src="{{ asset($branding->logo_url) }}" alt="Logo">
        @else
            <x-application-logo class="mx-auto h-16 w-auto text-white fill-current" />
        @endif
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">Reloj de Personal (RRHH)</h2>
        <p class="mt-2 text-center text-sm text-gray-300">
            Kiosco Web Híbrido ZKTeco
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            
            <div class="text-center mb-6" wire:poll.1000ms>
                <div class="text-4xl font-bold text-gray-800 tracking-tight">{{ now()->format('h:i:s A') }}</div>
                <div class="text-sm text-gray-500">{{ now()->translatedFormat('l, d de F Y') }}</div>
            </div>

            @if (session()->has('success'))
                <div class="rounded-md bg-green-50 p-4 mb-6 border border-green-200 shadow-sm animate-in fade-in slide-in-from-top-2">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="rounded-md bg-red-50 p-4 mb-6 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="punch(0)" class="space-y-6">
                <div>
                    <label for="biometricId" class="sr-only">ID Biométrico (Emp Code)</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-fingerprint text-gray-400 text-xl"></i>
                        </div>
                        <input wire:model="biometricId" type="number" id="biometricId" class="focus:ring-sga-primary focus:border-sga-primary block w-full pl-12 border-gray-300 rounded-lg py-4 text-center text-2xl tracking-widest font-bold placeholder-gray-300" placeholder="Su ID (Ej: 105)" required autofocus>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button type="button" wire:click="punch(0)" class="w-full flex flex-col justify-center items-center py-4 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors uppercase">
                        <i class="fas fa-sign-in-alt text-2xl mb-1"></i> Entrada
                    </button>
                    <button type="button" wire:click="punch(1)" class="w-full flex flex-col justify-center items-center py-4 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors uppercase">
                        <i class="fas fa-sign-out-alt text-2xl mb-1"></i> Salida
                    </button>
                </div>
            </form>
            
            <div class="mt-6 pt-4 border-t border-gray-100 flex justify-center text-xs text-gray-400">
                <i class="fas fa-shield-alt mr-1"></i> Acceso Seguro SGA-CENTU
            </div>
        </div>
    </div>
</div>
