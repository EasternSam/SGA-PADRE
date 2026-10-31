import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Award,
    ArrowLeft,
    CheckCircle2,
    XCircle,
    Lock,
    Save,
    RefreshCw,
    Download,
    Users
} from 'lucide-react';

export default function GradeBook({
    auth,
    sections = [],
    selectedSection = null,
    enrollments = [],
    isLocked = false
}) {
    const [gradesData, setGradesData] = useState([]);
    const [dirty, setDirty] = useState(false);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setGradesData(enrollments.map(e => ({
            enrollment_id: e.enrollment_id,
            student_id: e.student_id,
            student_code: e.student_code,
            student_name: e.student_name,
            final_grade: e.final_grade ?? '',
            dirty: false,
        })));
        setDirty(false);
    }, [enrollments]);

    const handleSectionChange = (newId) => {
        if (dirty && !confirm('Tiene notas sin guardar. ¿Desea descartar y cambiar de sección?')) return;
        router.get(route('teacher.grades', newId), {}, { preserveScroll: true });
    };

    const handleGradeChange = (index, value) => {
        if (isLocked) return;
        const updated = [...gradesData];
        if (value === '' || (!isNaN(value) && parseFloat(value) >= 0 && parseFloat(value) <= 100)) {
            updated[index].final_grade = value;
            updated[index].dirty = true;
            setGradesData(updated);
            setDirty(true);
        }
    };

    const handleSave = (e) => {
        if (e) e.preventDefault();
        if (isLocked) return;

        setSaving(true);
        router.post(route('teacher.grades.save', selectedSection.id), {
            grades: gradesData.map(g => ({
                enrollment_id: g.enrollment_id,
                final_grade: g.final_grade === '' ? null : parseFloat(g.final_grade),
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

    const getPerformanceLevel = (val) => {
        if (val === '' || val === null || isNaN(val)) return null;
        const num = parseFloat(val);
        if (num >= 89) return { label: 'Destacado', badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' };
        if (num >= 77) return { label: 'Logro Evidenciado', badge: 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' };
        if (num >= 65) return { label: 'En Proceso', badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' };
        return { label: 'Insuficiente', badge: 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' };
    };

    return (
        <AuthenticatedLayout auth={auth} title="Libro de Calificaciones">
            <Head title="Libro de Calificaciones | Docente" />

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
                            Libro de Calificaciones: {selectedSection?.subject_name}
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-3">
                        <select
                            value={selectedSection?.id || ''}
                            onChange={(e) => handleSectionChange(e.target.value)}
                            className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            {sections.map(s => (
                                <option key={s.id} value={s.id}>{s.name}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Locked Banner */}
                {isLocked && (
                    <div className="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                        <Lock className="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
                        <div className="text-sm">
                            <span className="font-bold">Acta Sellada Oficialmente:</span> Las calificaciones de esta sección han sido cerradas. Cualquier cambio debe solicitarse formalmente a la Dirección Académica.
                        </div>
                    </div>
                )}

                {/* Spreadsheet Table */}
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="border-b border-slate-100 bg-slate-50/50 px-6 py-3 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900/60">
                        <span className="text-xs font-bold uppercase tracking-wider text-slate-500">
                            {gradesData.length} Estudiantes Matriculados
                        </span>
                        <span className="text-xs text-slate-400">
                            Escala MINERD: 0-100 pts &bull; Aprobación &ge; 70
                        </span>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                <tr>
                                    <th className="px-5 py-3 text-center w-12">#</th>
                                    <th className="px-5 py-3 w-32">Matrícula</th>
                                    <th className="px-5 py-3">Estudiante</th>
                                    <th className="px-5 py-3 text-center w-36">Nota Final (0-100)</th>
                                    <th className="px-5 py-3 text-center w-40">Nivel MINERD</th>
                                    <th className="px-5 py-3 text-center w-28">Estado</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                {gradesData.map((g, idx) => {
                                    const perf = getPerformanceLevel(g.final_grade);
                                    const num = g.final_grade !== '' ? parseFloat(g.final_grade) : null;
                                    const isPassing = num !== null && num >= 70;

                                    return (
                                        <tr key={g.enrollment_id} className={`hover:bg-slate-50/80 dark:hover:bg-slate-800/50 ${
                                            g.dirty ? 'bg-blue-50/30 dark:bg-blue-950/20' : ''
                                        }`}>
                                            <td className="px-5 py-3 text-center text-xs font-semibold text-slate-400">
                                                {idx + 1}
                                            </td>
                                            <td className="px-5 py-3 font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                                                {g.student_code}
                                            </td>
                                            <td className="px-5 py-3 font-medium text-slate-900 dark:text-white">
                                                {g.student_name}
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.1"
                                                    disabled={isLocked}
                                                    placeholder="--"
                                                    value={g.final_grade}
                                                    onChange={(e) => handleGradeChange(idx, e.target.value)}
                                                    className="w-24 rounded-lg border border-slate-300 text-center text-sm font-bold shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                                />
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                {perf ? (
                                                    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${perf.badge}`}>
                                                        {perf.label}
                                                    </span>
                                                ) : (
                                                    <span className="text-xs text-slate-400">—</span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                {num !== null ? (
                                                    isPassing ? (
                                                        <span className="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                                            <CheckCircle2 className="h-3.5 w-3.5" />
                                                            Aprobado
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 text-xs font-bold text-rose-600">
                                                            <XCircle className="h-3.5 w-3.5" />
                                                            Reprobado
                                                        </span>
                                                    )
                                                ) : (
                                                    <span className="text-xs text-slate-400">Pendiente</span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                                {gradesData.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="py-12 text-center text-xs text-slate-400">
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
                            Calificaciones modificadas sin guardar
                        </span>
                        <button
                            type="button"
                            onClick={handleSave}
                            disabled={saving}
                            className="inline-flex items-center gap-1.5 rounded-lg bg-brand-primary px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-[#082260]"
                        >
                            {saving ? <RefreshCw className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
                            Guardar Calificaciones
                        </button>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
