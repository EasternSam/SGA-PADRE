<title>{{ config('app.name', 'SGA Padre') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        use App\Models\SystemOption;

        // 1. Obtener valores base
        $bg = SystemOption::getOption('navbar_color');
        $type = SystemOption::getOption('navbar_type');

        // 2. Lógica de AUTO-CORRECCIÓN:
        if ($type === 'gradient' && !str_contains($bg, 'gradient')) {
            $start = SystemOption::getOption('navbar_gradient_start', '#1e3a8a');
            $end = SystemOption::getOption('navbar_gradient_end', '#000000');
            $dir = SystemOption::getOption('navbar_gradient_direction', 'to right');
            
            $bg = "linear-gradient({$dir}, {$start}, {$end})";
        }

        // 3. Fallback de seguridad final
        if (empty($bg) || $bg === '#') {
            $bg = 'linear-gradient(135deg, #1e3a8a 0%, #000000 100%)';
        }
        
        // Logo
        $logoUrl = SystemOption::getOption('logo') ?: SystemOption::getOption('institution_logo');
    @endphp
    
    <style>
        :root {
            --dynamic-bg: {{ $bg }};
        }

        .main-container {
            background: var(--dynamic-bg) !important;
            background-attachment: fixed !important;
            position: relative;
            overflow: hidden;
        }

        /* Overlay para mejorar legibilidad y profundidad */
        .bg-overlay {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.05) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(0,0,0,0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Efecto Glassmorphism Avanzado */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        }

        /* Animación de entrada suave */
        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Decoración de círculos ambientales */
        .ambient-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 0;
            opacity: 0.4;
            animation: float 10s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, -20px); }
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased h-full">
    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 main-container">
        <div class="bg-overlay"></div>
        
        <!-- Elementos decorativos de fondo (Blobs) -->
        <div class="ambient-blob w-64 h-64 bg-white/10 top-[-10%] left-[-5%]"></div>
        <div class="ambient-blob w-96 h-96 bg-black/10 bottom-[-10%] right-[-5%] animation-delay-2000"></div>

        <div class="relative z-10 w-full max-w-md fade-in-up">
            <!-- Header del Login: Logo y Bienvenida -->
            <div class="text-center mb-10">
                <a href="/" wire:navigate class="inline-block group">
                    <div class="relative">
                        {{-- Brillo detrás del logo --}}
                        <div class="absolute -inset-1 bg-gradient-to-r from-white/30 to-white/10 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        
                        <div class="relative flex items-center justify-center">
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" 
                                     alt="{{ config('app.name') }}" 
                                     class="h-24 w-auto object-contain bg-white/40 rounded-2xl p-3 backdrop-blur-md shadow-xl border border-white/40 transform transition-all duration-500 group-hover:scale-105 group-hover:-translate-y-1">
                            @else
                                <div class="p-5 bg-white/30 rounded-3xl backdrop-blur-xl shadow-2xl border border-white/30 transform transition-all duration-500 group-hover:scale-105 group-hover:-translate-y-1">
                                    <x-application-logo class="w-16 h-16 fill-current text-white drop-shadow-md" />
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
                
                <h2 class="mt-6 text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">
                    Bienvenido de nuevo
                </h2>
                <p class="mt-2 text-sm text-white/70 font-medium uppercase tracking-widest">
                    Gestión Académica {{ config('app.name') }}
                </p>
            </div>

            <!-- Tarjeta Principal -->
            <div class="glass-card sm:rounded-3xl overflow-hidden relative">
                {{-- Línea de progreso decorativa en la parte superior --}}
                <div class="h-1.5 w-full bg-gradient-to-r from-transparent via-white/50 to-transparent"></div>
                
                <div class="px-8 py-10">
                    <!-- Aquí se inyecta el contenido del formulario (auth.login u otros) -->
                    {{ $slot }}
                </div>

                {{-- Footer de la tarjeta (opcional) --}}
                <div class="px-8 py-4 bg-black/5 border-t border-white/20 text-center">
                    <p class="text-xs text-gray-500 font-medium">
                        Acceso seguro con cifrado SSL
                    </p>
                </div>
            </div>
            
            <!-- Footer General -->
            <div class="mt-10 text-center space-y-2">
                <p class="text-white/60 text-xs font-medium tracking-wide">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="#" class="text-white/40 hover:text-white transition-colors text-xs">Soporte Técnico</a>
                    <span class="text-white/20">•</span>
                    <a href="#" class="text-white/40 hover:text-white transition-colors text-xs">Privacidad</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>