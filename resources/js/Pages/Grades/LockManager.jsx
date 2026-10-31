import React, { useState } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Lock,
    Unlock,
    ShieldAlert,
    ShieldCheck,
    Calendar,
    Clock,
    AlertTriangle,
    ArrowLeft,
    CheckCircle2,
    Save,
    Info,
    CalendarDays,
    Building2,
    Users
} from 'lucide-react';

export default function LockManager({ auth, activeYear, periods = [] }) {
    const [lockDates, setLockDates] = useState(() => {
        const initial = {};
        periods.forEach(p => {
            initial[p.id] = {
                lock_date: p.lock_date || '',
                lock_reason: p.lock_reason || '',
            };
        });
        return initial;
    });

    const [savingId, setSavingId] = useState(null);

    const handleDateChange = (periodId, field, value) => {
        setLockDates(prev => ({
            ...prev,
            [periodId]: {
                ...prev[periodId],
                [field]: value
            }
        }));
    };

    const handleToggleLock = (period) => {
        const action = period.is_locked ? 'desbloquear' : 'bloquear';
        if (!confirm(`¿Está seguro de que desea ${action} el período ${period.name}?`)) {
            return;
        }

        router.post(route('admin.grades.locks.toggle', period.id), {}, {
            preserveScroll: true
        });
    };

    const handleSaveConfig = (periodId) => {
        const config = lockDates[periodId];
        setSavingId(periodId);

        router.post(route('admin.grades.locks.save', periodId), {
            lock_date: config.lock_date,
            lock_reason: config.lock_reason,
        }, {
            preserveScroll: true,
            onFinish: () => setSavingId(null)
        });
    };

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Cierre de Actas & Bloqueo de Calificaciones" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="inline-flex items-center gap-1.5 rounded-md bg-brand-primary/10 px-2.5 py-1 text-xs font-semibold text-brand-primary dark:bg-blue-900/30 dark:text-blue-300">
                                <ShieldAlert className="h-3.5 w-3.5" />
                                Seguridad & Auditoría Académica
                            </span>
                            {activeYear && (
                                <span className="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <Calendar className="h-3 w-3" />
                                    Año {activeYear.name}
                                </span>
                            )}
                        </div>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Cierre Oficial de Actas & Bloqueo de Períodos
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2.5">
                        <Link
                            href={route('admin.grades.matrix')}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            <ArrowLeft className="h-3.5 w-3.5" />
                            Volver a Sábana de Notas
                        </Link>
                    </div>
                </div>

                {/* Periods Grid */}
                <div className="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
                    {periods.map(period => {
                        const isLocked = period.is_effective_locked;
                        const config = lockDates[period.id] || {};

                        return (
                            <div
                                key={period.id}
                                className={`rounded-xl border shadow-sm transition-all duration-200 flex flex-col justify-between ${
                                    isLocked
                                        ? 'border-rose-200 bg-rose-50/40 dark:border-rose-900/40 dark:bg-rose-950/20'
                                        : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900'
                                }`}
                            >
                                <div className="p-5">
                                    {/* Top status indicator */}
                                    <div className="flex items-start justify-between gap-2 mb-3">
                                        <div>
                                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                                Período {period.number}
                                            </span>
                                            <h3 className="text-lg font-black text-slate-900 dark:text-white">
                                                {period.name}
                                            </h3>
                                        </div>
                                        {isLocked ? (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                                <Lock className="h-3 w-3" />
                                                Bloqueado
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                                <Unlock className="h-3 w-3" />
                                                Abierto
                                            </span>
                                        )}
                                    </div>

                                    {/* Schedule info */}
                                    <div className="space-y-1.5 text-xs text-slate-500 dark:text-slate-400 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                                        <div className="flex items-center justify-between">
                                            <span>Inicio:</span>
                                            <span className="font-semibold text-slate-700 dark:text-slate-300">{period.start_date || '—'}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span>Finalización:</span>
                                            <span className="font-semibold text-slate-700 dark:text-slate-300">{period.end_date || '—'}</span>
                                        </div>
                                        {period.locked_by && (
                                            <div className="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                                                <span>Modificado por:</span>
                                                <span className="font-medium">{period.locked_by}</span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Form: Lock Date & Reason */}
                                    <div className="space-y-3">
                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Fecha Límite Automática
                                            </label>
                                            <div className="relative">
                                                <input
                                                    type="date"
                                                    value={config.lock_date || ''}
                                                    onChange={(e) => handleDateChange(period.id, 'lock_date', e.target.value)}
                                                    className="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                                />
                                            </div>
                                            <p className="mt-1 text-[10px] text-slate-400">
                                                Las notas se cerrarán a las 23:59 de esta fecha.
                                            </p>
                                        </div>

                                        <div>
                                            <label className="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                Motivo / Nota de Cierre
                                            </label>
                                            <input
                                                type="text"
                                                placeholder="Ej. Cierre de trimestre oficial"
                                                value={config.lock_reason || ''}
                                                onChange={(e) => handleDateChange(period.id, 'lock_reason', e.target.value)}
                                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Bottom action buttons */}
                                <div className="border-t border-slate-100 bg-slate-50/50 p-4 flex flex-col gap-2 dark:border-slate-800 dark:bg-slate-900/60">
                                    <button
                                        type="button"
                                        onClick={() => handleSaveConfig(period.id)}
                                        disabled={savingId === period.id}
                                        className="w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition"
                                    >
                                        <Save className="h-3.5 w-3.5 text-slate-500" />
                                        {savingId === period.id ? 'Guardando...' : 'Guardar Fecha'}
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => handleToggleLock(period)}
                                        className={`w-full inline-flex items-center justify-center gap-1.5 rounded-lg py-2 text-xs font-bold text-white shadow-sm transition active:scale-95 ${
                                            period.is_locked
                                                ? 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500'
                                                : 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500'
                                        }`}
                                    >
                                        {period.is_locked ? (
                                            <>
                                                <Unlock className="h-3.5 w-3.5" />
                                                Desbloquear Manualmente
                                            </>
                                        ) : (
                                            <>
                                                <Lock className="h-3.5 w-3.5" />
                                                Bloquear Inmediatamente
                                            </>
                                        )}
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Explanatory Policy Card */}
                <div className="rounded-xl border border-blue-200 bg-blue-50/60 p-6 dark:border-blue-900/50 dark:bg-blue-950/20">
                    <div className="flex items-start gap-4">
                        <div className="rounded-lg bg-blue-600 p-2 text-white">
                            <Info className="h-6 w-6" />
                        </div>
                        <div>
                            <h4 className="text-base font-bold text-blue-950 dark:text-blue-200">
                                Protocolo Institucional de Cierre de Actas de Evaluación
                            </h4>
                            <p className="mt-1 text-sm text-blue-800/80 dark:text-blue-300/80">
                                El sistema de control escolar cumple con los estándares del Ministerio de Educación (MINERD) de la República Dominicana:
                            </p>
                            <ul className="mt-3 grid grid-cols-1 gap-2 text-xs text-blue-900 dark:text-blue-300 md:grid-cols-2">
                                <li className="flex items-center gap-2">
                                    <CheckCircle2 className="h-4 w-4 text-blue-600 shrink-0 dark:text-blue-400" />
                                    <span><strong>Cierre Automático:</strong> Las actas se bloquean sin intervención humana al cumplirse la fecha límite.</span>
                                </li>
                                <li className="flex items-center gap-2">
                                    <CheckCircle2 className="h-4 w-4 text-blue-600 shrink-0 dark:text-blue-400" />
                                    <span><strong>Bloqueo Docente:</strong> Los profesores no pueden alterar calificaciones en períodos cerrados sin previa solicitud.</span>
                                </li>
                                <li className="flex items-center gap-2">
                                    <CheckCircle2 className="h-4 w-4 text-blue-600 shrink-0 dark:text-blue-400" />
                                    <span><strong>Rectificaciones Administrativas:</strong> Los administradores y directores conservan privilegios para desbloquear o rectificar.</span>
                                </li>
                                <li className="flex items-center gap-2">
                                    <CheckCircle2 className="h-4 w-4 text-blue-600 shrink-0 dark:text-blue-400" />
                                    <span><strong>Trazabilidad:</strong> Cada modificación queda sellada en la bitácora con marca temporal y usuario responsable.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
