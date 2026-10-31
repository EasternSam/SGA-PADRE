import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Calendar,
    ArrowLeft,
    CheckCircle2,
    XCircle,
    Clock,
    AlertCircle,
    Save,
    RefreshCw,
    Users,
    Sparkles
} from 'lucide-react';

export default function RollCall({
    auth,
    sections = [],
    selectedSection = null,
    attendanceDate,
    enrollments = [],
    isLocked = false
}) {
    const [attendances, setAttendances] = useState([]);
    const [selectedDate, setSelectedDate] = useState(attendanceDate || new Date().toISOString().split('T')[0]);
    const [dirty, setDirty] = useState(false);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setAttendances(enrollments.map(e => ({
            enrollment_id: e.enrollment_id,
            student_id: e.student_id,
            student_code: e.student_code,
            student_name: e.student_name,
            status: e.status || 'Presente',
        })));
        setDirty(false);
    }, [enrollments]);

    const handleDateChange = (newDate) => {
        if (dirty && !confirm('Tiene cambios sin guardar. ¿Desea descartar y cambiar de fecha?')) return;
        router.get(route('teacher.attendance', selectedSection?.id), { date: newDate }, { preserveScroll: true });
    };

    const handleStatusChange = (index, status) => {
        if (isLocked) return;
        const updated = [...attendances];
        updated[index].status = status;
        setAttendances(updated);
        setDirty(true);
    };

    const handleMarkAll = (status) => {
        if (isLocked) return;
        const updated = attendances.map(a => ({ ...a, status }));
        setAttendances(updated);
        setDirty(true);
    };

    const handleSave = (e) => {
        if (e) e.preventDefault();
        if (isLocked) return;

        setSaving(true);
        router.post(route('teacher.attendance.save', selectedSection.id), {
            date: selectedDate,
            attendances: attendances.map(a => ({
                enrollment_id: a.enrollment_id,
                status: a.status,
            })),
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setDirty(false);
                setSaving(false);
            },
            onError: () => setSaving(false),
        });
    };

    return (
        <AuthenticatedLayout auth={auth} title="Pase de Lista Docente">
            <Head title="Pase de Lista | Docente" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            href={route('teacher.dashboard')}
                            className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Volver al Dashboard
                        </Link>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Pase de Lista: {selectedSection?.subject_name}
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <select
                            value={selectedSection?.id || ''}
                            onChange={(e) => router.get(route('teacher.attendance', e.target.value), { date: selectedDate })}
                            className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            {sections.map(s => (
                                <option key={s.id} value={s.id}>{s.name}</option>
                            ))}
                        </select>

                        <div className="flex items-center gap-2">
                            <input
                                type="date"
                                value={selectedDate}
                                onChange={(e) => {
                                    setSelectedDate(e.target.value);
                                    handleDateChange(e.target.value);
                                }}
                                className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>
                    </div>
                </div>

                {/* Quick Bulk Action Bar */}
                <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <span className="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Sparkles className="h-4 w-4 text-brand-primary" />
                        Acciones Masivas para esta Clase:
                    </span>
                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            disabled={isLocked}
                            onClick={() => handleMarkAll('Presente')}
                            className="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 disabled:opacity-50 dark:bg-emerald-950/40 dark:text-emerald-300"
                        >
                            Todos Presentes
                        </button>
                        <button
                            type="button"
                            disabled={isLocked}
                            onClick={() => handleMarkAll('Ausente')}
                            className="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 disabled:opacity-50 dark:bg-rose-950/40 dark:text-rose-300"
                        >
                            Todos Ausentes
                        </button>
                    </div>
                </div>

                {/* Attendance Roll Call Table */}
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                <tr>
                                    <th className="px-5 py-3 text-center w-12">#</th>
                                    <th className="px-5 py-3 w-32">Matrícula</th>
                                    <th className="px-5 py-3">Estudiante</th>
                                    <th className="px-5 py-3 text-center">Estado de Asistencia</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {attendances.map((a, idx) => (
                                    <tr key={a.enrollment_id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                        <td className="px-5 py-3 text-center text-xs font-semibold text-slate-400">
                                            {idx + 1}
                                        </td>
                                        <td className="px-5 py-3 font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                                            {a.student_code}
                                        </td>
                                        <td className="px-5 py-3 font-medium text-slate-900 dark:text-white">
                                            {a.student_name}
                                        </td>
                                        <td className="px-5 py-3 text-center">
                                            <div className="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800">
                                                {['Presente', 'Ausente', 'Tardanza', 'Excusa'].map((opt) => (
                                                    <button
                                                        key={opt}
                                                        type="button"
                                                        disabled={isLocked}
                                                        onClick={() => handleStatusChange(idx, opt)}
                                                        className={`rounded-md px-3 py-1 text-xs font-bold transition ${
                                                            a.status === opt
                                                                ? opt === 'Presente'
                                                                    ? 'bg-emerald-600 text-white shadow-sm'
                                                                    : opt === 'Ausente'
                                                                    ? 'bg-rose-600 text-white shadow-sm'
                                                                    : opt === 'Tardanza'
                                                                    ? 'bg-amber-500 text-white shadow-sm'
                                                                    : 'bg-blue-600 text-white shadow-sm'
                                                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                                        }`}
                                                    >
                                                        {opt}
                                                    </button>
                                                ))}
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {attendances.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="py-12 text-center text-xs text-slate-400">
                                            No hay estudiantes inscritos en esta sección.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Floating Save Button */}
                {dirty && !isLocked && (
                    <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 rounded-2xl border border-slate-300 bg-white/95 px-6 py-3.5 shadow-2xl backdrop-blur-md dark:border-slate-700 dark:bg-slate-900/95">
                        <span className="text-sm font-bold text-slate-900 dark:text-white">
                            Asistencias modificadas sin guardar
                        </span>
                        <button
                            type="button"
                            onClick={handleSave}
                            disabled={saving}
                            className="inline-flex items-center gap-1.5 rounded-lg bg-brand-primary px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-[#082260]"
                        >
                            {saving ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
                            Guardar Asistencia
                        </button>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
