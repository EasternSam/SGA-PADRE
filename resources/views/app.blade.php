<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-[#0f0f17] overscroll-none">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Academic+') }}</title>

        {{-- Dark mode script to avoid flash + purge stale SW caches --}}
        <script>
            (function() {
                var d = localStorage.getItem('darkMode');
                if (d === 'true' || (!d && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                try {
                    var swVersion = 'sga-v5-20261103';
                    if (localStorage.getItem('sga_sw_version') !== swVersion) {
                        if ('caches' in window) {
                            caches.keys().then(function(names) {
                                names.forEach(function(name) {
                                    if (name !== swVersion) caches.delete(name);
                                });
                            });
                        }
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.getRegistrations().then(function(regs) {
                                regs.forEach(function(reg) { reg.update(); });
                            });
                        }
                        localStorage.setItem('sga_sw_version', swVersion);
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Dynamic Institutional Palette & Brand Settings -->
        @php
            $resolvedBrand = \App\Support\BrandingDefaults::fromDatabase();
            $primaryColor = $resolvedBrand['primary_color'];
            $secondaryColor = $resolvedBrand['secondary_color'];
            $accentColor = $resolvedBrand['accent_color'];
            $neutralColor = $resolvedBrand['neutral_color'];
            $favicon = $resolvedBrand['favicon'];
            $appIcon = $resolvedBrand['app_icon'];

            $primaryRgb = $resolvedBrand['primary_rgb'];
            $secondaryRgb = $resolvedBrand['secondary_rgb'];
            $accentRgb = $resolvedBrand['accent_rgb'];
            $neutralRgb = $resolvedBrand['neutral_rgb'];
        @endphp
        <link rel="icon" type="image/png" href="{{ $favicon }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $appIcon }}">

        <!-- Fonts & Icons (Inter Variable, Outfit & Font Awesome) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|outfit:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <style>
            [x-cloak] { display: none !important; }
            :root {
                --brand-primary: {{ $primaryColor }};
                --brand-secondary: {{ $secondaryColor }};
                --brand-accent: {{ $accentColor }};
                --brand-neutral: {{ $neutralColor }};

                --palette-primary: {{ $primaryColor }};
                --palette-secondary: {{ $secondaryColor }};
                --palette-accent: {{ $accentColor }};
                --palette-neutral: {{ $neutralColor }};

                --color-primary: {{ $primaryRgb }};
                --color-secondary: {{ $secondaryRgb }};
                --color-accent: {{ $accentRgb }};
                --color-neutral: {{ $neutralRgb }};

                --brand-primary-rgb: {{ $primaryRgb }};
                --brand-secondary-rgb: {{ $secondaryRgb }};
                --brand-accent-rgb: {{ $accentRgb }};
                --brand-neutral-rgb: {{ $neutralRgb }};
            }
            .bg-sga-primary { background-color: var(--brand-primary); }
            .text-sga-primary { color: var(--brand-primary); }
            .border-sga-primary { border-color: var(--brand-primary); }
        </style>

        <!-- Scripts & Styles -->
        @routes
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="h-full font-sans antialiased text-slate-600 dark:text-slate-300 overflow-hidden bg-gray-50 dark:bg-[#0f0f17]">
        @inertia
    </body>
</html>
