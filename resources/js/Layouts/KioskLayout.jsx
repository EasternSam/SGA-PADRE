import React, { useState, useEffect } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import ModalStandardizer from '../components/layout/ModalStandardizer';

export default function KioskLayout({
    children,
    student,
    backUrl,
    showBack = true,
}) {
    const { flash = {}, branding = {} } = usePage().props;

    const [largeText, setLargeText] = useState(() => {
        if (typeof window !== 'undefined') {
            return window.localStorage.getItem('kiosk_large_text') === '1';
        }
        return false;
    });

    const [darkMode, setDarkMode] = useState(() => {
        if (typeof window !== 'undefined') {
            return window.localStorage.getItem('kiosk_dark_mode') === '1';
        }
        return false;
    });

    const appName = branding?.name || branding?.institution_name || 'Academic+';
    const logoUrl = branding?.logo || branding?.logo_url || '/branding/academic-logo.png';
    const bannerSrc = branding?.login_banner || '';

    const primaryColor = branding?.primary_color || '#181956';
    const secondaryColor = branding?.secondary_color || '#064e3b';
    const accentColor = branding?.accent_color || '#10b981';
    const neutralColor = branding?.neutral_color || '#12b981';

    const navbarType = branding?.navbar_type || 'gradient';
    const navGradientStart = branding?.navbar_gradient_start || primaryColor;
    const navGradientEnd = branding?.navbar_gradient_end || secondaryColor || '#1a0a53';
    const navGradientDir = branding?.navbar_gradient_direction || 'to bottom right';
    const showcaseBg =
        navbarType === 'gradient'
            ? `linear-gradient(${navGradientDir}, ${navGradientStart}, ${navGradientEnd})`
            : `linear-gradient(145deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;

    const isLogged = Boolean(student);
    const homeHref = isLogged ? '/kiosk/dashboard' : '/kiosk/login';
    const resolvedBackUrl = backUrl || homeHref;

    useEffect(() => {
        if (typeof document !== 'undefined') {
            document.documentElement.classList.toggle('dark', darkMode);
        }
    }, [darkMode]);

    const toggleLargeText = () => {
        const next = !largeText;
        setLargeText(next);
        if (typeof window !== 'undefined') {
            window.localStorage.setItem('kiosk_large_text', next ? '1' : '0');
        }
    };

    const toggleDarkMode = () => {
        const next = !darkMode;
        setDarkMode(next);
        if (typeof window !== 'undefined') {
            window.localStorage.setItem('kiosk_dark_mode', next ? '1' : '0');
        }
    };

    const handleLogout = () => {
        router.post('/kiosk/logout');
    };

    return (
        <div
            style={{
                '--kiosk-primary': primaryColor,
                '--kiosk-secondary': secondaryColor,
                '--kiosk-accent': accentColor,
                '--kiosk-neutral': neutralColor,
            }}
            className={`kiosk-theme-root min-h-screen w-full flex items-center justify-center p-3 sm:p-5 lg:p-7 bg-slate-950 relative overflow-hidden font-sans select-none ${
                largeText ? 'text-[108%]' : 'text-base'
            }`}
        >
            <style>{`
                .kiosk-theme-root .kiosk-surface {
                    background-color: ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-primary) 18%, #0F172A)`
                            : `color-mix(in srgb, var(--kiosk-primary) 4.5%, #F8FAFC)`
                    };
                    border: 1.5px solid ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-accent) 28%, #1E293B)`
                            : `color-mix(in srgb, var(--kiosk-primary) 12%, #E2E8F0)`
                    };
                }
                .kiosk-theme-root .kiosk-card-interactive {
                    background-color: ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-primary) 18%, #0F172A)`
                            : `color-mix(in srgb, var(--kiosk-primary) 4.5%, #F8FAFC)`
                    };
                    border: 2px solid ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-accent) 26%, #1E293B)`
                            : `color-mix(in srgb, var(--kiosk-primary) 11%, #E2E8F0)`
                    };
                    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
                }
                .kiosk-theme-root .kiosk-card-interactive:hover {
                    background-color: ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-secondary) 28%, #1E293B)`
                            : `color-mix(in srgb, var(--kiosk-accent) 9%, #F1F5F9)`
                    };
                    border-color: var(--kiosk-accent);
                }
                .kiosk-theme-root .kiosk-icon-btn {
                    background-color: ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-primary) 24%, #1E293B)`
                            : `color-mix(in srgb, var(--kiosk-primary) 6%, #F1F5F9)`
                    };
                    border: 1px solid ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-accent) 30%, #334155)`
                            : `color-mix(in srgb, var(--kiosk-primary) 14%, #E2E8F0)`
                    };
                    color: ${darkMode ? '#F8FAFC' : 'var(--kiosk-primary)'};
                }
                .kiosk-theme-root .kiosk-icon-btn:hover {
                    border-color: var(--kiosk-accent);
                }
                .kiosk-theme-root .kiosk-key {
                    background-color: ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-primary) 14%, #1E293B)`
                            : '#FFFFFF'
                    };
                    border: 1px solid ${
                        darkMode
                            ? `color-mix(in srgb, var(--kiosk-accent) 24%, #334155)`
                            : `color-mix(in srgb, var(--kiosk-primary) 13%, #E2E8F0)`
                    };
                    color: ${darkMode ? '#F8FAFC' : '#0F172A'};
                }
                .kiosk-theme-root .kiosk-key:active {
                    background-color: var(--kiosk-primary);
                    color: #FFFFFF;
                }
                .kiosk-theme-root input.kiosk-bare-input,
                .kiosk-theme-root input.kiosk-bare-input:focus {
                    border: none !important;
                    outline: none !important;
                    box-shadow: none !important;
                    background: transparent !important;
                    padding: 0 !important;
                }
            `}</style>

            {/* Fondo con Imagen de Login y Resplandor Ambiental (Idéntico a /login) */}
            {bannerSrc && (
                <div
                    className="absolute inset-0 bg-cover bg-center pointer-events-none opacity-25 filter blur-xs"
                    style={{ backgroundImage: `url(${bannerSrc})` }}
                />
            )}
            <div
                style={{ backgroundColor: primaryColor }}
                className="absolute -top-40 -left-40 w-96 h-96 opacity-35 rounded-full blur-3xl pointer-events-none"
            />
            <div
                style={{ backgroundColor: accentColor }}
                className="absolute -bottom-40 -right-40 w-96 h-96 opacity-25 rounded-full blur-3xl pointer-events-none"
            />

            {/* Contenedor Principal Centrado con Bordes Curvos y Sombra (Idéntico a /login) */}
            <div
                className="w-full max-w-6xl xl:max-w-7xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-[0_25px_70px_-15px_rgba(0,0,0,0.55)] border border-white/20 flex flex-col lg:flex-row min-h-0 lg:min-h-[680px] my-auto backdrop-blur-xl relative z-10"
                style={{ borderRadius: '32px', overflow: 'hidden' }}
            >
                {/* ── Columna Izquierda: Showcase Institucional con Logo y Fachada (Idéntico a /login) ── */}
                <div
                    className="hidden lg:flex lg:w-[37%] xl:w-[39%] relative flex-col justify-between select-none border-r border-white/10 shrink-0"
                    style={{ background: showcaseBg }}
                >
                    {bannerSrc && (
                        <div
                            className="absolute inset-0 w-full h-full pointer-events-none z-0"
                            style={{
                                backgroundImage: `url(${bannerSrc})`,
                                backgroundSize: 'cover',
                                backgroundPosition: 'center',
                                mixBlendMode: 'multiply',
                                opacity: 0.18,
                            }}
                        />
                    )}

                    {/* Ambient Glows */}
                    <div
                        style={{ backgroundColor: accentColor }}
                        className="absolute -right-24 -top-24 w-80 h-80 opacity-25 rounded-full blur-3xl pointer-events-none"
                    />
                    <div
                        style={{ backgroundColor: primaryColor }}
                        className="absolute -left-24 -bottom-24 w-80 h-80 opacity-35 rounded-full blur-3xl pointer-events-none"
                    />

                    {/* Centro: Logo Centrado */}
                    <div className="relative z-10 p-8 lg:p-10 flex flex-col items-center justify-center my-auto flex-1">
                        <Link href={homeHref} className="flex items-center justify-center w-full py-4 transition-transform hover:scale-[1.02]">
                            <img
                                src={logoUrl}
                                alt={appName}
                                className="w-auto max-w-[300px] object-contain drop-shadow-2xl"
                                style={{ height: '5.25rem' }}
                            />
                        </Link>

                        {student && (
                            <div className="mt-4 w-full max-w-[260px] rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 px-4 py-3 text-center text-white">
                                <div className="text-sm font-bold truncate">{student.name}</div>
                                <div className="text-xs font-mono opacity-80 mt-0.5">{student.student_code}</div>
                            </div>
                        )}
                    </div>

                    {/* Bottom Banner Image con máscara de degradado suave */}
                    {bannerSrc && (
                        <div className="relative w-full mt-auto overflow-hidden pointer-events-none select-none z-10 flex items-end">
                            <img
                                src={bannerSrc}
                                alt={appName}
                                className="w-full h-64 xl:h-76 object-cover object-top"
                                style={{
                                    maskImage: 'linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.5) 20%, black 60%, black 100%)',
                                    WebkitMaskImage: 'linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.5) 20%, black 60%, black 100%)',
                                }}
                            />
                        </div>
                    )}
                </div>

                {/* ── Columna Derecha: Superficie Interactiva Kiosco / Tablet ── */}
                <div className="w-full lg:flex-1 p-5 sm:p-8 lg:p-10 xl:p-12 flex flex-col justify-between bg-white dark:bg-slate-900 relative">
                    {/* Top Bar (Responsive: Logo con fondo de marca en móvil/tablet vertical + Controles) */}
                    <div
                        role="banner"
                        className="w-full flex items-center justify-between gap-3 pb-4 shrink-0"
                    >
                        <div className="flex items-center gap-3">
                            {/* Logo en Móvil / Tablet Vertical (<lg) con fondo institucional */}
                            <Link
                                href={homeHref}
                                className="lg:hidden p-2 px-3 shadow-md inline-flex items-center"
                                style={{ background: showcaseBg, borderRadius: '14px' }}
                            >
                                <img
                                    src={logoUrl}
                                    alt={appName}
                                    className="h-7 sm:h-8 w-auto object-contain"
                                />
                            </Link>

                            {showBack && (
                                <Link
                                    href={resolvedBackUrl}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl kiosk-icon-btn text-sm font-semibold transition-all active:scale-95"
                                >
                                    <svg className="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                        <path d="M19 12H5M12 19l-7-7 7-7" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                    <span>Volver</span>
                                </Link>
                            )}
                        </div>

                        {/* Controles Minimalistas Derecha (TT, Contraste, Inicio, Salir) */}
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={toggleLargeText}
                                aria-label="Tamaño de texto"
                                style={largeText ? { backgroundColor: primaryColor, color: '#FFFFFF' } : undefined}
                                className={`w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center transition-all active:scale-95 ${
                                    largeText ? '' : 'kiosk-icon-btn'
                                }`}
                            >
                                <span className="font-sans font-bold tracking-tighter text-sm sm:text-base leading-none flex items-baseline">
                                    T<span className="text-[10px] font-extrabold -ml-0.5">T</span>
                                </span>
                            </button>

                            <button
                                type="button"
                                onClick={toggleDarkMode}
                                aria-label="Contraste"
                                className="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center transition-all active:scale-95 kiosk-icon-btn"
                            >
                                <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9">
                                    <circle cx="12" cy="12" r="4" />
                                    <path d="M12 3v2m0 14v2M5.64 5.64l1.41 1.41m9.9 9.9l1.41 1.41M3 12h2m14 0h2M5.64 18.36l1.41-1.41m9.9-9.9l1.41-1.41" strokeLinecap="round" />
                                </svg>
                            </button>

                            {showBack && (
                                <Link
                                    href={homeHref}
                                    aria-label="Inicio"
                                    className="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center transition-all active:scale-95 kiosk-icon-btn"
                                >
                                    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                                        <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                                    </svg>
                                </Link>
                            )}

                            {student && (
                                <button
                                    type="button"
                                    onClick={handleLogout}
                                    aria-label="Salir"
                                    className="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center transition-all active:scale-95 kiosk-icon-btn"
                                >
                                    <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" strokeLinecap="round" strokeLinejoin="round" />
                                    </svg>
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Alertas Flash */}
                    {(flash?.success || flash?.error) && (
                        <div className="mb-4 w-full">
                            {flash.success && (
                                <div
                                    style={{ borderColor: accentColor }}
                                    className="p-3.5 rounded-xl border-2 kiosk-surface font-semibold text-sm flex items-center gap-2.5"
                                >
                                    <span
                                        style={{ backgroundColor: accentColor }}
                                        className="w-5 h-5 rounded-full text-white flex items-center justify-center text-xs shrink-0"
                                    >
                                        ✓
                                    </span>
                                    <span>{flash.success}</span>
                                </div>
                            )}
                            {flash.error && (
                                <div className="p-3.5 rounded-xl border border-red-200 dark:border-red-800/70 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 font-semibold text-sm flex items-center gap-2.5">
                                    <span>!</span>
                                    <span>{flash.error}</span>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Contenido Central */}
                    <main className="flex-1 w-full flex flex-col justify-center my-auto">
                        {children}
                        <ModalStandardizer />
                    </main>
                </div>
            </div>

            {/* Copyright Inferior Izquierdo (Idéntico a /login) */}
            <div className="fixed bottom-4 left-6 z-40 hidden xl:block pointer-events-none select-none">
                <p className="text-xs font-light drop-shadow-md text-white/70">
                    &copy; {new Date().getFullYear()} {appName}. Todos los derechos reservados.
                </p>
            </div>
        </div>
    );
}
