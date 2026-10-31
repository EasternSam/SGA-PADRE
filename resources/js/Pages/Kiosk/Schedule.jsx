import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Schedule({ student, schedules = [] }) {
    const [selectedDay, setSelectedDay] = useState('all');

    const days = [
        { key: 'all', label: 'Todos' },
        { key: 'lunes', label: 'Lunes' },
        { key: 'martes', label: 'Martes' },
        { key: 'miércoles', label: 'Miércoles' },
        { key: 'jueves', label: 'Jueves' },
        { key: 'viernes', label: 'Viernes' },
        { key: 'sábado', label: 'Sábado' },
    ];

    const filtered = schedules.filter(s => {
        if (selectedDay === 'all') return true;
        return (s.days || '').toLowerCase().includes(selectedDay);
    });

    return (
        <KioskLayout title="Mi Horario de Clases" student={student}>
            <Head title="Mi Horario - Kiosco" />

            <div className="max-w-5xl mx-auto w-full space-y-6">
                {/* Header Control */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl">
                    <div>
                        <h2 className="text-2xl font-bold text-white">Horario Académico Activo</h2>
                        <p className="text-slate-400 text-sm mt-1">Período Cuatrimestral Regular</p>
                    </div>

                    <button
                        onClick={() => window.print()}
                        className="px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 text-white font-semibold text-sm border border-white/15 transition-all flex items-center justify-center gap-2"
                    >
                        <i className="fa-solid fa-print text-blue-400"></i>
                        <span>Imprimir Horario</span>
                    </button>
                </div>

                {/* Day Selector Tabs */}
                <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                    {days.map(d => (
                        <button
                            key={d.key}
                            onClick={() => setSelectedDay(d.key)}
                            className={`px-5 py-3 rounded-2xl font-bold text-sm whitespace-nowrap transition-all border ${
                                selectedDay === d.key
                                    ? 'bg-blue-600 border-blue-500 text-white shadow-lg shadow-blue-600/30'
                                    : 'bg-slate-900/60 border-white/10 text-slate-400 hover:text-white hover:bg-white/5'
                            }`}
                        >
                            {d.label}
                        </button>
                    ))}
                </div>

                {/* Schedules Grid / List */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {filtered.length === 0 ? (
                        <div className="md:col-span-2 p-12 text-center rounded-3xl bg-slate-900/60 border border-white/10 text-slate-400">
                            <i className="fa-regular fa-calendar-xmark text-5xl text-slate-500 mb-3"></i>
                            <div className="text-lg font-semibold text-white">No hay clases programadas para este día</div>
                            <p className="text-sm mt-1">Selecciona otro día o consulta el horario completo.</p>
                        </div>
                    ) : (
                        filtered.map(s => (
                            <div
                                key={s.id}
                                className="p-6 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl relative overflow-hidden flex flex-col justify-between hover:border-blue-500/40 transition-all shadow-xl group"
                            >
                                <div className="space-y-3">
                                    <div className="flex items-start justify-between gap-4">
                                        <div>
                                            <span className="px-3 py-1 rounded-full text-xs font-mono font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                                {s.subject_code}
                                            </span>
                                            <h3 className="text-xl font-bold text-white mt-2 group-hover:text-blue-300 transition-colors">
                                                {s.subject_name}
                                            </h3>
                                        </div>
                                        <div className="text-right shrink-0">
                                            <div className="text-sm font-bold text-blue-400 font-mono">{s.time}</div>
                                            <div className="text-xs text-slate-400 mt-0.5">{s.days}</div>
                                        </div>
                                    </div>

                                    <div className="pt-3 border-t border-white/5 space-y-1.5 text-xs text-slate-300">
                                        <div className="flex items-center gap-2">
                                            <i className="fa-solid fa-chalkboard-user text-blue-400 w-4"></i>
                                            <span>Docente: <strong>{s.teacher}</strong></span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <i className="fa-solid fa-door-open text-emerald-400 w-4"></i>
                                            <span>Aula: <strong>{s.classroom}</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </KioskLayout>
    );
}
