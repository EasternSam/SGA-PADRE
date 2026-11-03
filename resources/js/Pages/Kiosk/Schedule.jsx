import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Schedule({ student, schedules = [] }) {
    const [selectedDay, setSelectedDay] = useState('all');
    const [selectedSubjectId, setSelectedSubjectId] = useState(schedules[0]?.id || null);

    const days = [
        { key: 'all', label: 'Todos los días' },
        { key: 'lunes', label: 'Lunes' },
        { key: 'martes', label: 'Martes' },
        { key: 'miércoles', label: 'Miércoles' },
        { key: 'jueves', label: 'Jueves' },
        { key: 'viernes', label: 'Viernes' },
        { key: 'sábado', label: 'Sábado' },
    ];

    const filtered = schedules.filter((s) => {
        if (selectedDay === 'all') return true;
        return (s.days || '').toLowerCase().includes(selectedDay);
    });

    const activeSubject = filtered.find((s) => s.id === selectedSubjectId) || filtered[0] || null;

    return (
        <KioskLayout title="Mi Horario y Aulas" student={student} backUrl="/kiosk/dashboard" showBack={true}>
            <Head title="Mi Horario y Aulas - Kiosco" />

            <div className="max-w-5xl mx-auto w-full py-2 space-y-6">
                {/* Clean Header + Day Filter Pills */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-[#111827] dark:text-white tracking-tight">
                            Selecciona una asignatura para ver su aula y horario
                        </h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {student?.career || 'Programa Académico Activo'} • {schedules.length} asignatura(s) inscrita(s)
                        </p>
                    </div>
                </div>

                {/* Day Filter Tabs */}
                <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
                    {days.map((d) => (
                        <button
                            key={d.key}
                            type="button"
                            onClick={() => setSelectedDay(d.key)}
                            className={`px-5 py-3 rounded-2xl font-bold text-sm whitespace-nowrap transition-all active:scale-95 border-2 ${
                                selectedDay === d.key
                                    ? 'bg-[#F3F4F6] dark:bg-slate-800 border-[#3B7A57] text-[#3B7A57] dark:text-emerald-400'
                                    : 'bg-[#F3F4F6] dark:bg-slate-800/60 border-transparent text-slate-600 dark:text-slate-300 hover:bg-[#E5E7EB]'
                            }`}
                        >
                            {d.label}
                        </button>
                    ))}
                </div>

                {/* Main Content: Selectable Subject List (Image 5 right screen style) + Classroom Detail Card */}
                {filtered.length === 0 ? (
                    <div className="p-12 rounded-3xl bg-[#F3F4F6] dark:bg-slate-800/80 text-center">
                        <div className="text-xl font-bold text-[#111827] dark:text-white">
                            No hay clases programadas para este filtro
                        </div>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Toca "Todos los días" para visualizar tu carga académica completa.
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        {/* Left: Selectable Stacked List (Inspired by Reference Image 5 right screen) */}
                        <div className="lg:col-span-7 space-y-3">
                            {filtered.map((s) => {
                                const isSelected = activeSubject?.id === s.id;
                                return (
                                    <button
                                        key={s.id}
                                        type="button"
                                        onClick={() => setSelectedSubjectId(s.id)}
                                        className={`w-full p-5 rounded-2xl border-2 text-left transition-all active:scale-[0.99] flex items-center justify-between gap-4 ${
                                            isSelected
                                                ? 'bg-[#F3F4F6] dark:bg-slate-800 border-[#3B7A57] text-[#3B7A57] dark:text-emerald-400 shadow-xs'
                                                : 'bg-[#F3F4F6] dark:bg-slate-800/70 border-transparent text-[#111827] dark:text-white hover:bg-[#EAECEF]'
                                        }`}
                                    >
                                        <div>
                                            <span className="text-xs font-mono font-bold uppercase opacity-75">
                                                {s.subject_code} • {s.days}
                                            </span>
                                            <div className="text-xl font-bold mt-0.5">{s.subject_name}</div>
                                        </div>
                                        <div className="text-right shrink-0">
                                            <div className="font-mono font-bold text-sm">{s.time}</div>
                                            <div className="text-xs opacity-75 mt-0.5">{s.classroom}</div>
                                        </div>
                                    </button>
                                );
                            })}
                        </div>

                        {/* Right: Selected Subject Detail & Print Action (Image 5 style) */}
                        <div className="lg:col-span-5 rounded-3xl bg-[#F8FAFC] dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 p-7 space-y-5 shadow-xs">
                            {activeSubject && (
                                <>
                                    <div>
                                        <span className="px-3 py-1 rounded-full bg-[#E6F2F0] dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-300 text-xs font-mono font-bold">
                                            {activeSubject.subject_code}
                                        </span>
                                        <h2 className="text-2xl font-bold text-[#111827] dark:text-white mt-3">
                                            {activeSubject.subject_name}
                                        </h2>
                                    </div>

                                    <div className="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-700 text-sm">
                                        <div className="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                            <span className="text-slate-500">Aula / Laboratorio</span>
                                            <span className="font-bold text-[#111827] dark:text-white">
                                                {activeSubject.classroom}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                            <span className="text-slate-500">Días de Clase</span>
                                            <span className="font-bold text-[#111827] dark:text-white">
                                                {activeSubject.days}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                            <span className="text-slate-500">Horario</span>
                                            <span className="font-mono font-bold text-[#3B7A57] dark:text-emerald-400">
                                                {activeSubject.time}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between py-2">
                                            <span className="text-slate-500">Docente Asignado</span>
                                            <span className="font-bold text-[#111827] dark:text-white">
                                                {activeSubject.teacher}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="pt-3 flex justify-end">
                                        <button
                                            type="button"
                                            onClick={() => window.print()}
                                            className="w-full py-4 px-6 rounded-xl bg-[#3B7A57] hover:bg-[#2E6346] text-white font-bold text-base flex items-center justify-between transition-all active:scale-95 shadow-sm"
                                        >
                                            <span>Imprimir Mi Horario</span>
                                            <svg className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z" strokeLinecap="round" strokeLinejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </KioskLayout>
    );
}
