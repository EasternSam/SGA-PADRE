<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50 dark:bg-[#0f0f17] overscroll-none">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'SGA-PADRE') }}</title>

        {{-- Dark mode script to avoid flash --}}
        <script>
            (function() {
                var d = localStorage.getItem('darkMode');
                if (d === 'true' || (!d && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- Dynamic Institutional Palette & Brand Settings -->
        @php
            $primaryColor = \App\Models\Setting::get('brand_primary_color') ?? '#bb3902';
            $secondaryColor = \App\Models\Setting::get('brand_secondary_color') ?? '#1c1917';
            $accentColor = \App\Models\Setting::get('brand_accent_color') ?? '#ea580c';
            $neutralColor = \App\Models\Setting::get('brand_neutral_color') ?? '#78716c';
            $favicon = \App\Models\Setting::get('favicon') ?? '/centuu.png';
            $appIcon = \App\Models\Setting::get('app_icon') ?? '/centuu.png';

            if (!function_exists('hex2rgb')) {
                function hex2rgb($hex, $default = '187 57 2') {
                    $clean = ltrim(trim($hex), '#');
                    if (strlen($clean) === 3) {
                        $clean = $clean[0].$clean[0].$clean[1].$clean[1].$clean[2].$clean[2];
                    }
                    if (strlen($clean) !== 6 || !ctype_xdigit($clean)) {
                        return $default;
                    }
                    return hexdec(substr($clean, 0, 2)) . ' ' . hexdec(substr($clean, 2, 2)) . ' ' . hexdec(substr($clean, 4, 2));
                }
            }

            $primaryRgb = hex2rgb($primaryColor, '187 57 2');
            $secondaryRgb = hex2rgb($secondaryColor, '28 25 23');
            $accentRgb = hex2rgb($accentColor, '234 88 12');
            $neutralRgb = hex2rgb($neutralColor, '120 113 108');
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
