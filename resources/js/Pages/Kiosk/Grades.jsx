import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Grades({ student, grades = [], gpa = 88.5 }) {
    const [selectedGradeId, setSelectedGradeId] = useState(grades[0]?.id || null);

    return (
        <KioskLayout title="Récord de Calificaciones" student={student} backUrl="/kiosk/dashboard" showBack={true}>
            <Head title="Récord de Calificaciones - Kiosco" />

            <div className="max-w-5xl mx-auto w-full py-2 space-y-6">
                {/* Top Summary Cards (Daylight Kiosk Style) */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div className="p-6 rounded-3xl bg-[#F3F4F6] dark:bg-slate-800/80 flex items-center justify-between">
                        <div>
                            <div className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Índice General (GPA)
                            </div>
                            <div className="text-4xl font-black text-[#111827] dark:text-white mt-1">
                                {gpa} <span className="text-base font-semibold text-slate-400">/ 100</span>
                            </div>
                            <span className="inline-block mt-2 px-3 py-1 rounded-full bg-[#E6F2F0] dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-300 text-xs font-bold">
                                {gpa >= 85 ? 'Excelencia Académica' : 'Condición Regular'}
                            </span>
                        </div>
                        <div className="w-14 h-14 rounded-2xl bg-[#3B7A57] text-white flex items-center justify-center font-black text-xl">
                            A+
                        </div>
                    </div>

                    <div className="p-6 rounded-3xl bg-[#F3F4F6] dark:bg-slate-800/80 flex flex-col justify-between">
                        <div>
                            <div className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Asignaturas Evaluadas
                            </div>
                            <div className="text-4xl font-black text-[#111827] dark:text-white mt-1">
                                {grades.length}
                            </div>
                        </div>
                        <div className="text-xs font-medium text-slate-500 dark:text-slate-400 mt-2">
                            {student?.career || 'Programa Académico'}
                        </div>
                    </div>

                    <div className="p-6 rounded-3xl bg-[#F3F4F6] dark:bg-slate-800/80 flex flex-col justify-between">
                        <div>
                            <div className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Estudiante
                            </div>
                            <div className="text-lg font-bold text-[#111827] dark:text-white mt-1 truncate">
                                {student?.name || 'Estudiante Activo'}
                            </div>
                            <div className="text-xs font-mono font-bold text-[#3B7A57] dark:text-emerald-400">
                                Matrícula: {student?.student_code}
                            </div>
                        </div>

                        <button
                            type="button"
                            onClick={() => window.print()}
                            className="mt-4 w-full py-3.5 px-5 rounded-xl bg-[#3B7A57] hover:bg-[#2E6346] text-white font-bold text-sm flex items-center justify-between transition-all active:scale-95"
                        >
                            <span>Imprimir Récord</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>

                {/* Grades Selectable List (Reference Image 5 right screen style) */}
                <div className="rounded-3xl bg-[#F8FAFC] dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700 p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-bold text-[#111827] dark:text-white">
                            Historial de Calificaciones por Asignatura
                        </h2>
                        <span className="text-xs font-semibold text-slate-400">
                            Toca una materia para resaltar
                        </span>
                    </div>

                    {grades.length === 0 ? (
                        <div className="py-12 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <div className="text-lg font-bold text-[#111827] dark:text-white">
                                Aún no tienes calificaciones publicadas en este período
                            </div>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Las calificaciones aparecerán aquí tan pronto sean cerradas por el docente titular.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {grades.map((g) => {
                                const isSelected = selectedGradeId === g.id;
                                const passed = g.score >= 70;
                                return (
                                    <div
                                        key={g.id}
                                        onClick={() => setSelectedGradeId(g.id)}
                                        className={`p-5 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between gap-4 ${
                                            isSelected
                                                ? 'bg-white dark:bg-slate-900 border-[#3B7A57] shadow-xs'
                                                : 'bg-[#F3F4F6] dark:bg-slate-800/80 border-transparent hover:bg-[#EAECEF]'
                                        }`}
                                    >
                                        <div>
                                            <div
                                                className={`text-lg font-bold ${
                                                    isSelected
                                                        ? 'text-[#3B7A57] dark:text-emerald-400'
                                                        : 'text-[#111827] dark:text-white'
                                                }`}
                                            >
                                                {g.subject}
                                            </div>
                                            <div className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                {g.period}
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-4 shrink-0">
                                            <span
                                                className={`px-3 py-1 rounded-full text-xs font-bold ${
                                                    passed
                                                        ? 'bg-[#E6F2F0] text-[#166534] dark:bg-emerald-950/60 dark:text-emerald-300'
                                                        : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'
                                                }`}
                                            >
                                                {passed ? 'Aprobada' : 'Reprobada'}
                                            </span>
                                            <div className="text-right min-w-[56px]">
                                                <div className="text-2xl font-mono font-black text-[#111827] dark:text-white">
                                                    {g.score}
                                                </div>
                                                <div className="text-[10px] uppercase font-bold text-slate-400">
                                                    Puntos
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>
        </KioskLayout>
    );
}
