import React, { useState, useEffect } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import ModalStandardizer from '../components/layout/ModalStandardizer';

export default function KioskLayout({ children, title, student, backUrl = '/kiosk/dashboard', showBack = true }) {
    const { flash = {} } = usePage().props;
    const [currentTime, setCurrentTime] = useState('');
    const [currentDate, setCurrentDate] = useState('');

    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            setCurrentTime(now.toLocaleTimeString('es-DO', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }));
            setCurrentDate(now.toLocaleDateString('es-DO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
        };
        updateTime();
        const interval = setInterval(updateTime, 1000);
        return () => clearInterval(interval);
    }, []);

    const handleLogout = () => {
        router.post('/kiosk/logout');
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white font-sans flex flex-col justify-between overflow-hidden select-none">
            {/* Ambient Background Glows */}
            <div className="fixed inset-0 pointer-events-none z-0 overflow-hidden">
                <div className="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl"></div>
                <div className="absolute top-1/2 -right-32 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl"></div>
                <div className="absolute -bottom-32 left-1/3 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl"></div>
            </div>

            {/* Top Touch Header */}
            <header className="relative z-10 border-b border-white/10 bg-black/30 backdrop-blur-xl px-6 py-4 flex items-center justify-between shadow-2xl">
                <div className="flex items-center gap-4">
                    {showBack ? (
                        <Link
                            href={backUrl}
                            className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 text-white font-semibold text-lg border border-white/15 transition-all shadow-lg"
                        >
                            <i className="fa-solid fa-arrow-left text-xl text-blue-400"></i>
                            <span>Atrás</span>
                        </Link>
                    ) : (
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/30 border border-white/20">
                                <i className="fa-solid fa-graduation-cap text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 className="text-xl font-bold tracking-tight text-white leading-tight">SGA ITLA</h1>
                                <p className="text-xs text-blue-300 font-medium uppercase tracking-widest">Autoservicio Digital</p>
                            </div>
                        </div>
                    )}

                    {title && (
                        <div className="hidden md:block pl-6 border-l border-white/10">
                            <h2 className="text-xl font-bold text-white tracking-wide">{title}</h2>
                        </div>
                    )}
                </div>

                {/* Clock & Status */}
                <div className="flex items-center gap-6">
                    <div className="text-right hidden sm:block">
                        <div className="text-2xl font-mono font-bold tracking-wider text-blue-300">{currentTime}</div>
                        <div className="text-xs text-slate-400 capitalize">{currentDate}</div>
                    </div>

                    {student && (
                        <div className="flex items-center gap-3 bg-white/5 border border-white/10 px-4 py-2 rounded-2xl">
                            <div className="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-300 flex items-center justify-center font-bold text-lg border border-blue-500/30">
                                <i className="fa-solid fa-user-graduate"></i>
                            </div>
                            <div className="text-left">
                                <div className="text-sm font-bold text-white max-w-[180px] truncate">{student.name}</div>
                                <div className="text-xs text-blue-300 font-mono">{student.student_code}</div>
                            </div>
                        </div>
                    )}

                    {student && (
                        <button
                            onClick={handleLogout}
                            className="flex items-center gap-2 px-5 py-3 rounded-2xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 font-semibold text-base transition-all active:scale-95 shadow-lg"
                        >
                            <i className="fa-solid fa-arrow-right-from-bracket"></i>
                            <span className="hidden sm:inline">Salir</span>
                        </button>
                    )}
                </div>
            </header>

            {/* Flash notifications */}
            {(flash?.success || flash?.error) && (
                <div className="relative z-20 px-6 pt-4">
                    {flash.success && (
                        <div className="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-lg flex items-center gap-3 shadow-xl backdrop-blur-md">
                            <i className="fa-solid fa-circle-check text-2xl text-emerald-400"></i>
                            <span>{flash.success}</span>
                        </div>
                    )}
                    {flash.error && (
                        <div className="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-200 text-lg flex items-center gap-3 shadow-xl backdrop-blur-md">
                            <i className="fa-solid fa-triangle-exclamation text-2xl text-rose-400"></i>
                            <span>{flash.error}</span>
                        </div>
                    )}
                </div>
            )}

            {/* Main Interactive Screen */}
            <main className="relative z-10 flex-1 overflow-y-auto p-6 md:p-10 flex flex-col justify-center">
                {children}
            <ModalStandardizer />
            </main>

            {/* Bottom Touch Bar */}
            <footer className="relative z-10 border-t border-white/10 bg-black/40 backdrop-blur-xl px-6 py-3 flex items-center justify-between text-xs text-slate-400">
                <div className="flex items-center gap-2">
                    <span className="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span className="font-medium text-slate-300">Terminal de Autoservicio Activo</span>
                </div>
                <div className="flex items-center gap-4">
                    <Link href="/kiosk/hr-punch" className="hover:text-blue-300 transition-colors flex items-center gap-1.5 font-medium">
                        <i className="fa-solid fa-fingerprint text-blue-400"></i>
                        <span>Marcaje de Asistencia</span>
                    </Link>
                    <span className="text-white/20">•</span>
                    <span>SGA ITLA v2.4</span>
                </div>
            </footer>
        </div>
    );
}
