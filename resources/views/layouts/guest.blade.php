<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGA Padre') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Alpine.js para interactividad (mouse move) -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // --- LÓGICA DE FONDO DINÁMICO ---
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type');

            if ($type === 'gradient' && !str_contains($bg, 'gradient')) {
                $start = SystemOption::getOption('navbar_gradient_start', '#1e3a8a');
                $end = SystemOption::getOption('navbar_gradient_end', '#000000');
                $dir = SystemOption::getOption('navbar_gradient_direction', 'to right');
                $bg = "linear-gradient({$dir}, {$start}, {$end})";
            }

            // Fallback: Si no hay config, usamos un degradado oscuro similar al ejemplo
            if (empty($bg) || $bg === '#' || $bg === 'red' || $bg === '#red') {
                $bg = 'radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%)'; 
            }

            // --- LÓGICA DE LOGO ---
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp
        
        <style>
             body { font-family: 'Figtree', sans-serif; }
             
             /* Animaciones personalizadas */
             @keyframes float-slow {
                0%, 100% { transform: translate(0, 0); }
                50% { transform: translate(20px, -20px); }
             }
             @keyframes float-medium {
                0%, 100% { transform: translate(0, 0); }
                50% { transform: translate(-15px, 25px); }
             }
             @keyframes float-fast {
                0%, 100% { transform: translate(0, 0); }
                50% { transform: translate(10px, 15px); }
             }
             .animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
             .animate-float-medium { animation: float-medium 6s ease-in-out infinite; }
             .animate-float-fast { animation: float-fast 4s ease-in-out infinite; }
        </style>
    </head>
    <body class="font-sans antialiased text-white bg-[#0f172a]"
          x-data="{ 
              mouseX: 0, 
              mouseY: 0,
              handleMouseMove(e) {
                  this.mouseX = (e.clientX / window.innerWidth) * 20;
                  this.mouseY = (e.clientY / window.innerHeight) * 20;
              }
          }"
          @mousemove.window="handleMouseMove">
        
        <div class="min-h-screen w-full relative flex items-center justify-center overflow-hidden selection:bg-indigo-500 selection:text-white">
            
            <!-- --- Fondo Dinámico --- -->
            <div class="absolute inset-0 w-full h-full pointer-events-none"
                 style="background: {{ $bg }} !important; background-size: cover;">
                
                <!-- Orbes de luz flotantes con animación (Efecto visual) -->
                <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] rounded-full bg-purple-600/30 blur-[100px] animate-float-slow mix-blend-screen transition-transform duration-100 ease-out"
                     :style="`transform: translate(${mouseX * -1}px, ${mouseY * -1}px)`"></div>
                
                <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-600/30 blur-[120px] animate-float-medium mix-blend-screen transition-transform duration-100 ease-out"
                     :style="`transform: translate(${mouseX}px, ${mouseY}px)`"></div>
                
                <div class="absolute top-[40%] left-[60%] w-[300px] h-[300px] rounded-full bg-pink-600/20 blur-[80px] animate-float-fast mix-blend-screen"></div>
            </div>

            <!-- --- Tarjeta Glassmorphic --- -->
            <div class="relative z-10 w-full max-w-md p-4">
                
                <!-- Borde brillante sutil -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/40 via-white/10 to-transparent rounded-3xl blur-[1px]"></div>
                
                <div class="relative bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl p-8 sm:p-10 text-white overflow-hidden group">
                    
                    <!-- Brillo interior al hacer hover -->
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

                    <!-- --- Cabecera con Logo --- -->
                    <div class="text-center mb-8 relative">
                        <div class="inline-flex items-center justify-center mb-4 transform transition-transform duration-500 hover:scale-110">
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-20 w-auto object-contain drop-shadow-lg">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 shadow-lg shadow-indigo-500/30 flex items-center justify-center">
                                    <x-application-logo class="w-10 h-10 fill-current text-white" />
                                </div>
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-100 to-indigo-200">
                            {{ config('app.name', 'Portal Académico') }}
                        </h1>
                        <p class="text-indigo-200/80 mt-2 text-sm font-medium">
                            Ingresa tus credenciales para acceder
                        </p>
                    </div>

                    <!-- --- Slot del Formulario (Login/Register) --- -->
                    <div class="relative z-20">
                        {{ $slot }}
                    </div>

                    <!-- --- Footer / Links Adicionales (Opcional) --- -->
                    @if (Route::has('register'))
                        <div class="relative my-8">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-white/10"></div>
                            </div>
                            <div class="relative flex justify-center text-xs uppercase">
                                <span class="bg-[#1e2038]/60 backdrop-blur-md px-3 text-indigo-300 rounded-full border border-white/5">
                                    ¿Aún no eres estudiante?
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('register') }}" class="w-full py-3 px-4 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/30 text-indigo-100 hover:text-white font-semibold rounded-xl transition-all duration-300 flex items-center justify-center gap-2 group/btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 group-hover/btn:text-white transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                            Solicitar Admisión / Nuevo Ingreso
                        </a>
                    @endif

                </div>
            </div>
            
            <!-- Footer Copyright -->
            <div class="absolute bottom-4 text-center text-xs text-white/40">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </div>

        </div>
    </body>
</html>