<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGA Padre') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        },
                        animation: {
                            'blob': 'blob 7s infinite',
                        },
                        keyframes: {
                            blob: {
                                '0%': { transform: 'translate(0px, 0px) scale(1)' },
                                '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                                '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                                '100%': { transform: 'translate(0px, 0px) scale(1)' },
                            }
                        }
                    }
                }
            }
        </script>
        
        <!-- Alpine.js -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Scripts del Proyecto -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // --- Lógica de Fondo Dinámico (Recuperada del contexto anterior) ---
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type');

            // Reconstruir gradiente si es necesario
            if ($type === 'gradient' && !str_contains($bg, 'gradient')) {
                $start = SystemOption::getOption('navbar_gradient_start', '#4f46e5');
                $end = SystemOption::getOption('navbar_gradient_end', '#0f172a');
                $dir = SystemOption::getOption('navbar_gradient_direction', 'to bottom right');
                $bg = "linear-gradient({$dir}, {$start}, {$end})";
            }

            // Fallback elegante (Indigo profundo a Slate)
            if (empty($bg) || $bg === '#' || $bg === 'red' || $bg === '#red') {
                $bg = 'linear-gradient(135deg, #4f46e5 0%, #0f172a 100%)'; 
            }

            // Lógica de Logo
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp

        <style>
            /* --- Clases de Utilidad Personalizadas --- */
            .dynamic-bg-panel {
                background: {{ $bg }} !important;
                position: relative;
                overflow: hidden;
            }

            /* Patrón de puntos sutil */
            .dot-pattern {
                background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px);
                background-size: 24px 24px;
            }

            /* --- Adaptación para Inputs de Laravel Breeze --- */
            /* Esto estiliza los inputs inyectados en {{ $slot }} para que se vean modernos */
            
            .auth-form-wrapper label {
                display: block;
                font-size: 0.875rem;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
            }

            .auth-form-wrapper input[type="text"],
            .auth-form-wrapper input[type="email"],
            .auth-form-wrapper input[type="password"] {
                display: block;
                width: 100%;
                border-radius: 0.75rem; /* Más redondeado */
                border: 1px solid #e5e7eb;
                padding: 0.875rem 1rem;
                color: #1f2937;
                background-color: #ffffff;
                transition: all 0.2s ease-in-out;
                font-size: 0.95rem;
                margin-bottom: 1.25rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            }

            .auth-form-wrapper input:focus {
                outline: none;
                border-color: #6366f1;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); /* Ring suave */
            }

            /* Checkbox */
            .auth-form-wrapper input[type="checkbox"] {
                border-radius: 0.375rem;
                border-color: #d1d5db;
                color: #4f46e5;
                width: 1.1rem;
                height: 1.1rem;
                margin-right: 0.5rem;
                cursor: pointer;
            }

            /* Botones */
            .auth-form-wrapper button[type="submit"], 
            .auth-form-wrapper .inline-flex {
                display: flex;
                width: 100%;
                justify-content: center;
                border-radius: 0.75rem;
                background: {{ $bg }}; /* Usa el color del tema también para el botón */
                padding: 0.875rem 1.5rem;
                font-size: 0.95rem;
                font-weight: 600;
                color: white;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                transition: all 0.2s;
                letter-spacing: 0.025em;
                margin-top: 1.5rem;
                cursor: pointer;
                border: 1px solid rgba(255,255,255,0.1);
            }

            .auth-form-wrapper button[type="submit"]:hover {
                filter: brightness(110%);
                transform: translateY(-1px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }

            /* Enlaces */
            .auth-form-wrapper a {
                color: #4f46e5;
                font-size: 0.875rem;
                text-decoration: none;
                font-weight: 600;
                transition: color 0.2s;
            }
            .auth-form-wrapper a:hover {
                color: #4338ca;
            }

            /* Utilidades para layout interno del slot */
            .auth-form-wrapper .block.mt-4 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 1.5rem;
                margin-bottom: 1.5rem;
            }
        </style>
    </head>
    <body class="h-full antialiased font-sans text-gray-900 bg-white">
        
        <div class="flex min-h-screen w-full">
            
            <!-- SECCIÓN IZQUIERDA: Visual y Branding -->
            <!-- Utilizamos dynamic-bg-panel para inyectar el color de la BD -->
            <div class="hidden lg:flex lg:w-1/2 relative dynamic-bg-panel items-center justify-center p-12 z-0">
                
                <!-- Capas Decorativas (Animación Aurora Sutil) -->
                <div class="absolute inset-0 dot-pattern opacity-30"></div>
                
                <!-- Orbes animados con CSS puro (Tailwind config arriba) -->
                <div class="absolute top-0 -left-4 w-96 h-96 bg-white mix-blend-overlay rounded-full filter blur-[128px] opacity-20 animate-blob"></div>
                <div class="absolute top-0 -right-4 w-96 h-96 bg-purple-500 mix-blend-overlay rounded-full filter blur-[128px] opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-96 h-96 bg-pink-500 mix-blend-overlay rounded-full filter blur-[128px] opacity-20 animate-blob animation-delay-4000"></div>

                <!-- Contenido Central del Panel Izquierdo -->
                <div class="relative z-10 w-full max-w-lg">
                    <div class="space-y-8" x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)">
                        
                        <!-- Icono / Badge -->
                        <div class="w-20 h-20 bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl flex items-center justify-center shadow-2xl transition-all duration-700 transform"
                             x-show="show"
                             x-transition:enter="transition ease-out duration-700"
                             x-transition:enter-start="opacity-0 translate-y-10 rotate-12"
                             x-transition:enter-end="opacity-100 translate-y-0 rotate-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <!-- Texto de Bienvenida -->
                        <div x-show="show"
                             x-transition:enter="transition ease-out duration-700 delay-100"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <h2 class="text-5xl font-bold tracking-tight text-white mb-6 leading-tight">
                                Bienvenido al <br/>
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-200">Portal Académico</span>
                            </h2>
                            <p class="text-lg text-blue-100 leading-relaxed font-medium max-w-md">
                                Gestión académica simplificada para padres y tutores. 
                                Accede al rendimiento escolar, asistencia y comunicados en tiempo real.
                            </p>
                        </div>

                        <!-- Tarjeta de Ayuda -->
                        <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-lg transform transition hover:bg-white/10 hover:scale-[1.02] duration-300 cursor-default"
                             x-show="show"
                             x-transition:enter="transition ease-out duration-700 delay-200"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-indigo-500/20 rounded-xl">
                                    <svg class="h-6 w-6 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-base">¿Necesitas asistencia?</h3>
                                    <p class="text-sm text-blue-200/80 mt-0.5">Contacta con soporte si tienes problemas de acceso.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Copyright -->
                <div class="absolute bottom-8 left-12 text-blue-200/60 text-xs font-medium">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Sistema de Gestión Académica.
                </div>
            </div>

            <!-- SECCIÓN DERECHA: Formulario de Login -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white p-6 lg:p-16 xl:p-24 relative overflow-y-auto">
                
                <!-- Botón 'Ir al inicio' Flotante -->
                <div class="absolute top-6 right-8">
                    <a href="/" class="group flex items-center gap-2 text-sm font-semibold text-gray-400 hover:text-indigo-600 transition-colors duration-200">
                        Ir al inicio
                        <div class="p-1 rounded-full bg-gray-100 group-hover:bg-indigo-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </div>
                    </a>
                </div>

                <div class="w-full max-w-[420px] mx-auto" x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)">
                    
                    <div x-show="show" 
                         x-transition:enter="transition ease-out duration-700 delay-100"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0">

                        <!-- Cabecera Derecha -->
                        <div class="text-center mb-10">
                            <!-- Logo Dinámico -->
                            <div class="inline-flex justify-center items-center mb-6">
                                @if($logoUrl)
                                    <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-20 w-auto object-contain drop-shadow-md hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="h-16 w-16 bg-gradient-to-tr from-indigo-600 to-violet-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-200 rotate-3 hover:rotate-6 transition-transform duration-300">
                                        <x-application-logo class="w-9 h-9 fill-current text-white" />
                                    </div>
                                @endif
                            </div>

                            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Academic+</h1>
                            <p class="text-sm font-medium text-gray-500 bg-gray-50 inline-block px-3 py-1 rounded-full">
                                {{ config('app.name') }}
                            </p>
                        </div>

                        <!-- Formulario -->
                        <div class="bg-white rounded-none sm:rounded-lg">
                            <div class="auth-form-wrapper">
                                {{ $slot }}
                            </div>
                        </div>

                        <!-- Footer Móvil -->
                        <div class="lg:hidden mt-10 text-center">
                            <p class="text-xs text-gray-400">
                                &copy; {{ date('Y') }} {{ config('app.name') }}.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>