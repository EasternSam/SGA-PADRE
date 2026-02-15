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
        
        <!-- Alpine.js -->
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // --- LÓGICA DE FONDO DINÁMICO ---
            // Recuperamos el color configurado para usarlo como base si es necesario.
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type');

            if ($type === 'gradient' && !str_contains($bg, 'gradient')) {
                $start = SystemOption::getOption('navbar_gradient_start', '#1e3a8a');
                $end = SystemOption::getOption('navbar_gradient_end', '#000000');
                $dir = SystemOption::getOption('navbar_gradient_direction', 'to right');
                $bg = "linear-gradient({$dir}, {$start}, {$end})";
            }

            // Fallback por si la configuración está vacía o es incorrecta
            if (empty($bg) || $bg === '#' || $bg === 'red' || $bg === '#red') {
                $bg = 'radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%)'; 
            }

            // Logo
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp

        <style>
            /* Reset & Base */
            body { font-family: 'Figtree', sans-serif; margin: 0; }
            * { box-sizing: border-box; }
            
            .login-container {
                min-height: 100vh;
                width: 100%;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                background-color: #0f172a; /* Fallback color */
                color: white;
            }

            /* Utilidad para flex */
            .flex-center-gap {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            /* Error messages */
            .error-msg {
                color: #f87171;
                font-size: 0.75rem;
                margin-top: 4px;
                margin-left: 4px;
                display: block;
            }

            /* Background Effects */
            .background-wrapper {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                /* Usamos el valor dinámico de PHP */
                background: {{ $bg }} !important;
                background-size: cover !important;
            }

            .orb {
                position: absolute;
                border-radius: 50%;
                mix-blend-mode: screen;
                transition: transform 0.1s ease-out; 
            }

            .orb-1 {
                top: -10%;
                left: -10%;
                width: 500px;
                height: 500px;
                background: rgba(147, 51, 234, 0.3); /* Purple */
                filter: blur(100px);
                animation: float-slow 8s ease-in-out infinite;
            }

            .orb-2 {
                bottom: -10%;
                right: -10%;
                width: 600px;
                height: 600px;
                background: rgba(79, 70, 229, 0.3); /* Indigo */
                filter: blur(120px);
                animation: float-medium 6s ease-in-out infinite;
            }

            .orb-3 {
                top: 40%;
                left: 60%;
                width: 300px;
                height: 300px;
                background: rgba(219, 39, 119, 0.2); /* Pink */
                filter: blur(80px);
                animation: float-fast 4s ease-in-out infinite;
            }

            /* Card Wrapper & Glass Effect */
            .card-wrapper {
                position: relative;
                z-index: 10;
                width: 100%;
                max-width: 450px;
                padding: 1rem;
            }

            .card-border-glow {
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1), transparent);
                border-radius: 24px;
                filter: blur(1px);
            }

            .glass-card {
                position: relative;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                border-radius: 24px;
                padding: 40px;
                color: white;
                overflow: hidden;
            }

            /* Hover Shine Effect */
            .card-shine {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(255,255,255,0.05), transparent);
                opacity: 0;
                transition: opacity 0.7s;
                pointer-events: none;
            }
            .glass-card:hover .card-shine {
                opacity: 1;
            }

            /* Header */
            .card-header {
                text-align: center;
                margin-bottom: 32px;
                position: relative;
            }

            .logo-container {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 80px;
                height: 80px;
                border-radius: 16px;
                background: linear-gradient(to top right, #6366f1, #a855f7);
                box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
                margin-bottom: 16px;
                transition: transform 0.5s;
                padding: 10px;
            }
            .logo-container:hover {
                transform: scale(1.1) rotate(3deg);
            }
            .logo-icon { color: white; width: 100%; height: 100%; object-fit: contain; }

            .title {
                font-size: 1.875rem;
                font-weight: 700;
                letter-spacing: -0.025em;
                margin: 0;
                background: linear-gradient(to right, #ffffff, #e0e7ff, #c7d2fe);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                color: transparent;
            }

            .subtitle {
                color: rgba(199, 210, 254, 0.8);
                margin-top: 8px;
                font-size: 0.875rem;
                font-weight: 500;
            }

            /* Generic overrides for inputs inside the glass card to match design */
            .glass-card input[type="text"],
            .glass-card input[type="email"],
            .glass-card input[type="password"] {
                width: 100%;
                padding: 12px 16px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 12px;
                outline: none;
                color: white;
                font-size: 1rem;
                transition: all 0.3s;
            }
            .glass-card input:focus {
                background: rgba(255, 255, 255, 0.1);
                border-color: transparent;
                box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.5);
            }
            .glass-card label {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                color: #e0e7ff;
                margin-bottom: 8px;
            }
            .glass-card button[type="submit"] {
                width: 100%;
                padding: 14px 16px;
                background: linear-gradient(to right, #4f46e5, #9333ea);
                color: white;
                font-weight: 700;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
                transition: all 0.3s;
                margin-top: 1rem;
            }
            .glass-card button[type="submit"]:hover {
                background: linear-gradient(to right, #4338ca, #7e22ce);
                transform: translateY(-2px);
                box-shadow: 0 15px 20px -3px rgba(99, 102, 241, 0.6);
            }

            /* Animations Keyframes */
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
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900" 
          x-data="{ 
              mouseX: 0, 
              mouseY: 0, 
              handleMouseMove(e) {
                  this.mouseX = (e.clientX / window.innerWidth) * 20;
                  this.mouseY = (e.clientY / window.innerHeight) * 20;
              }
          }"
          @mousemove.window="handleMouseMove">
        
        <div class="login-container">
            
            {{-- Fondo Dinámico con Parallax controlado por Alpine --}}
            <div class="background-wrapper">
                <div 
                    class="orb orb-1"
                    :style="`transform: translate(-${mouseX}px, -${mouseY}px)`"
                ></div>
                <div 
                    class="orb orb-2"
                    :style="`transform: translate(${mouseX}px, ${mouseY}px)`"
                ></div>
                <div 
                    class="orb orb-3"
                ></div>
            </div>

            {{-- Tarjeta Glassmorphic --}}
            <div class="card-wrapper">
                <div class="card-border-glow"></div>
                
                <div class="glass-card">
                    {{-- Brillo interior al hacer hover --}}
                    <div class="card-shine"></div>

                    {{-- Cabecera --}}
                    <div class="card-header">
                        <div class="logo-container">
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="logo-icon">
                            @else
                                {{-- Icono por defecto --}}
                                <x-application-logo class="w-10 h-10 fill-current text-white" />
                            @endif
                        </div>
                        <h1 class="title">{{ config('app.name', 'Portal') }}</h1>
                        <p class="subtitle">Ingresa tus credenciales para acceder</p>
                    </div>

                    {{-- CONTENIDO DEL FORMULARIO --}}
                    <div class="relative z-20 text-left">
                        {{ $slot }}
                    </div>

                    {{-- Footer simple --}}
                    <div class="mt-8 text-center text-xs text-white/40">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>