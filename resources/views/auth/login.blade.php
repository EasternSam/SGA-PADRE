<x-guest-layout>
    {{-- Estilos personalizados inline para este componente --}}
    <style>
        /* Fondo con imagen corporativa sutil */
        body {
            /* Usamos una imagen de Unsplash de arquitectura moderna/universitaria */
            background-image: url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0; /* Asegurar que no haya margen por defecto */
        }
        
        /* Capa oscura sobre la imagen para resaltar el formulario */
        .overlay {
            position: fixed; /* Fixed para cubrir todo incluso al scrollear */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85); /* Blanco semitransparente */
            backdrop-filter: blur(4px); /* Blur suave */
            z-index: -1;
        }

        /* 1. Ajuste solicitado para .login-wrapper */
        .login-wrapper {
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        /* 2. Definición solicitada para .bg-sga-card */
        .bg-sga-card {
            --tw-bg-opacity: 1;
            background-color: rgb(30 58 138);
        }

        .login-card {
            background: white; /* Mantenemos blanco por defecto para que sea legible */
            padding: 40px;
            /* Se aplican clases de Tailwind en el HTML, pero mantenemos estilos base aquí por si acaso */
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
            max-width: 450px;
            width: 100%;
            position: relative; 
            z-index: 10;
        }

        /* LOGO CENTRADO */
        .brand-logo {
            display: flex;
            justify-content: center; 
            margin-bottom: 25px;
        }
        
        .brand-logo svg {
            height: 80px; 
            width: auto;
            color: #4f46e5; 
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            font-size: 26px;
            font-weight: 800;
            color: #111827; 
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-text p {
            color: #6b7280; 
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        /* Botón primario personalizado */
        .btn-primary-custom {
            width: 100%;
            display: flex; 
            justify-content: center;
            align-items: center;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            background-color: #4f46e5;
            color: white;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }
        
        .btn-primary-custom:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 30px 0 20px 0;
            color: #9ca3af;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider::before { margin-right: 15px; }
        .divider::after { margin-left: 15px; }

        .register-link {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background-color: #f9fafb; 
            color: #374151; 
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid #e5e7eb;
        }

        .register-link:hover {
            background-color: #f3f4f6; 
            color: #111827; 
            border-color: #d1d5db;
        }
    </style>

    {{-- Fondo Overlay --}}
    <div class="overlay"></div>

    <div class="login-wrapper">
        <!-- 3. Cambio de clases solicitado en el contenedor de la tarjeta -->
        <div class="w-full sm:max-w-md mt-6 px-6 py-6 rounded-lg login-card"> 
            

            <!-- Texto de Bienvenida -->
            <div class="welcome-text">
                <h2>Portal Académico</h2>
                <p>Ingresa tus credenciales para acceder</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Campo de Login -->
                <div class="form-group">
                    <x-input-label for="login" :value="__('Email o Matrícula')" />
                    <x-text-input id="login" class="block mt-1 w-full"
                                    type="text"
                                    name="login"
                                    :value="old('login')"
                                    required autofocus autocomplete="username" 
                                    placeholder="Ej: 2024-0001 o usuario@correo.com" />
                    <x-input-error :messages="$errors->get('login')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="flex justify-between items-center mb-1">
                        <x-input-label for="password" :value="__('Contraseña')" />
                        @if (Route::has('password.request'))
                            <a class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition-colors" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                    
                    <x-text-input id="password" class="block w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" 
                                    placeholder="••••••••" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    
                    <div class="mt-2 flex items-start gap-2 text-xs text-gray-500 bg-blue-50 p-2 rounded border border-blue-100">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Si eres nuevo ingreso, tu contraseña inicial es tu número de cédula (sin guiones).</span>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="block mt-4 mb-6">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 transition duration-150 ease-in-out" name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Mantener sesión activa') }}</span>
                    </label>
                </div>

                <!-- Botón Login -->
                <button type="submit" class="btn-primary-custom">
                    {{ __('Ingresar al Portal') }}
                </button>
            </form>

            <!-- Separador -->
            <div class="divider">
                <span>¿Aún no eres estudiante?</span>
            </div>

            <!-- Botón Registro -->
            <a href="{{ route('student.register.link') }}" class="register-link">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Solicitar Admisión / Nuevo Ingreso
            </a>
        </div>
    </div>
</x-guest-layout>