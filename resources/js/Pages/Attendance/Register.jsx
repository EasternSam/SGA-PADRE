import { Check } from 'lucide-react';
import React, { useState, useEffect } from 'react';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AttendanceRegister({
    activeYear,
    sections = [],
    selectedSectionId,
    selectedDate,
    attendances = [],
    summary: initialSummary = {},
    hasExisting = false,
    statusLabels = {},
}) {
    const [localAttendances, setLocalAttendances] = useState(attendances);
    const [date, setDate] = useState(selectedDate);
    const [sectionId, setSectionId] = useState(selectedSectionId || '');

    useEffect(() => {
        setLocalAttendances(attendances);
    }, [attendances]);

    // Recalculate summary live
    const summary = {
        present: localAttendances.filter(a => a.status === 'present').length,
        absent: localAttendances.filter(a => a.status === 'absent').length,
        late: localAttendances.filter(a => a.status === 'late').length,
        excused: localAttendances.filter(a => a.status === 'excused').length,
        permission: localAttendances.filter(a => a.status === 'permission').length,
    };

    const handleFilterChange = (newSecId, newDate) => {
        setSectionId(newSecId);
        setDate(newDate);
        router.get('/admin/attendance/register', {
            section_id: newSecId,
            date: newDate,
        }, {
            preserveState: true,
        });
    };

    const handleStatusChange = (studentId, newStatus) => {
        setLocalAttendances(prev => prev.map(item => {
            if (item.student_id === studentId) {
                return { ...item, status: newStatus };
            }
            return item;
        }));
    };

    const markAllPresent = () => {
        setLocalAttendances(prev => prev.map(item => ({
            ...item,
            status: 'present',
        })));
    };

    const handleSave = () => {
        if (!sectionId) {
            alert('Selecciona una sección.');
            return;
        }

        router.post('/admin/attendance/register', {
            section_id: sectionId,
            date: date,
            attendances: localAttendances.map(a => ({
                student_id: a.student_id,
                status: a.status,
            })),
        }, {
            preserveScroll: true,
        });
    };

    const statusOptions = [
        { key: 'present', label: 'Presente', activeBg: 'bg-emerald-600 text-white', hoverBg: 'hover:bg-emerald-50 text-emerald-700' },
        { key: 'absent', label: 'Ausente', activeBg: 'bg-red-600 text-white', hoverBg: 'hover:bg-red-50 text-red-700' },
        { key: 'late', label: 'Tardanza', activeBg: 'bg-amber-500 text-white', hoverBg: 'hover:bg-amber-50 text-amber-700' },
        { key: 'excused', label: 'Excusa', activeBg: 'bg-blue-600 text-white', hoverBg: 'hover:bg-blue-50 text-blue-700' },
        { key: 'permission', label: 'Permiso', activeBg: 'bg-purple-600 text-white', hoverBg: 'hover:bg-purple-50 text-purple-700' },
    ];

    return (
        <AuthenticatedLayout>
            <Head title="Pase de Lista & Asistencia Escolar - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </span>
                            Pase de Lista & Asistencia Escolar
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/attendance/justifications"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Ver Justificaciones
                        </Link>
                    </div>
                </div>

                {/* Filters and Controls Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col md:flex-row items-end justify-between gap-4">
                    <div className="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <div className="w-full sm:w-72">
                            <label className="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                                Sección Escolar *
                            </label>
                            <select
                                value={sectionId}
                                onChange={e => handleFilterChange(e.target.value, date)}
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white font-semibold focus:ring-2 focus:ring-brand-primary"
                            >
                                <option value="">Seleccione una sección...</option>
                                {sections.map(s => (
                                    <option key={s.id} value={s.id}>
                                        {s.name} ({s.grade_level})
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="w-full sm:w-48">
                            <label className="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                                Fecha de Lista *
                            </label>
                            <input
                                type="date"
                                value={date}
                                onChange={e => handleFilterChange(sectionId, e.target.value)}
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                            />
                        </div>
                    </div>

                    <div className="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            onClick={markAllPresent}
                            className="rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                        >
                            <Check className="w-4 h-4 mr-1.5 inline" /> Marcar Todos Presentes
                        </button>
                        <button
                            type="button"
                            onClick={handleSave}
                            className="rounded-xl bg-brand-primary hover:bg-brand-dark px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            {hasExisting ? 'Actualizar Asistencia' : 'Guardar Asistencia'}
                        </button>
                    </div>
                </div>

                {/* Counters Bar */}
                <div className="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div className="bg-white dark:bg-[#161622] rounded-xl p-3 border border-emerald-100 dark:border-emerald-950/40 text-center">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Presentes</span>
                        <span className="text-xl font-bold text-emerald-700 dark:text-emerald-400">{summary.present}</span>
                    </div>
                    <div className="bg-white dark:bg-[#161622] rounded-xl p-3 border border-red-100 dark:border-red-950/40 text-center">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-red-600 block">Ausentes</span>
                        <span className="text-xl font-bold text-red-700 dark:text-red-400">{summary.absent}</span>
                    </div>
                    <div className="bg-white dark:bg-[#161622] rounded-xl p-3 border border-amber-100 dark:border-amber-950/40 text-center">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Tardanzas</span>
                        <span className="text-xl font-bold text-amber-700 dark:text-amber-400">{summary.late}</span>
                    </div>
                    <div className="bg-white dark:bg-[#161622] rounded-xl p-3 border border-blue-100 dark:border-blue-950/40 text-center">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Excusas</span>
                        <span className="text-xl font-bold text-blue-700 dark:text-blue-400">{summary.excused}</span>
                    </div>
                    <div className="bg-white dark:bg-[#161622] rounded-xl p-3 border border-purple-100 dark:border-purple-950/40 text-center">
                        <span className="text-[10px] font-bold uppercase tracking-wider text-purple-600 block">Permisos</span>
                        <span className="text-xl font-bold text-purple-700 dark:text-purple-400">{summary.permission}</span>
                    </div>
                </div>

                {/* Student Attendance List */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    {localAttendances.length === 0 ? (
                        <div className="p-12 text-center text-gray-500">
                            <p className="text-sm">Selecciona una sección escolar para cargar el listado de alumnos.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-gray-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-gray-800/30">
                                        <th className="py-3 px-4 w-12 text-center">#</th>
                                        <th className="py-3 px-4">Estudiante</th>
                                        <th className="py-3 px-4">Matrícula / Cédula</th>
                                        <th className="py-3 px-4 text-right">Estado de Asistencia</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    {localAttendances.map((item, index) => (
                                        <tr key={item.student_id} className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition">
                                            <td className="py-3 px-4 text-center font-bold text-gray-400">
                                                {index + 1}
                                            </td>

                                            <td className="py-3 px-4 font-bold text-gray-900 dark:text-white">
                                                {item.student_name}
                                            </td>

                                            <td className="py-3 px-4 font-mono text-gray-500">
                                                {item.student_code} {item.cedula ? `• ${item.cedula}` : ''}
                                            </td>

                                            <td className="py-3 px-4 text-right">
                                                <div className="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-800/80 gap-1 border border-gray-200 dark:border-gray-700">
                                                    {statusOptions.map(opt => (
                                                        <button
                                                            key={opt.key}
                                                            type="button"
                                                            onClick={() => handleStatusChange(item.student_id, opt.key)}
                                                            className={`px-3 py-1 rounded-lg text-xs font-semibold transition ${
                                                                item.status === opt.key
                                                                    ? `${opt.activeBg} shadow-xs`
                                                                    : `text-gray-600 dark:text-gray-400 ${opt.hoverBg}`
                                                            }`}
                                                        >
                                                            {opt.label}
                                                        </button>
                                                    ))}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
