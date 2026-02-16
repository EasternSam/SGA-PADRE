<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGA Padre') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (CDN de respaldo para estilos rápidos) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        glass: {
                            100: 'rgba(255, 255, 255, 0.1)',
                            200: 'rgba(255, 255, 255, 0.2)',
                            border: 'rgba(255, 255, 255, 0.15)',
                        }
                    },
                    animation: {
                        'float': 'float 20s ease-in-out infinite',
                        'float-delayed': 'float 15s ease-in-out infinite reverse',
                        'pulse-slow': 'pulse 10s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'fade-in-down': 'fadeInDown 0.8s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translate(0, 0) rotate(0deg)' },
                            '33%': { transform: 'translate(30px, -50px) rotate(10deg)' },
                            '66%': { transform: 'translate(-20px, 20px) rotate(-5deg)' },
                        },
                        fadeInDown: {
                            '0%': { opacity: '0', transform: 'translateY(-20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts del Proyecto -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        use App\Models\SystemOption;
        use App\Models\Setting;

        // 1. Lógica del Logo (Mantenida de tu configuración anterior)
        $logoUrl = null;
        try {
            $logoUrl = SystemOption::getOption('logo') ?: SystemOption::getOption('institution_logo');
        } catch (\Exception $e) {
            // Fallback si la base de datos falla
        }

        // 2. Lógica del Fondo (Sincronizada con navigation.blade.php)
        // Usamos la misma consulta para obtener el 'brand_primary_color'
        $navBackground = null;
        try {
            $navBackground = Setting::where('key', 'brand_primary_color')->value('value');
        } catch (\Exception $e) {}
        
        // Fallback al azul corporativo si no hay configuración
        $navBackground = $navBackground ?? '#1e3a8a';
    @endphp

    <style>
        body {
            /* Asegura que el fondo cubra todo incluso en scroll */
            min-height: 100vh;
            color: #e2e8f0;
        }

        /* Glassmorphism Card */
        .glass-panel {
            background: rgba(17, 25, 40, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.125);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* --- FORZAR ESTILOS DE FORMULARIO PARA TEMA OSCURO --- */
        
        /* Inputs (Text, Email, Password) */
        .glass-input {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important; /* rounded-xl */
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1rem;
            padding-right: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .glass-input:focus {
            background-color: rgba(0, 0, 0, 0.35) !important;
            border-color: #818cf8 !important; /* indigo-400 */
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3) !important;
            outline: none;
        }

        .glass-input::placeholder {
            color: rgba(203, 213, 225, 0.4) !important;
        }

        /* Labels */
        label {
            color: #cbd5e1 !important; /* slate-300 */
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
            display: block;
        }

        /* Checkbox */
        input[type="checkbox"] {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #6366f1; /* indigo-500 */
            border-radius: 0.25rem;
        }
        input[type="checkbox"]:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }

        /* Botón Principal */
        .glass-button {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }

        .glass-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.5);
            filter: brightness(1.1);
        }

        /* Links */
        .glass-link {
            color: #94a3b8;
            transition: color 0.2s;
            font-size: 0.9rem;
            text-decoration: underline;
        }
        .glass-link:hover {
            color: #e2e8f0;
            text-decoration: underline;
        }

        /* Orbes decorativos */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.5;
        }
    </style>
</head>

<body class="font-sans antialiased overflow-x-hidden relative flex items-center justify-center"
      style="background: {{ $navBackground }};">

    <!-- Fondo Interactivo -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <!-- Orbe 1 (Opacidad reducida de 40 a 20) -->
        <div class="orb w-[500px] h-[500px] bg-indigo-600/20 -top-20 -left-20 animate-float"></div>
        
        <!-- Orbe 2 (Opacidad reducida de 40 a 20) -->
        <div class="orb w-[400px] h-[400px] bg-purple-600/20 bottom-0 right-0 animate-float-delayed"></div>
        
        <!-- Orbe 3 (Centro sutil, ajustado top-[40%] para subir la luz detrás del formulario) -->
        <div class="orb w-[600px] h-[600px] bg-blue-600/10 top-[40%] left-1/2 transform -translate-x-1/2 -translate-y-1/2 animate-pulse-slow"></div>
    </div>

    <!-- Contenedor Principal -->
    <div class="w-full min-h-screen flex flex-col sm:justify-center items-center pt-4 sm:pt-0 p-4">
        
        <!-- Logo y Título -->
        <!-- Se han ajustado los márgenes (eliminado mt-10 y reducido mb) para evitar el scroll -->
        <div class="mb-4 text-center relative z-10 animate-fade-in-down">
            <a href="/" class="flex flex-col items-center group">
                @if($logoUrl)
                    <!-- LOGO AUMENTADO (h-52) con padding reducido para compactar -->
                    <!-- MARGEN REDUCIDO de mb-9 a mb-2 para acercar el logo al título -->
                    <div class="p-2 bg-white/5 rounded-3xl backdrop-blur-sm border border-white/10 shadow-2xl mb-2 transition-transform duration-500 group-hover:scale-105 group-hover:-rotate-2">
                        <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-52 w-auto drop-shadow-2xl object-contain">
                    </div>
                @else
                    <!-- FALLBACK LOGO AUMENTADO -->
                    <!-- MARGEN REDUCIDO de mb-9 a mb-2 -->
                    <x-application-logo class="w-52 h-52 fill-current text-gray-100 drop-shadow-2xl mb-2" />
                @endif
                
                <!-- TEXTO REDUCIDO Y SIN ESPACIADO EXAGERADO -->
                <h1 class="text-xl font-bold text-white drop-shadow-md uppercase opacity-90">
                    {{ config('app.name') }}
                </h1>
                <p class="text-indigo-200/60 text-[0.65rem] font-semibold tracking-wider mt-1 uppercase">
                    Portal de Acceso
                </p>
            </a>
        </div>

        <!-- Tarjeta del Formulario -->
        <div class="w-full sm:max-w-md relative z-10">
            <!-- Efecto de borde brillante -->
            <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-30 animate-pulse" style="height: 625px;"></div>
            
            <div class="glass-panel px-8 py-10 shadow-2xl overflow-hidden rounded-2xl relative">
                <!-- Slot para el contenido (Login/Register/Etc) -->
                {{ $slot }}
            </div>
            
            <!-- Footer discreto -->
            <div class="text-center mt-6">
                <p class="text-xs text-slate-400 font-light">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>