<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGA Padre') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN (Para asegurar visualización inmediata) -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Alpine.js -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Scripts del Proyecto -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // Lógica de Logo Dinámico
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp

        <style>
            /* --- Configuración de Fuente --- */
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            
            /* --- Pattern Background para el lado izquierdo --- */
            .pattern-grid {
                background-color: #4f46e5; /* Indigo 600 */
                background-image: 
                    radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                    radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                    radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
                position: relative;
                overflow: hidden;
            }
            
            /* Elementos decorativos abstractos */
            .circle-deco {
                position: absolute;
                border-radius: 50%;
                background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
            }

            /* --- ESTILOS DE ADAPTACIÓN PARA COMPONENTES BREEZE/JETSTREAM --- */
            /* Estos estilos aseguran que los inputs del slot se vean bien sin editar sus archivos */
            
            .auth-form-wrapper label {
                display: block;
                font-size: 0.875rem;
                font-weight: 600;
                color: #374151; /* Gray 700 */
                margin-bottom: 0.5rem;
            }

            .auth-form-wrapper input[type="text"],
            .auth-form-wrapper input[type="email"],
            .auth-form-wrapper input[type="password"] {
                display: block;
                width: 100%;
                border-radius: 0.5rem;
                border: 1px solid #d1d5db; /* Gray 300 */
                padding: 0.75rem 1rem;
                color: #111827;
                background-color: #f9fafb;
                transition: all 0.2s;
                font-size: 0.95rem;
                margin-bottom: 1rem; /* Espacio entre inputs */
            }

            .auth-form-wrapper input:focus {
                outline: none;
                background-color: white;
                border-color: #6366f1; /* Indigo 500 */
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            /* Checkbox */
            .auth-form-wrapper input[type="checkbox"] {
                border-radius: 0.25rem;
                border-color: #d1d5db;
                color: #4f46e5;
                width: 1rem;
                height: 1rem;
                margin-right: 0.5rem;
            }

            /* Botones primarios dentro del slot */
            .auth-form-wrapper button[type="submit"], 
            .auth-form-wrapper .inline-flex {
                width: 100%;
                justify-content: center;
                border-radius: 0.5rem;
                background-color: #4f46e5; /* Indigo 600 */
                padding: 0.75rem 1.5rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: white;
                box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
                transition: all 0.2s;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-top: 1rem;
                cursor: pointer;
            }

            .auth-form-wrapper button[type="submit"]:hover {
                background-color: #4338ca; /* Indigo 700 */
                transform: translateY(-1px);
            }

            /* Links (Olvido su contraseña, etc) */
            .auth-form-wrapper a {
                color: #6366f1;
                font-size: 0.875rem;
                text-decoration: none;
                font-weight: 500;
            }
            .auth-form-wrapper a:hover {
                color: #4338ca;
                text-decoration: underline;
            }

            /* Flexibilidad para la fila de "Recordarme" y links */
            .auth-form-wrapper .block.mt-4 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 1.5rem;
                margin-bottom: 1.5rem;
            }
        </style>
    </head>
    <body class="h-full">
        
        <div class="flex min-h-screen w-full">
            
            <!-- SECCIÓN IZQUIERDA: Visual y Branding (Oculto en móviles) -->
            <div class="hidden lg:flex w-1/2 relative pattern-grid items-center justify-center p-12 overflow-hidden">
                
                <!-- Decoración animada sutil -->
                <div class="circle-deco w-96 h-96 -top-20 -left-20" x-data x-animate></div>
                <div class="circle-deco w-64 h-64 bottom-10 right-10 opacity-50"></div>

                <div class="relative z-10 max-w-lg text-white">
                    <div class="mb-8">
                        <!-- Logo Dinámico -->
                        <div class="mb-6 flex justify-start">
                             @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-20 w-auto object-contain bg-white/10 rounded-2xl p-2 backdrop-blur-sm border border-white/20">
                            @else
                                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/20">
                                    <x-application-logo class="w-10 h-10 fill-current text-white" />
                                </div>
                            @endif
                        </div>

                        <h2 class="text-4xl font-bold tracking-tight mb-4">Bienvenido a {{ config('app.name') }}</h2>
                        <p class="text-indigo-100 text-lg leading-relaxed opacity-90">
                            Gestión académica simplificada para padres y tutores. 
                            Accede al rendimiento escolar, asistencia y comunicados en tiempo real.
                        </p>
                    </div>
                    
                    <!-- Tarjeta de Info / Aviso -->
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/10">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">¿Necesitas ayuda?</h3>
                                <p class="text-sm text-indigo-200 mt-1">Si tienes problemas para ingresar, contacta a secretaría académica.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyright footer izquierda -->
                <div class="absolute bottom-8 left-12 text-indigo-300 text-xs">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Sistema de Gestión Académica.
                </div>
            </div>

            <!-- SECCIÓN DERECHA: Formulario de Login -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white p-6 lg:p-12 xl:p-24 relative">
                
                <!-- Botón flotante para regresar (opcional, por si es necesario) -->
                <div class="absolute top-6 right-6">
                    <a href="/" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition flex items-center gap-1">
                        Ir al inicio
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>

                <div class="w-full max-w-md mx-auto" x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)">
                    
                    <!-- Animación de entrada -->
                    <div x-show="show" 
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">

                        <!-- Header del Formulario -->
                        <div class="text-center mb-10">
                            <!-- Logo Móvil (Solo visible en pantallas pequeñas) -->
                            <div class="lg:hidden inline-flex justify-center items-center mb-6">
                                @if($logoUrl)
                                    <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-16 w-auto object-contain drop-shadow-sm hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="h-12 w-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                                        <x-application-logo class="w-8 h-8 fill-current text-white" />
                                    </div>
                                @endif
                            </div>

                            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ config('app.name') }}</h1>
                            <p class="text-sm text-gray-500 mt-2">Ingresa tus credenciales para acceder a tu cuenta</p>
                        </div>

                        <!-- SLOT del Formulario (Contenedor Wrapper) -->
                        <!-- Aquí es donde Laravel inyecta el form (login.blade.php) -->
                        <div class="auth-form-wrapper">
                            {{ $slot }}
                        </div>

                        <!-- Footer Móvil (visible solo en pantallas pequeñas) -->
                        <div class="lg:hidden mt-8 text-center text-xs text-gray-400">
                            &copy; {{ date('Y') }} {{ config('app.name') }}.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </body>
</html>