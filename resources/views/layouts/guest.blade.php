<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGA Padre') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Outfit"', 'sans-serif'],
                        },
                        animation: {
                            'float-slow': 'float 8s ease-in-out infinite',
                            'float-medium': 'float 6s ease-in-out infinite',
                            'float-fast': 'float 4s ease-in-out infinite',
                        },
                        keyframes: {
                            float: {
                                '0%, 100%': { transform: 'translateY(0)' },
                                '50%': { transform: 'translateY(-20px)' },
                            }
                        }
                    }
                }
            }
        </script>
        
        <!-- Alpine.js -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // --- Lógica de Fondo ---
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type');

            // Si es gradiente y no está vacío, lo usamos.
            // Si no, usamos el fondo oscuro por defecto del diseño React.
            $customBg = null;
            if ($type === 'gradient' && str_contains($bg, 'gradient')) {
               $customBg = $bg;
            } elseif ($type === 'solid' && $bg && $bg !== '#') {
               $customBg = $bg;
            }

            // Logo
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp

        <style>
            /* --- Override de Estilos para Formularios de Breeze/Jetstream --- */
            /* Esto es crucial para que los inputs blancos de Breeze se vuelvan transparentes/oscuros */
            
            .glass-form label {
                color: #e0e7ff !important; /* Indigo-100 */
                font-weight: 500 !important;
                font-size: 0.9rem !important;
            }

            .glass-form input[type="text"],
            .glass-form input[type="email"],
            .glass-form input[type="password"] {
                background-color: rgba(255, 255, 255, 0.03) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                color: white !important;
                border-radius: 0.75rem !important; /* Rounded-xl */
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
                transition: all 0.3s ease;
            }

            .glass-form input:focus {
                background-color: rgba(255, 255, 255, 0.08) !important;
                border-color: #818cf8 !important; /* Indigo-400 */
                box-shadow: 0 0 0 1px #818cf8 !important;
                outline: none !important;
            }
            
            /* Placeholder color override */
            .glass-form input::placeholder {
                color: rgba(199, 210, 254, 0.4) !important;
            }

            /* Checkbox */
            .glass-form input[type="checkbox"] {
                background-color: rgba(255, 255, 255, 0.1) !important;
                border-color: rgba(255, 255, 255, 0.3) !important;
                color: #6366f1 !important; /* Indigo-500 */
                border-radius: 0.25rem;
            }

            /* Botones primarios */
            .glass-form button[type="submit"] {
                background: linear-gradient(to right, #4f46e5, #9333ea) !important;
                border: none !important;
                color: white !important;
                font-weight: 600 !important;
                padding: 0.75rem 1rem !important;
                border-radius: 0.75rem !important;
                width: 100%;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-size: 0.85rem;
                transition: transform 0.2s, box-shadow 0.2s;
                box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);
            }
            
            .glass-form button[type="submit"]:hover {
                box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.5);
                transform: translateY(-2px);
            }

            /* Enlaces */
            .glass-form a {
                color: #a5b4fc !important; /* Indigo-300 */
                text-decoration: none;
                font-size: 0.85rem;
            }
            .glass-form a:hover {
                color: white !important;
                text-decoration: underline;
            }

            /* Utilitarios de fondo */
            .bg-aurora {
                background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%);
            }
            
            /* Orbes con mezcla */
            .orb-blur {
                filter: blur(100px);
                mix-blend-mode: screen;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-100 bg-[#0f172a] overflow-hidden"
          x-data="{ 
              mouseX: 0, 
              mouseY: 0,
              handleMove(e) {
                  // Efecto parallax suave y limitado
                  this.mouseX = (e.clientX - window.innerWidth / 2) / 50;
                  this.mouseY = (e.clientY - window.innerHeight / 2) / 50;
              }
          }"
          @mousemove.window="handleMove">
        
        <div class="min-h-screen w-full relative flex items-center justify-center">
            
            <!-- --- FONDO DINÁMICO --- -->
            <div class="absolute inset-0 w-full h-full pointer-events-none z-0 bg-aurora"
                 style="{{ $customBg ? 'background: ' . $customBg . ' !important; background-size: cover;' : '' }}">
                
                <!-- Orbes Animados (Solo visibles si no hay un fondo sólido personalizado superpuesto) -->
                @if(!$customBg || str_contains($customBg, 'gradient'))
                    <!-- Orbe Morado -->
                    <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-purple-600/20 rounded-full orb-blur animate-float-slow transition-transform duration-300 ease-out"
                         :style="`transform: translate(${mouseX * -1}px, ${mouseY * -1}px)`"></div>
                    
                    <!-- Orbe Indigo -->
                    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-indigo-600/20 rounded-full orb-blur animate-float-medium transition-transform duration-300 ease-out"
                         :style="`transform: translate(${mouseX}px, ${mouseY}px)`"></div>
                         
                    <!-- Orbe Rosa Central -->
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-pink-600/10 rounded-full orb-blur animate-float-fast"></div>
                @endif
                
                <!-- Ruido sutil para textura -->
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');"></div>
            </div>

            <!-- --- TARJETA PRINCIPAL (GLASS) --- -->
            <div class="relative z-10 w-full max-w-md p-4 transition-all duration-700 ease-out transform"
                 x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)"
                 x-show="show"
                 x-transition:enter="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                
                <!-- Borde Brillante (Glow) -->
                <div class="absolute -inset-0.5 bg-gradient-to-br from-indigo-500/30 via-purple-500/10 to-pink-500/30 rounded-3xl blur opacity-75"></div>
                
                <!-- Contenedor Tarjeta -->
                <div class="relative bg-white/5 backdrop-blur-xl border border-white/10 shadow-2xl rounded-2xl p-8 sm:p-10 overflow-hidden">
                    
                    <!-- Brillo superior -->
                    <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

                    <!-- CABECERA -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center mb-6 relative group">
                            <!-- Efecto de resplandor detrás del logo -->
                            <div class="absolute inset-0 bg-indigo-500 blur-xl opacity-20 group-hover:opacity-40 transition-opacity duration-500"></div>
                            
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="relative h-24 w-auto object-contain drop-shadow-2xl transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="relative w-20 h-20 bg-gradient-to-tr from-indigo-600 to-violet-600 rounded-2xl flex items-center justify-center text-white shadow-lg border border-white/10">
                                    <x-application-logo class="w-10 h-10 fill-current" />
                                </div>
                            @endif
                        </div>

                        <h1 class="text-3xl font-bold tracking-tight text-white mb-2 drop-shadow-md">
                            {{ config('app.name') }}
                        </h1>
                        <p class="text-indigo-200/80 text-sm font-medium">
                            Portal de Gestión Académica
                        </p>
                    </div>

                    <!-- FORMULARIO (Slot) -->
                    <!-- La clase 'glass-form' activa los estilos CSS personalizados del head -->
                    <div class="glass-form space-y-4">
                        {{ $slot }}
                    </div>

                    <!-- FOOTER -->
                    <div class="mt-8 pt-6 border-t border-white/5 text-center">
                        <p class="text-xs text-indigo-300/60">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>