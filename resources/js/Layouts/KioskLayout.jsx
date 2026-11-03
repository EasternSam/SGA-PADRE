import React, { useState, useEffect } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import ModalStandardizer from '../components/layout/ModalStandardizer';

export default function KioskLayout({
    children,
    student,
    backUrl = '/kiosk/dashboard',
    showBack = true,
}) {
    const { flash = {}, branding = {}, auth = {} } = usePage().props;

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

    const institutionName = branding?.name || branding?.institution_name || 'Academic+';
    const logoUrl = branding?.logo || branding?.logo_url || '/branding/academic-logo.png';
    const primaryColor = branding?.primary_color || 'var(--brand-primary, #211170)';
    const isLogged = Boolean(student || auth?.user);
    const homeHref = isLogged ? '/kiosk/dashboard' : '/kiosk/login';

    // Synchronize document.documentElement.classList ('dark') so global dark-mode.css never conflicts with Kiosk mode
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

    const btnClass = darkMode
        ? 'bg-[#171717] hover:bg-[#262626] text-white border border-[#262626]'
        : 'bg-[#F3F4F6] hover:bg-[#E5E7EB] text-[#111827] border border-transparent';

    return (
        <div
            style={{
                '--kiosk-primary': primaryColor,
                backgroundColor: darkMode ? '#0A0A0A' : '#FFFFFF',
                color: darkMode ? '#FAFAFA' : '#111827',
            }}
            className={`min-h-screen w-full font-sans flex flex-col justify-between select-none transition-colors duration-200 ${
                largeText ? 'text-[108%]' : 'text-base'
            }`}
        >
            {/* Minimalist Top Bar (Using div role="banner" to avoid global header CSS overrides) */}
            <div
                role="banner"
                className="w-full max-w-6xl mx-auto px-5 sm:px-8 md:px-12 pt-5 sm:pt-7 pb-2 flex items-center justify-between gap-4 shrink-0"
            >
                {/* Left: Configuration Logo + Optional Back Button */}
                <div className="flex items-center gap-4 sm:gap-6">
                    <Link href={homeHref} className="flex items-center transition-opacity hover:opacity-85">
                        <img
                            src={logoUrl}
                            alt={institutionName}
                            style={{ height: `${Math.min(44, Math.max(28, branding?.sidebar_logo_height || 36))}px` }}
                            className="w-auto object-contain"
                        />
                    </Link>

                    {showBack && (
                        <Link
                            href={backUrl}
                            style={{ color: darkMode ? '#FAFAFA' : '#111827' }}
                            className="inline-flex items-center gap-2 text-sm sm:text-base font-semibold opacity-80 hover:opacity-100 transition-opacity active:scale-95"
                        >
                            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M19 12H5M12 19l-7-7 7-7" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            <span>Volver</span>
                        </Link>
                    )}
                </div>

                {/* Right: Minimalist Square Icon Controls (TT, Theme, Home, Exit) */}
                <div className="flex items-center gap-2 sm:gap-2.5">
                    <button
                        type="button"
                        onClick={toggleLargeText}
                        aria-label="Tamaño de texto"
                        style={largeText ? { backgroundColor: primaryColor, color: '#FFFFFF' } : undefined}
                        className={`w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center transition-all active:scale-95 ${
                            largeText ? '' : btnClass
                        }`}
                    >
                        <span className="font-sans font-bold tracking-tighter text-base leading-none flex items-baseline">
                            T<span className="text-[11px] font-extrabold -ml-0.5">T</span>
                        </span>
                    </button>

                    <button
                        type="button"
                        onClick={toggleDarkMode}
                        aria-label="Contraste"
                        className={`w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center transition-all active:scale-95 ${btnClass}`}
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
                            className={`w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center transition-all active:scale-95 ${btnClass}`}
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
                            className={`w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center transition-all active:scale-95 ${btnClass}`}
                        >
                            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </button>
                    )}
                </div>
            </div>

            {/* Flash Alerts (Minimalist) */}
            {(flash?.success || flash?.error) && (
                <div className="px-5 sm:px-8 md:px-12 pt-3 max-w-3xl w-full mx-auto">
                    {flash.success && (
                        <div
                            style={{ borderColor: primaryColor }}
                            className="p-4 rounded-2xl border-2 font-semibold text-sm sm:text-base flex items-center gap-3"
                        >
                            <span>✓</span>
                            <span>{flash.success}</span>
                        </div>
                    )}
                    {flash.error && (
                        <div className="p-4 rounded-2xl border-2 border-rose-500 text-rose-600 font-semibold text-sm sm:text-base flex items-center gap-3">
                            <span>!</span>
                            <span>{flash.error}</span>
                        </div>
                    )}
                </div>
            )}

            {/* Main Responsive Surface */}
            <main className="flex-1 w-full max-w-5xl mx-auto px-5 sm:px-8 md:px-12 py-6 sm:py-10 flex flex-col justify-center">
                {children}
                <ModalStandardizer />
            </main>
        </div>
    );
}
