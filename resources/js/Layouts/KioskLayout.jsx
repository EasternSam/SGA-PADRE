import React, { useState, useEffect } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import ModalStandardizer from '../components/layout/ModalStandardizer';

export default function KioskLayout({
    children,
    title,
    student,
    backUrl = '/kiosk/dashboard',
    showBack = true,
    heroVariant = false,
}) {
    const { flash = {}, branding = {}, auth = {} } = usePage().props;
    const [currentTime, setCurrentTime] = useState('');
    const [currentDate, setCurrentDate] = useState('');
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
    const [lang, setLang] = useState('es');
    const [showLangModal, setShowLangModal] = useState(false);
    const [showHelpModal, setShowHelpModal] = useState(false);

    const institutionName = branding?.institution_name || 'ACADEMIC+';
    const logoUrl = branding?.logo_light_url || branding?.logo_url || null;
    const isLogged = Boolean(student || auth?.user);
    const homeHref = isLogged ? '/kiosk/dashboard' : '/kiosk/login';

    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            setCurrentTime(
                now.toLocaleTimeString('es-DO', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                })
            );
            setCurrentDate(
                now.toLocaleDateString('es-DO', {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric',
                })
            );
        };
        updateTime();
        const interval = setInterval(updateTime, 1000);
        return () => clearInterval(interval);
    }, []);

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

    const surfaceBg = darkMode
        ? 'bg-[#0F172A] text-slate-100'
        : 'bg-[#FFFFFF] text-[#111827]';

    const pillBg = darkMode
        ? 'bg-slate-800/90 hover:bg-slate-700 text-slate-100 border-slate-700'
        : 'bg-[#F3F4F6] hover:bg-[#E5E7EB] text-[#111827] border-transparent';

    return (
        <div
            className={`min-h-screen font-sans flex flex-col justify-between select-none transition-colors duration-200 ${surfaceBg} ${
                largeText ? 'text-[108%]' : 'text-base'
            }`}
        >
            {/* Top Minimalist Kiosk Utility Bar (Images 1, 2, 4, 5) */}
            <header className="relative z-20 px-6 md:px-10 pt-6 pb-3 flex items-center justify-between gap-4 max-w-7xl w-full mx-auto">
                {/* Left: Language Selector Pill + Institution Brand */}
                <div className="flex items-center gap-3 md:gap-4">
                    <button
                        type="button"
                        onClick={() => setShowLangModal(true)}
                        className={`h-12 px-4 rounded-2xl border font-semibold text-sm flex items-center gap-2.5 transition-all active:scale-95 ${pillBg}`}
                        title="Cambiar idioma"
                    >
                        <svg className="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M3.6 9h16.8M3.6 15h16.8M12 3a14.5 14.5 0 0 0 0 18M12 3a14.5 14.5 0 0 1 0 18" strokeLinecap="round" />
                        </svg>
                        <span>{lang === 'es' ? 'Español' : 'English'}</span>
                        <svg className="w-3.5 h-3.5 opacity-60" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clipRule="evenodd" />
                        </svg>
                    </button>

                    <Link
                        href={homeHref}
                        className="hidden sm:flex items-center gap-2.5 px-3 py-1.5 rounded-xl transition-opacity hover:opacity-80"
                    >
                        {logoUrl ? (
                            <img src={logoUrl} alt={institutionName} className="h-7 w-auto object-contain" />
                        ) : (
                            <div className="w-8 h-8 rounded-xl bg-[#166534] text-white flex items-center justify-center font-black text-sm shadow-sm">
                                A+
                            </div>
                        )}
                        <div className="flex items-baseline gap-1">
                            <span className={`font-black tracking-tight text-base ${darkMode ? 'text-white' : 'text-[#111827]'}`}>
                                {institutionName}
                            </span>
                            <span className={`text-xs font-semibold uppercase tracking-wider ${darkMode ? 'text-slate-400' : 'text-slate-400'}`}>
                                Kiosk
                            </span>
                        </div>
                    </Link>
                </div>

                {/* Right: Student Pill + Accessibility Controls (TT, Theme, Home, Help) */}
                <div className="flex items-center gap-2.5">
                    {student && (
                        <div
                            className={`hidden md:flex items-center gap-3 h-12 px-4 rounded-2xl border ${
                                darkMode ? 'bg-slate-800/80 border-slate-700' : 'bg-[#F3F4F6] border-transparent'
                            }`}
                        >
                            <div className="w-8 h-8 rounded-xl bg-[#166534] text-white flex items-center justify-center font-bold text-xs">
                                {(student.name || 'E').charAt(0).toUpperCase()}
                            </div>
                            <div className="leading-tight">
                                <div className={`text-xs font-bold truncate max-w-[150px] ${darkMode ? 'text-white' : 'text-[#111827]'}`}>
                                    {student.name}
                                </div>
                                <div className="text-[11px] font-mono text-[#166534] dark:text-emerald-400 font-semibold">
                                    {student.student_code}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* TT Font Size Toggle (Image 1 top-right) */}
                    <button
                        type="button"
                        onClick={toggleLargeText}
                        aria-label="Tamaño de texto"
                        title="Alternar tamaño de texto"
                        className={`w-12 h-12 rounded-2xl border flex items-center justify-center transition-all active:scale-95 ${
                            largeText
                                ? 'bg-[#166534] text-white border-[#166534]'
                                : pillBg
                        }`}
                    >
                        <span className="font-sans font-bold tracking-tighter text-base leading-none flex items-baseline">
                            T<span className="text-[11px] font-extrabold -ml-0.5">T</span>
                        </span>
                    </button>

                    {/* Sun / Moon Surface Toggle (Image 1 top-right) */}
                    <button
                        type="button"
                        onClick={toggleDarkMode}
                        aria-label="Alternar contraste de pantalla"
                        title="Alternar modo claro / oscuro"
                        className={`w-12 h-12 rounded-2xl border flex items-center justify-center transition-all active:scale-95 ${pillBg}`}
                    >
                        {darkMode ? (
                            <svg className="w-5 h-5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" strokeLinecap="round" />
                            </svg>
                        ) : (
                            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9">
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 3v2m0 14v2M5.64 5.64l1.41 1.41m9.9 9.9l1.41 1.41M3 12h2m14 0h2M5.64 18.36l1.41-1.41m9.9-9.9l1.41-1.41" strokeLinecap="round" />
                            </svg>
                        )}
                    </button>

                    {/* Home Icon Button (Images 4 & 5 top-right) */}
                    <Link
                        href={homeHref}
                        title="Inicio del Kiosco"
                        className={`w-12 h-12 rounded-2xl border flex items-center justify-center transition-all active:scale-95 ${pillBg}`}
                    >
                        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                            <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                        </svg>
                    </Link>

                    {student && (
                        <button
                            type="button"
                            onClick={handleLogout}
                            className="h-12 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-bold text-sm flex items-center gap-2 transition-all active:scale-95"
                        >
                            <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                            <span className="hidden sm:inline">{lang === 'es' ? 'Salir' : 'Exit'}</span>
                        </button>
                    )}
                </div>
            </header>

            {/* Sub-header Back Link (Images 2, 4, 5: "← Back" + section title) */}
            {showBack && (
                <div className="px-6 md:px-10 pt-2 pb-1 max-w-6xl w-full mx-auto flex items-center justify-between">
                    <Link
                        href={backUrl}
                        className={`inline-flex items-center gap-2.5 py-2 pr-4 rounded-xl font-bold text-base transition-all active:scale-95 ${
                            darkMode ? 'text-slate-200 hover:text-white' : 'text-[#111827] hover:text-[#166534]'
                        }`}
                    >
                        <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2">
                            <path d="M19 12H5M12 19l-7-7 7-7" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                        <span>{lang === 'es' ? 'Volver' : 'Back'}</span>
                    </Link>

                    {title && (
                        <span className={`text-sm font-semibold ${darkMode ? 'text-slate-400' : 'text-slate-500'}`}>
                            {title}
                        </span>
                    )}
                </div>
            )}

            {/* Flash Alerts */}
            {(flash?.success || flash?.error) && (
                <div className="px-6 md:px-10 pt-3 max-w-4xl w-full mx-auto z-20">
                    {flash.success && (
                        <div className="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 dark:bg-emerald-950/60 dark:border-emerald-800 dark:text-emerald-200 font-semibold text-base flex items-center gap-3 shadow-sm">
                            <span className="w-8 h-8 rounded-xl bg-[#166534] text-white flex items-center justify-center shrink-0">
                                ✓
                            </span>
                            <span>{flash.success}</span>
                        </div>
                    )}
                    {flash.error && (
                        <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 dark:bg-rose-950/60 dark:border-rose-800 dark:text-rose-200 font-semibold text-base flex items-center gap-3 shadow-sm">
                            <span className="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-bold">
                                !
                            </span>
                            <span>{flash.error}</span>
                        </div>
                    )}
                </div>
            )}

            {/* Main Interactive Touch Surface */}
            <main className="relative z-10 flex-1 overflow-y-auto px-6 md:px-10 py-4 md:py-6 flex flex-col justify-center max-w-6xl w-full mx-auto">
                {children}
                <ModalStandardizer />
            </main>

            {/* Minimalist Bottom Utility Bar (Image 2 "Need help?" pill + Live Clock + Staff Check-in) */}
            <footer className="relative z-10 px-6 md:px-10 py-4 max-w-7xl w-full mx-auto flex flex-wrap items-center justify-between gap-4 text-xs">
                <div className="flex items-center gap-3">
                    <span className="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-[#F3F4F6] dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold">
                        <span className="w-2 h-2 rounded-full bg-[#166534] animate-pulse" />
                        <span>{currentTime}</span>
                        <span className="opacity-40">•</span>
                        <span className="capitalize">{currentDate}</span>
                    </span>
                </div>

                <div className="flex items-center gap-3">
                    <Link
                        href="/kiosk/hr-punch"
                        className="px-4 py-2 rounded-full bg-[#F3F4F6] hover:bg-[#E5E7EB] dark:bg-slate-800 dark:hover:bg-slate-700 text-[#111827] dark:text-slate-200 font-semibold transition-all active:scale-95 flex items-center gap-2"
                    >
                        <span className="w-2 h-2 rounded-full bg-[#166534]" />
                        <span>Check-in / Check-out Personal</span>
                    </Link>

                    <button
                        type="button"
                        onClick={() => setShowHelpModal(true)}
                        className="px-4 py-2 rounded-full border border-slate-200 dark:border-slate-700 hover:bg-[#F3F4F6] dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold transition-all active:scale-95"
                    >
                        {lang === 'es' ? '¿Necesitas ayuda?' : 'Need help?'}
                    </button>
                </div>
            </footer>

            {/* Language Selector Modal (Directly inspired by Image 3 right screen "Change language") */}
            {showLangModal && (
                <div
                    className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-6"
                    onClick={() => setShowLangModal(false)}
                >
                    <div
                        className="bg-white dark:bg-slate-900 text-[#111827] dark:text-white rounded-3xl max-w-md w-full p-8 shadow-2xl relative"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <button
                            type="button"
                            onClick={() => setShowLangModal(false)}
                            className="absolute top-5 right-5 w-10 h-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-xl font-bold"
                        >
                            ×
                        </button>
                        <div className="text-center mb-6">
                            <h3 className="text-2xl font-bold">Cambiar idioma</h3>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                Selecciona el idioma de tu preferencia para la terminal táctil
                            </p>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            {[
                                { code: 'es', flag: '🇩🇴', label: 'Español' },
                                { code: 'en', flag: '🇺🇸', label: 'English' },
                            ].map((item) => (
                                <button
                                    key={item.code}
                                    type="button"
                                    onClick={() => {
                                        setLang(item.code);
                                        setShowLangModal(false);
                                    }}
                                    className={`p-4 rounded-2xl font-bold text-base flex items-center gap-3 transition-all active:scale-95 border-2 ${
                                        lang === item.code
                                            ? 'bg-[#1F2937] text-white border-[#1F2937]'
                                            : 'bg-[#F3F4F6] dark:bg-slate-800 text-[#111827] dark:text-white border-transparent hover:border-slate-300'
                                    }`}
                                >
                                    <span className="text-2xl">{item.flag}</span>
                                    <span>{item.label}</span>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            {/* Help Modal (Image 2 "Need help?" action) */}
            {showHelpModal && (
                <div
                    className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-6"
                    onClick={() => setShowHelpModal(false)}
                >
                    <div
                        className="bg-white dark:bg-slate-900 text-[#111827] dark:text-white rounded-3xl max-w-lg w-full p-8 shadow-2xl relative"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <button
                            type="button"
                            onClick={() => setShowHelpModal(false)}
                            className="absolute top-5 right-5 w-10 h-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-xl font-bold"
                        >
                            ×
                        </button>
                        <h3 className="text-2xl font-bold">Asistencia en el Kiosco</h3>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Sigue estos pasos rápidos para utilizar la terminal de autoservicio:
                        </p>
                        <ul className="mt-5 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                            <li className="flex items-start gap-3">
                                <span className="w-6 h-6 rounded-full bg-[#166534] text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">1</span>
                                <span><strong>Estudiantes:</strong> Ingresa tu número de Cédula o Matrícula y tu PIN de 4 dígitos para consultar tu estado de cuenta, horario o calificaciones.</span>
                            </li>
                            <li className="flex items-start gap-3">
                                <span className="w-6 h-6 rounded-full bg-[#166534] text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">2</span>
                                <span><strong>Autopago y QR:</strong> En la sección de Autopago puedes pagar con código QR instantáneo, tarjeta (NFC/Chip) o generar un turno para caja.</span>
                            </li>
                            <li className="flex items-start gap-3">
                                <span className="w-6 h-6 rounded-full bg-[#166534] text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">3</span>
                                <span><strong>Colaboradores y Docentes:</strong> Usa el botón <em>Check-in / Check-out</em> para registrar tu entrada o salida con tu PIN.</span>
                            </li>
                        </ul>
                        <button
                            type="button"
                            onClick={() => setShowHelpModal(false)}
                            className="mt-6 w-full py-3.5 rounded-2xl bg-[#166534] hover:bg-[#14532d] text-white font-bold text-base transition-all active:scale-95"
                        >
                            Entendido
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
