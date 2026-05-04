<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SGA">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ config('app.name', 'SGA Padre') }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    @php
        $favicon = \App\Models\Setting::get('favicon');
        $appIcon = \App\Models\Setting::get('app_icon');
        $faviconUrl = $favicon ? $favicon : '/centuu.png';
        $appIconUrl = $appIcon ? $appIcon : '/centuu.png';
    @endphp
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $appIconUrl }}">
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

    <!-- Fonts (preload for instant render) -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts del Proyecto -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        // Intentamos obtener la variable branding compartida globalmente (desde AppServiceProvider)
        // Si no existe, inicializamos valores por defecto o intentamos consultar la BD manualmente.
        
        $logoUrl = null;
        $navBackground = '#1e3a8a'; // Azul por defecto

        // 1. Prioridad: Variable global $branding (inyectada por AppServiceProvider)
        if (isset($branding)) {
            $logoUrl = $branding->logo_url ?? null;
            $navBackground = $branding->primary_color ?? '#1e3a8a';
        } 
        // 2. Respaldo: Consulta directa si la variable global falló
        else {
            try {
                // Intenta usar Setting primero (nuevo estándar)
                $logoUrl = \App\Models\Setting::get('institution_logo');
                if (!$logoUrl) {
                    // Fallback a SystemOption (legacy)
                    $logoUrl = \App\Models\SystemOption::getOption('logo');
                }
                
                $color = \App\Models\Setting::get('brand_primary_color');
                if ($color) {
                    $navBackground = $color;
                }
            } catch (\Exception $e) {
                // Si la BD falla, se mantienen los nulos/defaults
            }
        }
    @endphp

    <style>
        body {
            min-height: 100vh;
            color: #e2e8f0;
            background: {{ $navBackground }};
        }

        /* ── Glass Card ── */
        .glass-panel {
            background: rgba(17, 25, 40, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* ── Form Inputs ── */
        .glass-input {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important;
            padding: 0.75rem 1rem;
            width: 100%;
        }

        .glass-input:focus {
            background-color: rgba(0, 0, 0, 0.35) !important;
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3) !important;
            outline: none;
        }

        .glass-input::placeholder {
            color: rgba(203, 213, 225, 0.4) !important;
        }

        /* ── Labels ── */
        label {
            color: #cbd5e1 !important;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
            display: block;
        }

        /* ── Checkbox ── */
        input[type="checkbox"] {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #6366f1;
            border-radius: 0.25rem;
        }
        input[type="checkbox"]:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }

        /* ── Primary Button ── */
        .glass-button {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            width: 100%;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            cursor: pointer;
        }

        .glass-button:hover {
            filter: brightness(1.1);
        }

        .glass-button:active {
            filter: brightness(0.95);
        }

        /* ── Links ── */
        .glass-link {
            color: #94a3b8;
            font-size: 0.9rem;
            text-decoration: underline;
        }
        .glass-link:hover {
            color: #e2e8f0;
        }

        /* ── Static decorative background ── */
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
        }

        /* ── Glow border (static) ── */
        .glow-border {
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(124, 58, 237, 0.25));
            border-radius: 1rem;
            filter: blur(8px);
            z-index: -1;
        }
    </style>
</head>

<body class="font-sans antialiased overflow-x-hidden relative flex items-center justify-center">

    <!-- Static Background Orbs (no animation, lightweight) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="bg-orb w-[500px] h-[500px] bg-indigo-600/15 -top-20 -left-20"></div>
        <div class="bg-orb w-[400px] h-[400px] bg-purple-600/15 bottom-0 right-0"></div>
        <div class="bg-orb w-[600px] h-[600px] bg-blue-600/8 top-[40%] left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
    </div>

    <!-- Contenedor Principal -->
    <div class="w-full min-h-screen flex flex-col sm:justify-center items-center pt-4 sm:pt-0 p-4">
        
        <!-- Logo y Título -->
        <div class="mb-4 text-center relative z-10">
            <a href="/" class="flex flex-col items-center">
                @if($logoUrl)
                    <div class="p-2 rounded-3xl mb-4">
                        <img src="{{ Str::startsWith($logoUrl, 'http') ? $logoUrl : asset($logoUrl) }}" 
                             alt="{{ config('app.name') }}" 
                             class="h-32 w-auto drop-shadow-2xl object-contain">
                    </div>
                @else
                    <x-application-logo class="w-32 h-32 fill-current text-gray-100 drop-shadow-2xl mb-4" />
                @endif
                
                <h1 class="text-xl font-bold text-white drop-shadow-md uppercase opacity-90">
                    {{ config('app.name') }}
                </h1>
                <p class="text-indigo-200/60 text-[0.65rem] font-semibold tracking-wider mt-1 uppercase">
                    Portal de Acceso
                </p>
            </a>
        </div>

        <!-- Tarjeta del Formulario -->
        <div class="w-full sm:max-w-md relative z-10 px-4 sm:px-0">
            <!-- Static glow border (no animation) -->
            <div class="glow-border"></div>
            
            <div class="glass-panel px-4 py-8 sm:px-8 sm:py-10 shadow-2xl overflow-hidden rounded-2xl relative">
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