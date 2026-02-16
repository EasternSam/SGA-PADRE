<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SGA Padre - Login Preview</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (CDN para la vista previa) -->
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

    <style>
        body {
            min-height: 100vh;
            color: #e2e8f0;
            /* Simulando el color por defecto o dinámico */
            background-color: #0f172a; 
        }

        /* Glassmorphism Card */
        .glass-panel {
            background: rgba(17, 25, 40, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.125);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* Inputs */
        .glass-input {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1rem;
            padding-right: 1rem;
            transition: all 0.3s ease;
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

        /* Labels */
        label {
            color: #cbd5e1 !important;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
            display: block;
        }

        /* Checkbox */
        input[type="checkbox"] {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #6366f1;
            border-radius: 0.25rem;
            height: 1rem;
            width: 1rem;
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
            cursor: pointer;
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
            text-decoration: none;
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

<body class="font-sans antialiased overflow-x-hidden relative flex items-center justify-center">

    <!-- Fondo Interactivo -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <!-- Orbe 1 -->
        <div class="orb w-[500px] h-[500px] bg-indigo-600/40 -top-20 -left-20 animate-float"></div>
        
        <!-- Orbe 2 -->
        <div class="orb w-[400px] h-[400px] bg-purple-600/40 bottom-0 right-0 animate-float-delayed"></div>
        
        <!-- Orbe 3 (Centro sutil) -->
        <div class="orb w-[600px] h-[600px] bg-blue-600/20 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 animate-pulse-slow"></div>
    </div>

    <!-- Contenedor Principal -->
    <div class="w-full min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 p-4">
        
        <!-- Logo y Título -->
        <div class="mb-8 text-center relative z-10 animate-fade-in-down">
            <a href="#" class="flex flex-col items-center group decoration-0">
                <!-- Placeholder Logo (Simulando x-application-logo) -->
                <svg class="w-20 h-20 fill-current text-gray-200 mb-4 drop-shadow-md" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002-.34.18a.75.75 0 0 1-.707 0A50.009 50.009 0 0 0 7.5 12.174v-.224c0-.131.067-.248.182-.311a51.02 51.02 0 0 1 2.313-1.19.75.75 0 1 0-.65-1.35 49.508 49.508 0 0 0-2.5 1.289l-.004.002a.75.75 0 0 1-.726.028 50.87 50.87 0 0 0-2.294-1.277.75.75 0 0 1-.36-1.077 6.162 6.162 0 0 1 1.579-1.928.75.75 0 0 1 .892.15c.677.636 1.39 1.182 2.128 1.636a.75.75 0 0 0 .822-1.272 47.96 47.96 0 0 0-2.92-2.072 7.63 7.63 0 0 1 1.77-1.352 60.694 60.694 0 0 1 3.99-1.932Zm-6.702 9.497 2.76 1.459a49.95 49.95 0 0 1 3.23 1.63c.6.329 1.2.65 1.797.96l.272.138.27-.136c.599-.31 1.2-.633 1.802-.963a49.92 49.92 0 0 1 3.222-1.628l2.772-1.465a51.463 51.463 0 0 1 .006 3.424 53.67 53.67 0 0 1-5.736 10.183.75.75 0 0 1-1.326 0 53.659 53.659 0 0 1-5.73-10.177 51.42 51.42 0 0 1-3.34-3.432Zm13.313-3.643c-1.377.75-2.78 1.463-4.207 2.125a51.428 51.428 0 0 0-4.206-2.125 62.19 62.19 0 0 0 4.206-2.235 62.24 62.24 0 0 0 4.207 2.235Z" />
                </svg>
                
                <h1 class="text-3xl font-bold text-white tracking-tight drop-shadow-lg">
                    SGA Padre
                </h1>
                <p class="text-indigo-200 text-sm font-medium tracking-wide mt-1 uppercase opacity-80">
                    Acceso Seguro
                </p>
            </a>
        </div>

        <!-- Tarjeta del Formulario -->
        <div class="w-full sm:max-w-md relative z-10">
            <!-- Efecto de borde brillante -->
            <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-30 animate-pulse"></div>
            
            <div class="glass-panel px-8 py-10 shadow-2xl overflow-hidden rounded-2xl relative">
                
                <!-- Formulario simulado -->
                <form action="#" class="space-y-6" onsubmit="event.preventDefault(); alert('Login Demo');">
                    
                    <!-- Email Address -->
                    <div class="space-y-1">
                        <label for="email">Email / Correo</label>
                        <div class="relative">
                            <input id="email" class="glass-input" type="email" name="email" required autofocus placeholder="ejemplo@correo.com" />
                            <!-- Icono -->
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-indigo-300/50">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1">
                        <label for="password">Contraseña</label>
                        <div class="relative">
                            <input id="password" class="glass-input" type="password" name="password" required placeholder="••••••••" />
                            <!-- Icono -->
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-indigo-300/50">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mt-4">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer !mb-0" style="display: flex;">
                            <input id="remember_me" type="checkbox" name="remember">
                            <span class="ml-2 text-sm text-gray-300 hover:text-white transition-colors">Recordarme</span>
                        </label>

                        <a class="glass-link text-sm" href="#">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="glass-button">
                            Iniciar Sesión
                        </button>
                    </div>
                    
                    <!-- Registration Link -->
                    <div class="text-center mt-6 border-t border-white/10 pt-4">
                        <p class="text-sm text-slate-400">
                            ¿No tienes cuenta?
                            <a href="#" class="text-indigo-400 hover:text-indigo-300 font-semibold transition-colors ml-1">
                                Regístrate aquí
                            </a>
                        </p>
                    </div>
                </form>

            </div>
            
            <!-- Footer discreto -->
            <div class="text-center mt-6">
                <p class="text-xs text-slate-400 font-light">
                    &copy; 2026 SGA Padre. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>

</body>
</html>