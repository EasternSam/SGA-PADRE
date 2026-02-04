<x-guest-layout>
    {{-- Estilos personalizados inline para este componente --}}
    <style>
        /* Fondo con imagen corporativa sutil */
        body {
            background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); /* Imagen de campus universitario genérica */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        
        /* Capa oscura sobre la imagen para resaltar el formulario */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85); /* Blanco semitransparente para mantener limpieza */
            backdrop-filter: blur(5px);
            z-index: -1;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .brand-logo svg {
            height: 60px;
            width: auto;
            color: #4f46e5; /* Indigo 600 */
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            font-size: 24px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .welcome-text p {
            color: #6b7280;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-primary-custom {
            width: 100%;
            justify-content: center;
            padding: 12px;
            font-size: 16px;
            background-color: #4f46e5;
            transition: background-color 0.2s;
        }
        
        .btn-primary-custom:hover {
            background-color: #4338ca;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #9ca3af;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider::before { margin-right: 10px; }
        .divider::after { margin-left: 10px; }

        .register-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 12px;
            background-color: #f3f4f6;
            color: #4b5563;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }

        .register-link:hover {
            background-color: #e5e7eb;
            color: #1f2937;
        }
    </style>

    {{-- Fondo Overlay (necesario si el layout no lo provee) --}}
    <div class="overlay"></div>

    <div class="login-card">
        <!-- Logo -->
        <div class="brand-logo">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
        </div>

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
                        <a class="text-xs text-indigo-600 hover:text-indigo-800 font-medium" href="{{ route('password.request') }}">
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
                <p class="text-xs text-gray-500 mt-2 italic">
                    * Si eres nuevo ingreso, tu contraseña inicial es tu cédula.
                </p>
            </div>

            <!-- Remember Me -->
            <div class="block mt-4 mb-6">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Mantener sesión activa') }}</span>
                </label>
            </div>

            <!-- Botón Login -->
            <x-primary-button class="btn-primary-custom">
                {{ __('Ingresar al Portal') }}
            </x-primary-button>
        </form>

        <!-- Separador -->
        <div class="divider">
            <span>¿Aún no eres estudiante?</span>
        </div>

        <!-- Botón Registro -->
        <a href="{{ route('student.register.link') }}" class="register-link">
            📝 Solicitar Admisión / Nuevo Ingreso
        </a>
    </div>
</x-guest-layout>