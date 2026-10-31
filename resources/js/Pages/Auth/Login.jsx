import React, { useState } from 'react';
import { useForm, usePage, Head, Link } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { branding } = usePage().props;
    const [showPassword, setShowPassword] = useState(false);

    const form = useForm({
        login: '',
        password: '',
        remember: false,
        website: '', // Honeypot
    });

    const primaryColor = branding?.primary_color || '#211170';
    const navbarType = branding?.navbar_type || 'gradient';
    const navGradientStart = branding?.navbar_gradient_start || primaryColor;
    const navGradientEnd = branding?.navbar_gradient_end || '#1a0a53';
    const navGradientDir = branding?.navbar_gradient_direction || 'to bottom right';
    const showcaseBg = navbarType === 'gradient'
        ? `linear-gradient(${navGradientDir}, ${navGradientStart}, ${navGradientEnd})`
        : primaryColor;
    const logoUrl = branding?.logo || '/branding/academic-logo.png';
    const bannerSrc = branding?.login_banner || '';
    const appName = branding?.name || 'Academic+';

    const submit = (e) => {
        e.preventDefault();
        form.post('/login', {
            onFinish: () => form.reset('password'),
        });
    };

    return (
        <div className="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-slate-950 relative overflow-hidden font-sans">
            <Head title={`Iniciar Sesión - ${appName}`} />

            {/* Fondo con Degradado y Resplandor Ambiental */}
            {bannerSrc && (
                <div 
                    className="absolute inset-0 bg-cover bg-center pointer-events-none opacity-20 filter blur-sm"
                    style={{ backgroundImage: `url(${bannerSrc})` }}
                />
            )}
            <div className="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none" />
            <div className="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/25 rounded-full blur-3xl pointer-events-none" />

            {/* Contenedor Principal Centrado con Bordes Curvos y Sombra */}
            <div 
                className="w-full max-w-6xl xl:max-w-7xl bg-white dark:bg-slate-900 shadow-[0_25px_70px_-15px_rgba(0,0,0,0.5)] border border-white/20 flex flex-col lg:flex-row min-h-0 lg:min-h-[680px] my-auto backdrop-blur-xl relative z-10"
                style={{ borderRadius: '32px', overflow: 'hidden' }}
            >
                {/* ── Columna Izquierda: Showcase con Logo y Banner ── */}
                <div 
                    className="hidden lg:flex lg:w-1/2 relative flex-col justify-between select-none border-r border-white/10"
                    style={{ background: showcaseBg }}
                >
                    {/* Atmospheric Image Backdrop inside lateral column */}
                    {bannerSrc && (
                        <div 
                            className="absolute inset-0 w-full h-full pointer-events-none z-0"
                            style={{
                                backgroundImage: `url(${bannerSrc})`,
                                backgroundSize: 'cover',
                                backgroundPosition: 'center',
                                mixBlendMode: 'multiply',
                                opacity: 0.15,
                            }}
                        />
                    )}

                    {/* Ambient Glows */}
                    <div className="absolute -right-24 -top-24 w-96 h-96 bg-blue-400/20 rounded-full blur-3xl pointer-events-none" />
                    <div className="absolute -left-24 -bottom-24 w-96 h-96 bg-indigo-500/25 rounded-full blur-3xl pointer-events-none" />

                    {/* Centro: Logo Centrado */}
                    <div className="relative z-10 p-8 sm:p-12 lg:p-14 flex flex-col items-center justify-center my-auto flex-1">
                        {logoUrl && (
                            <div className="flex items-center justify-center w-full py-6">
                                <img 
                                    src={logoUrl} 
                                    alt={appName} 
                                    className="w-auto max-w-[380px] object-contain drop-shadow-2xl" 
                                    style={{ height: '5.5rem' }}
                                />
                            </div>
                        )}
                    </div>

                    {/* Bottom Banner Image con máscara de degradado suave */}
                    {bannerSrc && (
                        <div className="relative w-full mt-auto overflow-hidden pointer-events-none select-none z-10 flex items-end">
                            <img 
                                src={bannerSrc}
                                alt="Portal Académico" 
                                className="w-full h-72 sm:h-80 lg:h-96 object-cover object-top"
                                style={{
                                    maskImage: 'linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.5) 20%, black 60%, black 100%)',
                                    WebkitMaskImage: 'linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.5) 20%, black 60%, black 100%)',
                                }}
                            />
                        </div>
                    )}
                </div>

                {/* ── Columna Derecha: Formulario de Autenticación ── */}
                <div className="w-full lg:flex-1 p-6 sm:p-10 lg:p-14 xl:p-16 flex flex-col justify-center bg-white dark:bg-slate-900 relative">
                    <div className="w-full max-w-sm sm:max-w-md mx-auto my-auto py-6">
                        {/* En Móviles: Logo con fondo de marca */}
                        {logoUrl && (
                            <div className="lg:hidden mb-6 flex justify-start">
                                <div 
                                    className="p-2.5 px-3.5 shadow-md inline-block" 
                                    style={{ background: showcaseBg, borderRadius: '16px' }}
                                >
                                    <img 
                                        src={logoUrl} 
                                        alt={appName} 
                                        className="h-8 sm:h-9 w-auto object-contain" 
                                    />
                                </div>
                            </div>
                        )}

                        {/* Encabezado del Formulario */}
                        <div className="mb-6">
                            <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                Iniciar Sesión
                            </h1>
                            
                        </div>

                        {/* Status message */}
                        {status && (
                            <div className="mb-4 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800">
                                {status}
                            </div>
                        )}

                        <form onSubmit={submit} className="space-y-4 sm:space-y-5">
                            {/* Honeypot anti-bot */}
                            <div style={{ position: 'absolute', left: '-9999px', top: '-9999px', opacity: 0 }} aria-hidden="true">
                                <input 
                                    type="text" 
                                    name="website" 
                                    value={form.data.website} 
                                    onChange={(e) => form.setData('website', e.target.value)} 
                                    tabIndex={-1} 
                                    autoComplete="off" 
                                />
                            </div>

                            {/* Email o Matrícula */}
                            <div>
                                <label htmlFor="login" className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                    Email o Matrícula
                                </label>
                                <input 
                                    id="login" 
                                    type="text"
                                    value={form.data.login}
                                    onChange={(e) => form.setData('login', e.target.value)}
                                    required 
                                    autoFocus 
                                    autoComplete="username" 
                                    placeholder="2024-0001 o usuario@correo.com"
                                    className="w-full px-4 py-3.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-sm shadow-sm rounded-xl"
                                />
                                {form.errors.login && (
                                    <p className="text-xs text-red-500 mt-1.5">{form.errors.login}</p>
                                )}
                            </div>

                            {/* Contraseña */}
                            <div>
                                <label htmlFor="password" className="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                    Contraseña
                                </label>
                                <div className="relative">
                                    <input 
                                        id="password" 
                                        type={showPassword ? 'text' : 'password'}
                                        value={form.data.password}
                                        onChange={(e) => form.setData('password', e.target.value)}
                                        required 
                                        autoComplete="current-password" 
                                        placeholder="••••••••"
                                        className="w-full px-4 py-3.5 pr-11 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-primary/30 focus:border-brand-primary transition-all text-sm shadow-sm rounded-xl"
                                    />
                                    <button 
                                        type="button" 
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                        aria-label="Mostrar u ocultar contraseña"
                                    >
                                        {showPassword ? (
                                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        ) : (
                                            <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        )}
                                    </button>
                                </div>
                                {form.errors.password && (
                                    <p className="text-xs text-red-500 mt-1.5">{form.errors.password}</p>
                                )}

                                {/* Nota de nuevo ingreso */}
                                <div className="mt-2 flex items-center gap-2 px-1 text-xs text-slate-500 dark:text-slate-400">
                                    <svg className="w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd"/>
                                    </svg>
                                    <span>Si eres <strong className="text-slate-700 dark:text-slate-300 font-semibold">nuevo ingreso</strong>, tu clave es tu cédula <em>(sin guiones)</em>.</span>
                                </div>
                            </div>

                            {/* Remember Me & Forgot Password */}
                            <div className="flex flex-wrap items-center justify-between gap-y-2 pt-1">
                                <label htmlFor="remember" className="inline-flex items-center cursor-pointer">
                                    <input 
                                        id="remember" 
                                        type="checkbox" 
                                        checked={form.data.remember}
                                        onChange={(e) => form.setData('remember', e.target.checked)}
                                        className="w-4 h-4 rounded border-slate-300 text-brand-primary shadow-sm focus:ring-brand-primary dark:bg-slate-800 dark:border-slate-700"
                                    />
                                    <span className="ml-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                        Mantener sesión activa
                                    </span>
                                </label>

                                <a 
                                    href="/forgot-password" 
                                    className="text-xs sm:text-sm font-semibold hover:underline transition-colors text-blue-600 dark:text-blue-400"
                                >
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            {/* Botón Principal */}
                            <div className="pt-2">
                                <button 
                                    type="submit" 
                                    disabled={form.processing}
                                    className="w-full py-4 px-4 text-white font-bold text-sm tracking-wide shadow-lg hover:shadow-xl transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2 hover:brightness-110 rounded-xl disabled:opacity-50"
                                    style={{ background: primaryColor }}
                                >
                                    {form.processing && (
                                        <svg className="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    )}
                                    <span>Ingresar al Portal</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {/* ── Esquina Inferior Izquierda: Copyright ── */}
            <div className="fixed bottom-6 left-6 lg:bottom-8 lg:left-8 z-40 hidden md:block pointer-events-none select-none">
                <p className="text-xs font-light drop-shadow-md text-white/70">
                    &copy; {new Date().getFullYear()} {appName}. Todos los derechos reservados.
                </p>
            </div>

            {/* ── Esquina Inferior Derecha: Solicitar Admisión ── */}
            <div className="fixed bottom-6 right-6 lg:bottom-8 lg:right-8 z-40 flex items-center gap-3 select-none">
                <span className="text-xs sm:text-sm font-medium drop-shadow-md hidden sm:inline text-white">¿Aún no eres estudiante?</span>
                <a 
                    href="/register" 
                    className="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-900 dark:text-white font-bold text-xs sm:text-sm shadow-[0_10px_30px_rgba(0,0,0,0.35)] hover:shadow-[0_15px_35px_rgba(0,0,0,0.45)] transition-all transform hover:scale-105 active:scale-95 rounded-xl"
                >
                    <span className="text-slate-950 dark:text-white">Solicitar Admisión</span>
                    <svg className="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    );
}
