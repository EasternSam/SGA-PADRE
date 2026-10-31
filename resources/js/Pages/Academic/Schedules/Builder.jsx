import { AlertCircle, Zap, MapPin, X, AlertTriangle } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ScheduleBuilder({
    activeYear,
    sections = [],
    selectedSectionId,
    hasBlocks = false,
    scheduleGrid = [],
    days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'],
    dayLabels = {},
    subjects = [],
    teachers = [],
    classrooms = [],
}) {
    const [cellModalOpen, setCellModalOpen] = useState(false);
    const [activeCellInfo, setActiveCellInfo] = useState(null);
    const [conflictWarning, setConflictWarning] = useState('');
    const [checkingConflict, setCheckingConflict] = useState(false);

    const cellForm = useForm({
        section_id: selectedSectionId || '',
        time_block_id: '',
        day_of_week: '',
        subject_id: '',
        teacher_id: '',
        classroom_name: '',
    });

    const handleSectionChange = (newSectionId) => {
        router.get('/admin/schedules/builder', {
            section_id: newSectionId,
        }, {
            preserveState: true,
        });
    };

    const handleGenerateBlocks = () => {
        if (confirm('¿Deseas generar los bloques horarios automáticos según la tanda escolar?')) {
            router.post('/admin/schedules/builder/generate-blocks');
        }
    };

    const openCellModal = (row, day) => {
        const cell = row.cells[day] || {};
        setActiveCellInfo({
            block_id: row.block_id,
            block_name: row.block_name,
            time_range: row.time_range,
            day: day,
            day_name: dayLabels[day] || day,
            existing_id: cell.id,
        });

        cellForm.setData({
            section_id: selectedSectionId,
            time_block_id: row.block_id,
            day_of_week: day,
            subject_id: cell.subject_id || '',
            teacher_id: cell.teacher_id || '',
            classroom_name: cell.classroom_name || '',
        });

        setConflictWarning('');
        setCellModalOpen(true);
    };

    const checkTeacherConflict = async (teacherId) => {
        cellForm.setData('teacher_id', teacherId);
        if (!teacherId || !activeCellInfo) {
            setConflictWarning('');
            return;
        }

        setCheckingConflict(true);
        try {
            const res = await fetch('/admin/schedules/builder/check-conflict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    teacher_id: teacherId,
                    day: activeCellInfo.day,
                    time_block_id: activeCellInfo.block_id,
                    schedule_id: activeCellInfo.existing_id,
                }),
            });
            const data = await res.json();
            if (data.has_conflict) {
                setConflictWarning(data.conflict_message);
            } else {
                setConflictWarning('');
            }
        } catch (err) {
            console.error(err);
        } finally {
            setCheckingConflict(false);
        }
    };

    const handleSaveCell = (e) => {
        e.preventDefault();
        cellForm.post('/admin/schedules/builder/cell', {
            preserveScroll: true,
            onSuccess: () => {
                setCellModalOpen(false);
            },
        });
    };

    const handleClearCell = () => {
        if (!activeCellInfo) return;
        router.post('/admin/schedules/builder/clear-cell', {
            section_id: selectedSectionId,
            time_block_id: activeCellInfo.block_id,
            day_of_week: activeCellInfo.day,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setCellModalOpen(false);
            },
        });
    };

    const selectedSection = sections.find(s => s.id === selectedSectionId);

    return (
        <AuthenticatedLayout>
            <Head title="Constructor de Horarios Escolares - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            Constructor de Horario Escolar
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-3">
                        {activeYear ? (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-brand-primary dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                                <span className="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Año Activo: {activeYear.name}
                            </span>
                        ) : (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <AlertCircle className="w-4 h-4 mr-1 inline text-amber-500" /> Sin año escolar activo
                            </span>
                        )}

                        <Link
                            href="/admin/sections"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            Ver Secciones
                        </Link>
                    </div>
                </div>

                {/* Section Selector & Controls Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
                    <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div className="w-full md:w-96">
                            <label className="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                                Seleccionar Sección a Planificar:
                            </label>
                            <select
                                value={selectedSectionId || ''}
                                onChange={e => handleSectionChange(e.target.value)}
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white font-semibold focus:ring-2 focus:ring-brand-primary"
                            >
                                {sections.length === 0 ? (
                                    <option value="">No hay secciones creadas</option>
                                ) : (
                                    sections.map(s => (
                                        <option key={s.id} value={s.id}>
                                            {s.name} ({s.grade_level})
                                        </option>
                                    ))
                                )}
                            </select>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            {!hasBlocks && (
                                <button
                                    type="button"
                                    onClick={handleGenerateBlocks}
                                    className="rounded-xl bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition active:scale-[0.98]"
                                >
                                    <Zap className="w-4 h-4 mr-1.5 inline" /> Generar Bloques Horarios Automáticos
                                </button>
                            )}

                            {/* Legend */}
                            <div className="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <span className="flex items-center gap-1.5">
                                    <span className="w-3 h-3 rounded-md bg-blue-100 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700"></span>
                                    Clase Asignada
                                </span>
                                <span className="flex items-center gap-1.5">
                                    <span className="w-3 h-3 rounded-md bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600"></span>
                                    Disponible (+ Asignar)
                                </span>
                                <span className="flex items-center gap-1.5">
                                    <span className="w-3 h-3 rounded-md bg-gray-200 dark:bg-gray-700"></span>
                                    Recreo / Receso
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Schedule Matrix */}
                {!hasBlocks ? (
                    <div className="rounded-2xl border-2 border-dashed border-indigo-200 dark:border-indigo-900/40 bg-indigo-50/50 dark:bg-indigo-950/20 p-12 text-center">
                        <svg className="mx-auto h-12 w-12 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 className="mt-3 text-lg font-bold text-indigo-950 dark:text-indigo-200">
                            No hay bloques horarios configurados para este ciclo
                        </h3>
                        <p className="mt-1 text-sm text-indigo-600 dark:text-indigo-400 max-w-md mx-auto">
                            Puedes generar automáticamente los bloques estándar de la jornada escolar (matutina / extendida) con un solo clic.
                        </p>
                        <div className="mt-5">
                            <button
                                type="button"
                                onClick={handleGenerateBlocks}
                                className="rounded-xl bg-brand-primary px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-brand-dark transition"
                            >
                                Generar Bloques Horarios
                            </button>
                        </div>
                    </div>
                ) : scheduleGrid.length === 0 ? (
                    <div className="rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 p-12 text-center bg-white dark:bg-[#161622]">
                        <p className="text-gray-500">Selecciona una sección para cargar la matriz de horario.</p>
                    </div>
                ) : (
                    <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-brand-primary text-white">
                                        <th className="py-3 px-4 text-xs font-bold uppercase tracking-wider w-36 border-r border-blue-900/50">
                                            Bloque / Hora
                                        </th>
                                        {days.map(day => (
                                            <th key={day} className="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider border-r last:border-r-0 border-blue-900/50">
                                                {dayLabels[day] || day}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    {scheduleGrid.map(row => (
                                        <tr
                                            key={row.block_id}
                                            className={row.type !== 'class' ? 'bg-gray-50/80 dark:bg-gray-800/40' : ''}
                                        >
                                            {/* Time Column */}
                                            <td className="py-3 px-4 border-r border-gray-100 dark:border-gray-800 align-top">
                                                <div className="font-bold text-gray-900 dark:text-white">
                                                    {row.block_name}
                                                </div>
                                                <div className="text-[11px] text-gray-500 font-mono mt-0.5">
                                                    {row.time_range}
                                                </div>
                                                {row.type !== 'class' && (
                                                    <span className="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                        {row.type_label}
                                                    </span>
                                                )}
                                            </td>

                                            {/* Days Columns */}
                                            {days.map(day => {
                                                const cell = row.cells[day];
                                                const isClass = row.type === 'class';

                                                return (
                                                    <td
                                                        key={day}
                                                        className="p-1.5 border-r last:border-r-0 border-gray-100 dark:border-gray-800 align-top"
                                                    >
                                                        {isClass ? (
                                                            <button
                                                                type="button"
                                                                onClick={() => openCellModal(row, day)}
                                                                className={`w-full text-left rounded-xl p-2.5 min-h-[72px] transition-all duration-200 flex flex-col justify-between ${
                                                                    cell?.subject_name
                                                                        ? 'bg-blue-50/90 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-800/50 hover:border-brand-primary hover:shadow-sm'
                                                                        : 'bg-white dark:bg-[#1a1a2e] border border-dashed border-gray-200 dark:border-gray-700/60 hover:border-brand-primary hover:bg-blue-50/20'
                                                                }`}
                                                            >
                                                                {cell?.subject_name ? (
                                                                    <>
                                                                        <div>
                                                                            <div className="font-bold text-xs text-brand-primary dark:text-blue-300 line-clamp-1">
                                                                                {cell.subject_name}
                                                                            </div>
                                                                            {cell.teacher_name && (
                                                                                <div className="text-[11px] text-gray-600 dark:text-gray-400 mt-1 flex items-center gap-1">
                                                                                    <svg className="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                                    </svg>
                                                                                    <span className="truncate">{cell.teacher_name}</span>
                                                                                </div>
                                                                            )}
                                                                        </div>
                                                                        {cell.classroom_name && (
                                                                            <div className="mt-1.5 self-start px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white dark:bg-gray-800 border border-blue-100 dark:border-blue-900 text-gray-600 dark:text-gray-300">
                                                                                <MapPin className="w-3 h-3 mr-1 inline text-gray-400" /> {cell.classroom_name}
                                                                            </div>
                                                                        )}
                                                                    </>
                                                                ) : (
                                                                    <div className="h-full flex items-center justify-center text-gray-300 dark:text-gray-600 text-xs font-medium py-3">
                                                                        + Asignar
                                                                    </div>
                                                                )}
                                                            </button>
                                                        ) : (
                                                            <div className="p-3 text-center text-gray-400 text-xs italic">
                                                                {row.type_label}
                                                            </div>
                                                        )}
                                                    </td>
                                                );
                                            })}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal: Asignar Asignatura y Docente al Bloque */}
            {cellModalOpen && activeCellInfo && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-md rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Asignar Asignatura
                                </h3>
                                <p className="text-xs text-gray-500 mt-0.5">
                                    {activeCellInfo.day_name} • {activeCellInfo.block_name} ({activeCellInfo.time_range})
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setCellModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        {conflictWarning && (
                            <div className="mt-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                <AlertTriangle className="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                                <div>
                                    <strong className="block font-semibold">Cruce de horario detectado:</strong>
                                    <span>{conflictWarning}</span>
                                </div>
                            </div>
                        )}

                        <form onSubmit={handleSaveCell} className="space-y-4 mt-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Asignatura *
                                </label>
                                <select
                                    value={cellForm.data.subject_id}
                                    onChange={e => cellForm.setData('subject_id', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">— Ninguna (Vacío) —</option>
                                    {subjects.map(s => (
                                        <option key={s.id} value={s.id}>
                                            {s.name} {s.code ? `(${s.code})` : ''}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Docente Encargado
                                    {checkingConflict && (
                                        <span className="ml-2 text-[10px] text-blue-500 animate-pulse">
                                            (Comprobando cruces...)
                                        </span>
                                    )}
                                </label>
                                <select
                                    value={cellForm.data.teacher_id}
                                    onChange={e => checkTeacherConflict(e.target.value)}
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">Sin docente asignado</option>
                                    {teachers.map(t => (
                                        <option key={t.id} value={t.id}>
                                            {t.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Aula Asignada
                                </label>
                                <div className="space-y-2">
                                    <select
                                        value={cellForm.data.classroom_name}
                                        onChange={e => cellForm.setData('classroom_name', e.target.value)}
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    >
                                        <option value="">Seleccione o escriba el aula...</option>
                                        {classrooms.map(c => (
                                            <option key={c.id} value={c.name}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                    <input
                                        type="text"
                                        placeholder="O especifique un espacio manualmente (Ej: Cancha, Sala de Música)"
                                        value={cellForm.data.classroom_name}
                                        onChange={e => cellForm.setData('classroom_name', e.target.value)}
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                {activeCellInfo.existing_id ? (
                                    <button
                                        type="button"
                                        onClick={handleClearCell}
                                        className="rounded-xl px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 dark:bg-red-950/30 hover:bg-red-100 transition"
                                    >
                                        Vaciar Bloque
                                    </button>
                                ) : (
                                    <div></div>
                                )}

                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setCellModalOpen(false)}
                                        className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={cellForm.processing}
                                        className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                    >
                                        {cellForm.processing ? 'Guardando...' : 'Guardar'}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
