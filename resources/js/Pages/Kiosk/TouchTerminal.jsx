import React from 'react';
import { Head, Link } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function TouchTerminal({ student, currentTime, currentDate }) {
    const modules = [
        {
            title: 'Estado de Cuenta',
            desc: 'Ver balances pendientes, pagos realizados y comprobantes fiscales',
            icon: 'fa-solid fa-file-invoice-dollar',
            href: '/kiosk/finances',
            color: 'from-emerald-500/20 via-emerald-600/10 to-transparent',
            borderColor: 'border-emerald-500/40 hover:border-emerald-400',
            iconColor: 'text-emerald-400',
            glowColor: 'group-hover:shadow-[0_0_50px_rgba(16,185,129,0.25)]',
            badge: 'Finanzas',
            badgeColor: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
        },
        {
            title: 'Mi Horario',
            desc: 'Consulta de aulas, docentes asignados, horarios de clases y laboratorios',
            icon: 'fa-regular fa-calendar-days',
            href: '/kiosk/schedule',
            color: 'from-indigo-500/20 via-indigo-600/10 to-transparent',
            borderColor: 'border-indigo-500/40 hover:border-indigo-400',
            iconColor: 'text-indigo-400',
            glowColor: 'group-hover:shadow-[0_0_50px_rgba(99,102,241,0.25)]',
            badge: 'Clases',
            badgeColor: 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30'
        },
        {
            title: 'Récord de Calificaciones',
            desc: 'Visualiza tus notas acumuladas, índice académico (GPA) y progreso de materias',
            icon: 'fa-solid fa-chart-line',
            href: '/kiosk/grades',
            color: 'from-amber-500/20 via-amber-600/10 to-transparent',
            borderColor: 'border-amber-500/40 hover:border-amber-400',
            iconColor: 'text-amber-400',
            glowColor: 'group-hover:shadow-[0_0_50px_rgba(245,158,11,0.25)]',
            badge: 'Académico',
            badgeColor: 'bg-amber-500/20 text-amber-300 border-amber-500/30'
        },
        {
            title: 'Oferta Académica',
            desc: 'Explora carreras técnicas, diplomados y cursos disponibles para inscripción',
            icon: 'fa-solid fa-graduation-cap',
            href: '/kiosk/academic-offer',
            color: 'from-purple-500/20 via-purple-600/10 to-transparent',
            borderColor: 'border-purple-500/40 hover:border-purple-400',
            iconColor: 'text-purple-400',
            glowColor: 'group-hover:shadow-[0_0_50px_rgba(168,85,247,0.25)]',
            badge: 'Catálogo',
            badgeColor: 'bg-purple-500/20 text-purple-300 border-purple-500/30'
        }
    ];

    return (
        <KioskLayout title="Menú de Autoservicio" student={student} showBack={false}>
            <Head title="Terminal de Autoservicio" />

            <div className="max-w-6xl mx-auto w-full flex flex-col justify-center py-6">
                {/* Greeting */}
                <div className="text-center mb-10">
                    <span className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/15 border border-blue-500/30 text-blue-300 text-sm font-semibold tracking-wide uppercase mb-3">
                        <i className="fa-solid fa-fingerprint"></i> Terminal Táctil Universitario
                    </span>
                    <h1 className="text-3xl md:text-5xl font-black text-white tracking-tight">
                        ¿Qué deseas realizar hoy?
                    </h1>
                    <p className="text-slate-400 text-base md:text-lg mt-2 max-w-xl mx-auto">
                        Toca cualquiera de las opciones para acceder a tus servicios académicos y financieros.
                    </p>
                </div>

                {/* Touch Module Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    {modules.map((m, idx) => (
                        <Link
                            key={idx}
                            href={m.href}
                            className={`group relative overflow-hidden rounded-3xl bg-slate-900/60 backdrop-blur-xl border ${m.borderColor} p-8 flex items-start gap-6 transition-all duration-300 hover:-translate-y-2 active:scale-95 ${m.glowColor}`}
                        >
                            <div className={`absolute inset-0 bg-gradient-to-br ${m.color} opacity-40 group-hover:opacity-80 transition-opacity`}></div>
                            <div className="w-20 h-20 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center shrink-0 shadow-xl group-hover:scale-110 transition-transform">
                                <i className={`${m.icon} text-4xl ${m.iconColor}`}></i>
                            </div>
                            <div className="relative z-10 flex-1">
                                <div className="flex items-center justify-between mb-2">
                                    <span className={`px-3 py-1 rounded-full text-xs font-bold border ${m.badgeColor}`}>
                                        {m.badge}
                                    </span>
                                    <i className="fa-solid fa-arrow-right text-slate-500 group-hover:text-white group-hover:translate-x-1 transition-all"></i>
                                </div>
                                <h3 className="text-2xl font-bold text-white group-hover:text-blue-300 transition-colors">
                                    {m.title}
                                </h3>
                                <p className="text-slate-400 text-sm mt-2 leading-relaxed">
                                    {m.desc}
                                </p>
                            </div>
                        </Link>
                    ))}
                </div>

                {/* Bottom Quick Action: HR Punch */}
                <div className="mt-10 flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg">
                            <i className="fa-solid fa-id-card-clip"></i>
                        </div>
                        <div>
                            <div className="font-semibold text-white text-sm">¿Eres Colaborador o Docente?</div>
                            <div className="text-xs text-slate-400">Puedes registrar tu entrada o salida sin iniciar sesión.</div>
                        </div>
                    </div>
                    <Link
                        href="/kiosk/hr-punch"
                        className="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm transition-all shadow-lg shadow-blue-600/30 flex items-center gap-2"
                    >
                        <i className="fa-solid fa-clock"></i>
                        <span>Ir al Marcaje de Asistencia</span>
                    </Link>
                </div>
            </div>
        </KioskLayout>
    );
}
