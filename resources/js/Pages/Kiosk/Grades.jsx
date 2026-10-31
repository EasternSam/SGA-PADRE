import React from 'react';
import { Head } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

export default function Grades({ student, grades = [], gpa = 85 }) {
    const getStatusColor = (score) => {
        if (score >= 90) return 'text-emerald-400 bg-emerald-500/20 border-emerald-500/30';
        if (score >= 80) return 'text-blue-400 bg-blue-500/20 border-blue-500/30';
        if (score >= 70) return 'text-amber-400 bg-amber-500/20 border-amber-500/30';
        return 'text-rose-400 bg-rose-500/20 border-rose-500/30';
    };

    return (
        <KioskLayout title="Récord de Calificaciones" student={student}>
            <Head title="Récord de Calificaciones - Kiosco" />

            <div className="max-w-5xl mx-auto w-full space-y-6">
                {/* GPA Banner */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl flex items-center justify-between">
                        <div>
                            <div className="text-xs uppercase font-bold text-slate-400 tracking-wider">Índice General (GPA)</div>
                            <div className="text-5xl font-black text-white mt-2 tracking-tight">{gpa} <span className="text-lg text-slate-400">/ 100</span></div>
                            <div className="mt-3 flex items-center gap-2">
                                <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold border ${getStatusColor(gpa)}`}>
                                    {gpa >= 85 ? 'Condición de Honor' : 'Condición Regular'}
                                </span>
                            </div>
                        </div>
                        <div className="w-20 h-20 rounded-full bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400 text-3xl">
                            <i className="fa-solid fa-award"></i>
                        </div>
                    </div>

                    <div className="p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl">
                        <div className="text-xs uppercase font-bold text-slate-400 tracking-wider">Asignaturas Cursadas</div>
                        <div className="text-5xl font-black text-white mt-2">{grades.length}</div>
                        <div className="text-xs text-slate-400 mt-3">Evaluaciones registradas formalmente</div>
                    </div>

                    <div className="p-8 rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl flex flex-col justify-between">
                        <div>
                            <div className="text-xs uppercase font-bold text-slate-400 tracking-wider">Certificación</div>
                            <div className="text-sm font-semibold text-white mt-1">Válido para fines informativos</div>
                        </div>
                        <button
                            onClick={() => window.print()}
                            className="mt-4 w-full py-3 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 text-white font-semibold text-sm border border-white/15 transition-all flex items-center justify-center gap-2"
                        >
                            <i className="fa-solid fa-print text-blue-400"></i>
                            <span>Imprimir Récord</span>
                        </button>
                    </div>
                </div>

                {/* Grades Table */}
                <div className="rounded-3xl bg-slate-900/60 border border-white/10 backdrop-blur-xl overflow-hidden shadow-2xl">
                    <div className="p-6 border-b border-white/10 flex items-center justify-between">
                        <h3 className="text-lg font-bold text-white">Detalle de Asignaturas & Calificaciones</h3>
                        <span className="text-xs text-slate-400 font-mono">Período Actual</span>
                    </div>

                    {grades.length === 0 ? (
                        <div className="p-12 text-center text-slate-400">
                            <i className="fa-solid fa-file-circle-question text-5xl text-slate-500 mb-3"></i>
                            <div className="text-lg font-semibold text-white">No hay calificaciones publicadas aún</div>
                            <p className="text-sm mt-1">Los docentes están evaluando los períodos correspondientes.</p>
                        </div>
                    ) : (
                        <div className="divide-y divide-white/5">
                            {grades.map(g => (
                                <div key={g.id} className="p-6 flex items-center justify-between gap-4 hover:bg-white/[0.02] transition-colors">
                                    <div className="space-y-1">
                                        <div className="text-lg font-bold text-white">{g.subject}</div>
                                        <div className="text-xs text-slate-400">{g.period}</div>
                                    </div>
                                    <div className="flex items-center gap-6">
                                        <span className={`px-3 py-1 rounded-xl text-xs font-bold border capitalize ${
                                            g.performance_level === 'logro_evidenciado' || g.score >= 70
                                                ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
                                                : 'bg-rose-500/20 text-rose-300 border-rose-500/30'
                                        }`}>
                                            {g.score >= 70 ? 'Aprobada' : 'Reprobada'}
                                        </span>
                                        <div className="text-right">
                                            <div className="text-2xl font-mono font-black text-white">{g.score}</div>
                                            <div className="text-[10px] text-slate-400 uppercase">Puntos</div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </KioskLayout>
    );
}
