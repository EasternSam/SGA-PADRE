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

    <!-- Vite removido para evitar conflictos con Tailwind compilado -->

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
</html>