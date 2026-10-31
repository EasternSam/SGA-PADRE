import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    GraduationCap,
    ArrowLeft,
    CheckCircle2,
    Clock,
    Lock,
    Award,
    Sparkles,
    BookOpen,
    TrendingUp,
    AlertCircle,
    Calendar
} from 'lucide-react';

export default function AcademicProjection({
    auth,
    student,
    career,
    semesters = [],
    progress = {}
}) {
    return (
        <AuthenticatedLayout auth={auth} title="Pensum y Avance Académico">
            <Head title="Proyección Académica & Pensum" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            href={route('student.dashboard')}
                            className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Volver al Portal
                        </Link>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Proyección Académica & Malla Curricular
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                            <GraduationCap className="h-4 w-4 text-brand-primary dark:text-blue-400" />
                            {progress.percentage || 0}% Carrera Completada
                        </span>
                    </div>
                </div>

                {/* Overall Degree Progress Gauge */}
                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Avance Global de Titulación
                            </span>
                            <div className="mt-1 flex items-baseline gap-2">
                                <span className="text-3xl font-black text-slate-900 dark:text-white">
                                    {progress.earned_credits || 0}
                                </span>
                                <span className="text-sm font-semibold text-slate-400">
                                    / {progress.total_credits || 0} créditos aprobados
                                </span>
                            </div>
                        </div>

                        {/* Legend */}
                        <div className="flex flex-wrap items-center gap-3 text-xs">
                            <div className="flex items-center gap-1.5">
                                <span className="h-2.5 w-2.5 rounded-full bg-emerald-500" />
                                <span className="font-semibold text-slate-600 dark:text-slate-400">Aprobada</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <span className="h-2.5 w-2.5 rounded-full bg-blue-500" />
                                <span className="font-semibold text-slate-600 dark:text-slate-400">Cursando</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <span className="h-2.5 w-2.5 rounded-full bg-slate-300 dark:bg-slate-600" />
                                <span className="font-semibold text-slate-600 dark:text-slate-400">Pendiente</span>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 h-3 w-full rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                        <div
                            className="h-full bg-gradient-to-r from-brand-primary to-emerald-500 rounded-full transition-all duration-700"
                            style={{ width: `${progress.percentage || 0}%` }}
                        />
                    </div>
                </div>

                {/* Pensum Tree / Semesters Grid */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                    {semesters.map(sem => (
                        <div
                            key={sem.semester}
                            className="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/40 space-y-3"
                        >
                            <div className="flex items-center justify-between border-b border-slate-200 pb-2.5 dark:border-slate-800">
                                <h3 className="font-black text-sm text-slate-900 dark:text-white">
                                    {sem.name}
                                </h3>
                                <span className="text-[11px] font-semibold text-slate-500">
                                    {sem.modules.reduce((a, c) => a + (c.credits || 0), 0)} crs.
                                </span>
                            </div>

                            <div className="space-y-2.5">
                                {sem.modules.map(mod => {
                                    const isApproved = mod.status === 'Aprobada';
                                    const isCurrent = mod.status === 'Cursando';

                                    return (
                                        <div
                                            key={mod.id}
                                            className={`rounded-xl border p-3.5 shadow-sm transition ${
                                                isApproved
                                                    ? 'border-emerald-200 bg-white dark:border-emerald-950/60 dark:bg-slate-900'
                                                    : isCurrent
                                                    ? 'border-blue-300 bg-blue-50/40 dark:border-blue-900/60 dark:bg-blue-950/20'
                                                    : 'border-slate-200 bg-white/70 dark:border-slate-800 dark:bg-slate-900/60'
                                            }`}
                                        >
                                            <div className="flex items-start justify-between gap-1.5">
                                                <span className="font-mono text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                                    {mod.code}
                                                </span>
                                                {isApproved && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        <CheckCircle2 className="h-3 w-3" />
                                                        {mod.grade ? `${mod.grade} pts` : 'Aprobada'}
                                                    </span>
                                                )}
                                                {isCurrent && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-brand-primary dark:bg-blue-950/60 dark:text-blue-300">
                                                        <Clock className="h-3 w-3" />
                                                        Cursando
                                                    </span>
                                                )}
                                                {!isApproved && !isCurrent && (
                                                    <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                                        Pendiente
                                                    </span>
                                                )}
                                            </div>

                                            <h4 className="mt-1.5 text-xs font-bold text-slate-900 dark:text-white line-clamp-2">
                                                {mod.name}
                                            </h4>

                                            <div className="mt-2 flex items-center justify-between text-[11px] text-slate-400 border-t border-slate-100 pt-1.5 dark:border-slate-800/60">
                                                <span>{mod.credits} Créditos</span>
                                                <span className="truncate max-w-[120px]" title={mod.prerequisite}>
                                                    Prerreq: {mod.prerequisite}
                                                </span>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
