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
        * {
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            min-height: 100vh;
            min-height: 100dvh; /* Dynamic viewport height for mobile */
            color: #e2e8f0;
            background: {{ $navBackground }};
            overscroll-behavior: none;
            touch-action: manipulation;
        }

        /* ── Glass Card (Mobile Optimized) ── */
        .glass-panel {
            background: rgba(17, 25, 40, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* ── Form Inputs (Enhanced for Mobile) ── */
        .glass-input {
            background-color: rgba(0, 0, 0, 0.25) !important;
            border: 2px solid rgba(255, 255, 255, 0.18) !important;
            color: #ffffff !important;
            border-radius: 1rem !important;
            padding: 1rem 3rem 1rem 1rem;
            width: 100%;
            font-size: 16px !important; /* Prevents zoom on iOS */
            transition: all 0.2s ease;
            -webkit-appearance: none;
        }

        .glass-input:focus {
            background-color: rgba(0, 0, 0, 0.4) !important;
            border-color: #818cf8 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25) !important;
            outline: none;
            transform: translateY(-1px);
        }

        .glass-input::placeholder {
            color: rgba(203, 213, 225, 0.45) !important;
            font-size: 15px;
        }

        /* ── Labels (Improved Readability) ── */
        label {
            color: #e2e8f0 !important;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            display: block;
            letter-spacing: 0.01em;
        }

        /* ── Checkbox (Larger Touch Target) ── */
        input[type="checkbox"] {
            background-color: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.35);
            color: #6366f1;
            border-radius: 0.375rem;
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        input[type="checkbox"]:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
        }
        input[type="checkbox"]:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        /* ── Primary Button (Enhanced Touch) ── */
        .glass-button {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            font-weight: 700;
            padding: 1.125rem 1.5rem;
            border-radius: 1rem;
            border: none;
            width: 100%;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            min-height: 52px; /* Better touch target */
        }

        .glass-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, 0.5);
        }

        .glass-button:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        /* Ripple effect for buttons */
        .glass-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .glass-button:active::after {
            width: 300px;
            height: 300px;
        }

        /* ── Links (Better Touch Targets) ── */
        .glass-link {
            color: #a5b4fc;
            font-size: 0.9rem;
            text-decoration: none;
            position: relative;
            padding: 0.25rem 0;
            transition: color 0.2s ease;
            display: inline-block;
        }
        
        .glass-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: currentColor;
            opacity: 0.5;
        }
        
        .glass-link:hover {
            color: #e2e8f0;
        }

        .glass-link:active {
            color: #fff;
        }

        /* ── Static decorative background ── */
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
        }

        /* ── Glow border (Enhanced) ── */
        .glow-border {
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(124, 58, 237, 0.3));
            border-radius: 1.25rem;
            filter: blur(10px);
            z-index: -1;
            opacity: 0.8;
        }

        /* ── Mobile-specific improvements ── */
        @media (max-width: 640px) {
            .glass-panel {
                border-radius: 1.5rem 1.5rem 0 0 !important;
                margin-bottom: 0 !important;
            }

            .glass-input {
                padding: 1.25rem 3.5rem 1.25rem 1.25rem;
                font-size: 16px !important;
            }

            .glass-button {
                padding: 1.25rem 1.5rem;
                font-size: 0.95rem;
                min-height: 56px;
            }

            label {
                font-size: 1rem;
            }

            /* Prevent body scroll when focusing inputs */
            body.input-focused {
                position: fixed;
                width: 100%;
            }
        }

        /* ── Safe area for notch devices ── */
        @supports (padding: max(0px)) {
            .safe-area-top {
                padding-top: max(1rem, env(safe-area-inset-top));
            }
            
            .safe-area-bottom {
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }

        /* ── Input icons hover effect ── */
        .input-icon {
            transition: all 0.2s ease;
        }

        .glass-input:focus + .input-icon-wrapper .input-icon {
            color: #818cf8 !important;
            transform: scale(1.1);
        }

        /* ── Smooth animations for mobile ── */
        @media (prefers-reduced-motion: no-preference) {
            .fade-in {
                animation: fadeIn 0.3s ease-in;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .slide-up {
                animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }

        /* ── Better loading states ── */
        .glass-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .glass-button:disabled:hover {
            transform: none;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        /* ── Info boxes mobile optimization ── */
        .info-box {
            transition: all 0.2s ease;
        }

        .info-box:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body class="font-sans antialiased overflow-x-hidden relative flex items-center justify-center safe-area-top">

    <!-- Static Background Orbs (optimized for mobile) -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="bg-orb w-[500px] h-[500px] bg-indigo-600/15 -top-20 -left-20"></div>
        <div class="bg-orb w-[400px] h-[400px] bg-purple-600/15 bottom-0 right-0"></div>
        <div class="bg-orb w-[600px] h-[600px] bg-blue-600/8 top-[40%] left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
    </div>

    <!-- Contenedor Principal (Mobile-First) -->
    <div class="w-full min-h-screen min-h-[100dvh] flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4 pb-safe safe-area-bottom">
        
        <!-- Logo y Título (Optimizado para móvil) -->
        <div class="mb-6 sm:mb-8 text-center relative z-10 fade-in">
            <a href="/" class="flex flex-col items-center">
                @if($logoUrl)
                    <div class="p-3 rounded-3xl mb-3 sm:mb-4 transform transition-transform active:scale-95">
                        <img src="{{ Str::startsWith($logoUrl, 'http') ? $logoUrl : asset($logoUrl) }}" 
                             alt="{{ config('app.name') }}" 
                             class="h-20 sm:h-28 w-auto drop-shadow-2xl object-contain">
                    </div>
                @else
                    <div class="transform transition-transform active:scale-95 mb-3 sm:mb-4">
                        <x-application-logo class="w-20 h-20 sm:w-28 sm:h-28 fill-current text-gray-100 drop-shadow-2xl" />
                    </div>
                @endif
                
                <h1 class="text-2xl sm:text-3xl font-bold text-white drop-shadow-md uppercase tracking-tight">
                    {{ config('app.name') }}
                </h1>
                <p class="text-indigo-200/70 text-xs sm:text-sm font-semibold tracking-wider mt-2 uppercase">
                    Portal de Acceso
                </p>
            </a>
        </div>

        <!-- Tarjeta del Formulario (Mejorada para móvil) -->
        <div class="w-full sm:max-w-md relative z-10 slide-up">
            <!-- Static glow border -->
            <div class="glow-border"></div>
            
            <div class="glass-panel px-5 py-8 sm:px-10 sm:py-12 shadow-2xl overflow-hidden rounded-2xl sm:rounded-3xl relative">
                <!-- Slot para el contenido (Login/Register/Etc) -->
                {{ $slot }}
            </div>
            
            <!-- Footer (Mejorado para móvil) -->
            <div class="text-center mt-6 mb-4 sm:mb-0 px-4">
                <p class="text-xs sm:text-sm text-slate-300/80 font-light">
                    &copy; {{ date('Y') }} {{ config('app.name') }}.
                    <span class="block sm:inline mt-1 sm:mt-0">Todos los derechos reservados.</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Mobile Input Focus Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.glass-input');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    // Prevent zoom on iOS
                    if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                        document.body.classList.add('input-focused');
                    }
                    
                    // Scroll input into view smoothly
                    setTimeout(() => {
                        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                });
                
                input.addEventListener('blur', function() {
                    document.body.classList.remove('input-focused');
                });
            });

            // Add haptic feedback for buttons on supported devices
            const buttons = document.querySelectorAll('.glass-button');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    if ('vibrate' in navigator) {
                        navigator.vibrate(10);
                    }
                });
            });

            // Prevent double-tap zoom on buttons
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(event) {
                const now = Date.now();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
        });
    </script>
</body>
</html>