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

            // Logo dinámico
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp

        <style>
            /* Reset & Base */
            body { font-family: 'Figtree', sans-serif; margin: 0; background-color: #0f172a; color: white; }
            * { box-sizing: border-box; }
            
            .login-container {
                min-height: 100vh;
                width: 100%;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            /* --- Background Effects (Tema Oscuro Forzado) --- */
            .background-wrapper {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                /* Fondo radial oscuro fijo para garantizar el diseño "espacial" */
                background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%) !important;
                background-size: cover !important;
            }

            .orb {
                position: absolute;
                border-radius: 50%;
                mix-blend-mode: screen;
                transition: transform 0.1s ease-out; 
            }

            .orb-1 {
                top: -10%; left: -10%; width: 500px; height: 500px;
                background: rgba(147, 51, 234, 0.3); /* Purple */
                filter: blur(100px);
                animation: float-slow 8s ease-in-out infinite;
            }

            .orb-2 {
                bottom: -10%; right: -10%; width: 600px; height: 600px;
                background: rgba(79, 70, 229, 0.3); /* Indigo */
                filter: blur(120px);
                animation: float-medium 6s ease-in-out infinite;
            }

            .orb-3 {
                top: 40%; left: 60%; width: 300px; height: 300px;
                background: rgba(219, 39, 119, 0.2); /* Pink */
                filter: blur(80px);
                animation: float-fast 4s ease-in-out infinite;
            }

            /* --- Glass Card (Versión Oscura) --- */
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
                /* Borde brillante más sutil */
                background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05), transparent);
                border-radius: 24px;
                filter: blur(1px);
            }

            .glass-card {
                position: relative;
                /* Fondo oscuro semitransparente en lugar de blanco */
                background: rgba(15, 23, 42, 0.6); 
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                border-radius: 24px;
                padding: 40px;
                color: white;
                overflow: hidden;
            }

            /* Hover Shine Effect */
            .card-shine {
                position: absolute;
                top: 0; left: 0; width: 100%; height: 100%;
                background: linear-gradient(135deg, rgba(255,255,255,0.03), transparent);
                opacity: 0; transition: opacity 0.7s; pointer-events: none;
            }
            .glass-card:hover .card-shine { opacity: 1; }

            /* --- Header --- */
            .card-header {
                text-align: center;
                margin-bottom: 32px;
                position: relative;
            }

            .logo-container {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 80px; height: 80px;
                border-radius: 16px;
                background: linear-gradient(to top right, #6366f1, #a855f7);
                box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
                margin-bottom: 16px;
                transition: transform 0.5s;
                padding: 12px;
            }
            .logo-container:hover { transform: scale(1.1) rotate(3deg); }
            .logo-icon { width: 100%; height: 100%; object-fit: contain; color: white; }

            .title {
                font-size: 1.875rem; font-weight: 700; letter-spacing: -0.025em; margin: 0;
                background: linear-gradient(to right, #ffffff, #e0e7ff, #c7d2fe);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text; color: transparent;
            }

            .subtitle {
                color: rgba(199, 210, 254, 0.8);
                margin-top: 8px; font-size: 0.875rem; font-weight: 500;
            }

            /* --- ESTILOS FORZADOS PARA EL FORMULARIO (SLOT) --- */
            
            .glass-card label {
                display: block; font-size: 0.875rem; font-weight: 500;
                color: #e0e7ff; margin-bottom: 6px; margin-left: 4px;
            }

            .glass-card input[type="text"],
            .glass-card input[type="email"],
            .glass-card input[type="password"] {
                width: 100%;
                padding: 12px 16px;
                /* Fondo muy oscuro y transparente */
                background: rgba(0, 0, 0, 0.2) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-radius: 12px !important;
                outline: none;
                color: white !important;
                font-size: 1rem;
                transition: all 0.3s;
                margin-bottom: 4px;
            }
            
            .glass-card input:focus {
                background: rgba(0, 0, 0, 0.4) !important;
                border-color: transparent !important;
                box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.5) !important; /* Indigo glow */
            }

            .glass-card input::placeholder {
                color: rgba(165, 180, 252, 0.4);
            }

            /* Botón Principal */
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
                margin-top: 1.5rem;
                display: flex;
                justify-content: center;
                align-items: center;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-size: 0.875rem;
            }
            
            .glass-card button[type="submit"]:hover {
                background: linear-gradient(to right, #4338ca, #7e22ce);
                transform: translateY(-2px);
                box-shadow: 0 15px 20px -3px rgba(99, 102, 241, 0.6);
            }

            /* Checkbox */
            .glass-card input[type="checkbox"] {
                border-radius: 4px;
                background-color: rgba(0, 0, 0, 0.3);
                border-color: rgba(255, 255, 255, 0.3);
                color: #6366f1;
            }
            .glass-card input[type="checkbox"]:checked {
                background-color: #6366f1;
            }

            /* Enlaces */
            .glass-card a {
                color: #a5b4fc;
                font-size: 0.875rem;
                text-decoration: none;
                transition: color 0.2s;
            }
            .glass-card a:hover {
                color: white;
                text-decoration: underline;
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

                    {{-- Cabecera con Logo --}}
                    <div class="card-header">
                        <div class="logo-container">
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="logo-icon">
                            @else
                                {{-- Icono GraduationCap (SVG) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="logo-icon">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                                </svg>
                            @endif
                        </div>
                        <h1 class="title">{{ config('app.name', 'Portal Académico') }}</h1>
                        <p class="subtitle">Ingresa tus credenciales para acceder</p>
                    </div>

                    {{-- CONTENIDO DEL FORMULARIO INYECTADO --}}
                    {{-- Los estilos CSS globales (.glass-card input) estilizarán los componentes de Breeze aquí dentro --}}
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