<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a0a53">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Academic+') }}">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ config('app.name', 'GridBase Academic+') }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    @php
        $favicon = \App\Models\Setting::get('favicon');
        $appIcon = \App\Models\Setting::get('app_icon');
        $faviconUrl = $favicon ? $favicon : '/branding/academic-favicon.png';
        $appIconUrl = $appIcon ? $appIcon : '/branding/academic-favicon.png';
    @endphp
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $appIconUrl }}">
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

    <!-- Fonts (Outfit & Playfair Display / Serif) -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800,900|playfair-display:400,400i,600,700,700i&display=swap" rel="stylesheet" />

    <!-- Scripts del Proyecto -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $logoUrl = null;
        $loginBanner = null;
        $navBackground = 'linear-gradient(to bottom right, #5a1256, #1a0a53)';

        if (isset($branding)) {
            $logoUrl = $branding->logo_url ?? null;
            $navBackground = $branding->primary_color ?? $navBackground;
            $loginBanner = $branding->login_banner ?? null;
        } else {
            try {
                $logoUrl = \App\Models\Setting::get('institution_logo') ?? \App\Models\SystemOption::getOption('logo');
                $loginBanner = \App\Models\Setting::get('login_banner');
                $color = \App\Models\Setting::get('brand_primary_color');
                if ($color) {
                    $navBackground = $color;
                }
            } catch (\Exception $e) {}
        }

        $bannerSrc = $loginBanner 
            ? (Str::startsWith($loginBanner, 'http') ? $loginBanner : asset($loginBanner))
            : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80';
    @endphp

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }

        .font-editorial {
            font-family: 'Playfair Display', Georgia, serif;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: {{ $navBackground }};
            min-height: 100vh;
            min-height: 100dvh;
            color: #1e293b;
        }

        /* Ambient background glow */
        .bg-glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body class="font-sans antialiased overflow-x-hidden min-h-screen flex items-center justify-center p-3 sm:p-6 lg:p-8 relative selection:bg-indigo-500 selection:text-white">
    
    <!-- Dynamic Ambient Background: Low-opacity blurred banner texture + glowing ambient orbs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10 select-none">
        <!-- Ambient Image Layer -->
        <div class="absolute inset-0 w-full h-full"
             style="background-image: url('{{ $bannerSrc }}'); background-size: cover; background-position: center; filter: blur(0px); mix-blend-mode: multiply; opacity: 0.10;">
        </div>

        <!-- Ambient glow orbs matching lateral nav palette -->
        <div class="bg-glow-orb w-[650px] h-[650px] bg-pink-500/20 -top-32 -left-32"></div>
        <div class="bg-glow-orb w-[550px] h-[550px] bg-indigo-500/25 bottom-0 right-0"></div>
        <div class="bg-glow-orb w-[450px] h-[450px] bg-purple-500/25 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
    </div>

    {{ $slot }}
</body>
</html>
