<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
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
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Outfit"', 'sans-serif'],
                    },
                    boxShadow: {
                        glow: '0 0 40px rgba(99,102,241,0.35)',
                        soft: '0 20px 60px -15px rgba(0,0,0,0.6)'
                    },
                    animation: {
                        'float-slow': 'float 10s ease-in-out infinite',
                        'float-medium': 'float 7s ease-in-out infinite',
                        'float-fast': 'float 5s ease-in-out infinite',
                        'fade-in': 'fadeIn .8s ease-out forwards'
                    },
                    keyframes: {
                        float: {
                            '0%,100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-25px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: 0, transform: 'translateY(10px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        use App\Models\SystemOption;

        $bg = SystemOption::getOption('navbar_color');
        $type = SystemOption::getOption('navbar_type');

        $customBg = null;
        if ($type === 'gradient' && str_contains($bg, 'gradient')) {
            $customBg = $bg;
        } elseif ($type === 'solid' && $bg && $bg !== '#') {
            $customBg = $bg;
        }

        $logoUrl = SystemOption::getOption('logo') ?: SystemOption::getOption('institution_logo');
    @endphp

    <style>
        body {
            background-color: #0b1120;
        }

        .glass-card {
            background: linear-gradient(160deg, rgba(15,23,42,0.85), rgba(30,41,59,0.75));
            backdrop-filter: blur(35px);
            -webkit-backdrop-filter: blur(35px);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .glass-form label {
            color: #c7d2fe !important;
            font-weight: 500 !important;
            font-size: 0.85rem !important;
            letter-spacing: .02em;
        }

        .glass-form input[type="text"],
        .glass-form input[type="email"],
        .glass-form input[type="password"] {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #fff !important;
            border-radius: 0.9rem !important;
            padding: 0.8rem 1rem;
            transition: all .3s ease;
        }

        .glass-form input:focus {
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 2px rgba(99,102,241,.4) !important;
            outline: none !important;
        }

        .glass-form input::placeholder {
            color: rgba(199,210,254,0.35) !important;
        }

        .glass-form button[type="submit"] {
            background: linear-gradient(to right, #4f46e5, #9333ea);
            border: none !important;
            color: white !important;
            font-weight: 600 !important;
            padding: 0.85rem 1rem !important;
            border-radius: 0.9rem !important;
            width: 100%;
            letter-spacing: 0.08em;
            font-size: 0.8rem;
            transition: all .25s ease;
            box-shadow: 0 10px 25px -5px rgba(79,70,229,.45);
        }

        .glass-form button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px -10px rgba(79,70,229,.6);
        }

        .glass-form a {
            color: #a5b4fc !important;
            font-size: 0.8rem;
            transition: .2s;
        }

        .glass-form a:hover {
            color: #fff !important;
        }

        .orb {
            filter: blur(120px);
            opacity: .4;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-100 min-h-screen relative overflow-x-hidden"
      x-data="{ mouseX: 0, mouseY: 0, handleMove(e){ this.mouseX=(e.clientX-window.innerWidth/2)/60; this.mouseY=(e.clientY-window.innerHeight/2)/60;} }"
      @mousemove.window="handleMove">

<div class="absolute inset-0 -z-10" style="{{ $customBg ? 'background: '.$customBg.'; background-size:cover;' : '' }}">

    <!-- Dark overlay to improve contrast -->
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900/70 via-slate-900/60 to-slate-950/80"></div>

    <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-indigo-600 rounded-full orb animate-float-slow"
         :style="`transform: translate(${mouseX*-1}px, ${mouseY*-1}px)`"></div>

    <div class="absolute -bottom-40 -right-40 w-[700px] h-[700px] bg-purple-600 rounded-full orb animate-float-medium"
         :style="`transform: translate(${mouseX}px, ${mouseY}px)`"></div>

</div>

<div class="min-h-screen flex items-center justify-center px-4 py-10">

    <div x-data="{show:false}" x-init="setTimeout(()=>show=true,100)"
         x-show="show"
         x-transition
         class="relative w-full max-w-lg animate-fade-in">

        <div class="absolute -inset-1 bg-gradient-to-br from-indigo-500/30 via-purple-500/20 to-pink-500/30 rounded-3xl blur-xl opacity-70"></div>

        <div class="relative glass-card rounded-3xl shadow-soft p-10">

            <div class="text-center mb-10">
                @if($logoUrl)
                    <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}"
                         class="mx-auto h-24 object-contain drop-shadow-2xl mb-6 transition-transform duration-500 hover:scale-105">
                @endif

                <h1 class="text-4xl font-bold tracking-tight mb-3">
                    {{ config('app.name') }}
                </h1>

                <p class="text-indigo-200/70 text-sm tracking-wide">
                    Sistema de Gestión Académica para Padres
                </p>
            </div>

            <div class="glass-form space-y-5">
                {{ $slot }}
            </div>

            <div class="mt-10 pt-6 border-t border-white/5 text-center">
                <p class="text-xs text-indigo-300/50">
                    &copy; {{ date('Y') }} {{ config('app.name') }} · Plataforma segura y moderna
                </p>
            </div>

        </div>

    </div>

</div>

</body>
</html>
