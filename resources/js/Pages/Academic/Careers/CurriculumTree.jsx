import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { GraduationCap, Loader2, BookOpen } from 'lucide-react';

export default function CurriculumTree({
    career,
    periods = [],
    allSubjects = [],
    metrics,
}) {
    const [selectedSubjectId, setSelectedSubjectId] = useState(null);
    const [isSubjectModalOpen, setIsSubjectModalOpen] = useState(false);
    const [editingSubject, setEditingSubject] = useState(null);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [subjectToDelete, setSubjectToDelete] = useState(null);

    // Moodle Linking State
    const [isMoodleModalOpen, setIsMoodleModalOpen] = useState(false);
    const [moodleSubject, setMoodleSubject] = useState(null);
    const [moodleCourses, setMoodleCourses] = useState([]);
    const [loadingMoodle, setLoadingMoodle] = useState(false);
    const [selectedMoodleId, setSelectedMoodleId] = useState('');
    const [savingMoodle, setSavingMoodle] = useState(false);

    const openMoodleSubjectModal = (sub) => {
        setMoodleSubject(sub);
        setSelectedMoodleId(sub.moodle_course_id || '');
        setIsMoodleModalOpen(true);
        if (moodleCourses.length === 0) {
            setLoadingMoodle(true);
            fetch('/admin/courses/moodle-courses')
                .then((res) => res.json())
                .then((data) => {
                    if (data.courses) setMoodleCourses(data.courses);
                })
                .catch((err) => console.error('Error fetching moodle courses:', err))
                .finally(() => setLoadingMoodle(false));
        }
    };

    const handleSaveSubjectMoodle = (e) => {
        e.preventDefault();
        if (!moodleSubject) return;
        setSavingMoodle(true);
        router.post(
            `/admin/careers/${career.id}/subjects/${moodleSubject.id}/link-moodle`,
            { moodle_course_id: selectedMoodleId || null },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsMoodleModalOpen(false);
                    setMoodleSubject(null);
                },
                onFinish: () => setSavingMoodle(false),
            }
        );
    };

    const subjectForm = useForm({
        name: '',
        code: '',
        credits: 3,
        period_number: 1,
        is_elective: false,
        description: '',
        prerequisite_ids: [],
    });

    // Abrir Crear Asignatura
    const openCreateSubjectModal = (periodNum = 1) => {
        setEditingSubject(null);
        subjectForm.reset();
        subjectForm.clearErrors();
        subjectForm.setData({
            name: '',
            code: '',
            credits: 3,
            period_number: periodNum,
            is_elective: false,
            description: '',
            prerequisite_ids: [],
        });
        setIsSubjectModalOpen(true);
    };

    // Abrir Editar Asignatura
    const openEditSubjectModal = (subject) => {
        setEditingSubject(subject);
        subjectForm.clearErrors();
        const currentPrereqIds = Array.isArray(subject.prerequisites)
            ? subject.prerequisites.map((p) => p.id)
            : [];
        subjectForm.setData({
            name: subject.name || '',
            code: subject.code || '',
            credits: subject.credits || 3,
            period_number: subject.period_number || 1,
            is_elective: Boolean(subject.is_elective),
            description: subject.description || '',
            prerequisite_ids: currentPrereqIds,
        });
        setIsSubjectModalOpen(true);
    };

    const handleSaveSubject = (e) => {
        e.preventDefault();
        if (editingSubject) {
            subjectForm.put(`/admin/careers/${career.id}/subjects/${editingSubject.id}`, {
                preserveScroll: true,
                onSuccess: () => setIsSubjectModalOpen(false),
            });
        } else {
            subjectForm.post(`/admin/careers/${career.id}/subjects`, {
                preserveScroll: true,
                onSuccess: () => setIsSubjectModalOpen(false),
            });
        }
    };

    const togglePrerequisite = (id) => {
        const current = [...subjectForm.data.prerequisite_ids];
        const idx = current.indexOf(id);
        if (idx > -1) current.splice(idx, 1);
        else current.push(id);
        subjectForm.setData('prerequisite_ids', current);
    };

    const confirmDeleteSubject = (sub) => {
        setSubjectToDelete(sub);
        setIsDeleteModalOpen(true);
    };

    const handleDeleteSubject = () => {
        if (!subjectToDelete) return;
        router.delete(`/admin/careers/${career.id}/subjects/${subjectToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setSubjectToDelete(null);
            },
        });
    };

    // Determinar si una materia está resaltada como prerrequisito o dependiente
    const isSubjectPrereq = (sub) => {
        if (!selectedSubjectId) return false;
        const sel = allSubjects.find((s) => s.id === selectedSubjectId);
        // Materias de las que depende la seleccionada
        const currentSelectedObject = periods
            .flatMap((p) => p.subjects)
            .find((s) => s.id === selectedSubjectId);
        return currentSelectedObject?.prerequisites?.some((p) => p.id === sub.id);
    };

    const isSubjectDependent = (sub) => {
        if (!selectedSubjectId) return false;
        const currentSelectedObject = periods
            .flatMap((p) => p.subjects)
            .find((s) => s.id === selectedSubjectId);
        return currentSelectedObject?.required_for?.some((r) => r.id === sub.id);
    };

    return (
        <AuthenticatedLayout title={`Pensum: ${career.name}`}>
            <Head title={`Malla Curricular: ${career.name} — SGA`} />

            <div className="space-y-6">
                {/* Migajas y Encabezado */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <Link href="/admin/careers" className="hover:text-brand-primary dark:hover:text-blue-400">
                                Carreras Universitarias
                            </Link>
                            <span>/</span>
                            <span className="font-semibold text-gray-900 dark:text-white">{career.name}</span>
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Malla Curricular & Pensum Académico
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2.5">
                        <a
                            href={`/admin/careers/${career.id}/curriculum/pdf`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition"
                        >
                            <svg className="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Imprimir Pensum PDF
                        </a>
                        <button
                            type="button"
                            onClick={() => openCreateSubjectModal(1)}
                            className="inline-flex items-center gap-1.5 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-xs font-bold text-white shadow-sm transition"
                        >
                            + Nueva Asignatura
                        </button>
                    </div>
                </div>

                {/* Métricas del Pensum */}
                <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Materias</p>
                        <p className="text-xl font-bold text-gray-900 dark:text-white mt-0.5">{metrics?.total_subjects || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Créditos</p>
                        <p className="text-xl font-bold text-blue-600 dark:text-blue-400 font-mono mt-0.5">{metrics?.total_credits || 0} cr</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Cuatrimestres</p>
                        <p className="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">{metrics?.duration_periods || 0} Períodos</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Precio x Crédito</p>
                        <p className="text-xl font-bold text-gray-900 dark:text-white font-mono mt-0.5">DOP ${Number(career.credit_price || 0).toLocaleString()}</p>
                    </div>
                </div>

                {/* Leyenda Interactiva de Prerrequisitos */}
                <div className="rounded-2xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 p-3.5 flex flex-wrap items-center justify-between text-xs text-brand-primary dark:text-blue-300">
                    <div className="flex items-center gap-4">
                        <span className="font-bold">Interacción de Malla:</span>
                        <span className="flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded-md bg-brand-primary inline-block" /> Seleccionada
                        </span>
                        <span className="flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded-md bg-amber-400 inline-block" /> Prerrequisito Requerido
                        </span>
                        <span className="flex items-center gap-1.5">
                            <span className="h-3 w-3 rounded-md bg-emerald-400 inline-block" /> Habilita Materia Futura
                        </span>
                    </div>
                    {selectedSubjectId && (
                        <button
                            type="button"
                            onClick={() => setSelectedSubjectId(null)}
                            className="font-semibold underline hover:text-blue-800"
                        >
                            Limpiar selección
                        </button>
                    )}
                </div>

                {/* Malla Curricular por Períodos */}
                <div className="overflow-x-auto pb-6">
                    <div className="flex gap-4 min-w-[1200px]">
                        {periods.map((period) => (
                            <div
                                key={period.period_number}
                                className="w-72 flex-shrink-0 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622] flex flex-col justify-between"
                            >
                                <div>
                                    {/* Cabecera del Cuatrimestre */}
                                    <div className="border-b border-gray-100 dark:border-gray-800 pb-3 mb-3 flex items-center justify-between">
                                        <div>
                                            <h4 className="text-sm font-bold text-gray-900 dark:text-white">
                                                {period.period_label}
                                            </h4>
                                            <p className="text-[11px] font-mono text-gray-400">
                                                {period.total_credits} Créditos • {period.subjects.length} materias
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => openCreateSubjectModal(period.period_number)}
                                            title="Agregar materia a este cuatrimestre"
                                            className="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition"
                                        >
                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>

                                    {/* Tarjetas de Asignaturas */}
                                    <div className="space-y-3">
                                        {period.subjects.map((sub) => {
                                            const isSelected = selectedSubjectId === sub.id;
                                            const isPrereq = isSubjectPrereq(sub);
                                            const isDependent = isSubjectDependent(sub);

                                            let cardBorderClass = 'border-gray-200 dark:border-gray-800';
                                            let cardBgClass = 'bg-gray-50/50 dark:bg-gray-900/30';

                                            if (isSelected) {
                                                cardBorderClass = 'border-brand-primary ring-2 ring-brand-primary';
                                                cardBgClass = 'bg-blue-50/80 dark:bg-blue-950/40';
                                            } else if (isPrereq) {
                                                cardBorderClass = 'border-amber-400 ring-2 ring-amber-400';
                                                cardBgClass = 'bg-amber-50/80 dark:bg-amber-950/40';
                                            } else if (isDependent) {
                                                cardBorderClass = 'border-emerald-400 ring-2 ring-emerald-400';
                                                cardBgClass = 'bg-emerald-50/80 dark:bg-emerald-950/40';
                                            }

                                            return (
                                                <div
                                                    key={sub.id}
                                                    onClick={() => setSelectedSubjectId(isSelected ? null : sub.id)}
                                                    className={`rounded-2xl border p-3.5 cursor-pointer transition shadow-xs ${cardBorderClass} ${cardBgClass}`}
                                                >
                                                    <div className="flex items-start justify-between">
                                                        <span className="font-mono text-[10px] font-bold text-gray-400">
                                                            {sub.code}
                                                        </span>
                                                        <span className="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                                            {sub.credits} cr
                                                        </span>
                                                    </div>

                                                    <h5 className="text-xs font-bold text-gray-900 dark:text-white mt-1">
                                                        {sub.name}
                                                    </h5>

                                                    {/* Badge Moodle */}
                                                    {sub.moodle_course_id && (
                                                        <div className="mt-1.5 flex items-center gap-1 text-[10px] font-semibold text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/40 px-2 py-0.5 rounded-md border border-orange-200/80 dark:border-orange-900/50 w-fit">
                                                            <GraduationCap className="w-3 h-3 text-orange-500 flex-shrink-0" />
                                                            <span>Moodle ID: {sub.moodle_course_id}</span>
                                                        </div>
                                                    )}

                                                    {/* Prerrequisitos de la asignatura */}
                                                    {sub.prerequisites && sub.prerequisites.length > 0 && (
                                                        <div className="mt-2 text-[10px] text-gray-400">
                                                            Req:{' '}
                                                            <span className="font-mono text-gray-600 dark:text-gray-300">
                                                                {sub.prerequisites.map((p) => p.code).join(', ')}
                                                            </span>
                                                        </div>
                                                    )}

                                                    {/* Acciones */}
                                                    <div className="mt-3 pt-2 border-t border-gray-200/60 dark:border-gray-800 flex items-center justify-between text-[11px]">
                                                        <div className="flex items-center gap-2">
                                                            <button
                                                                type="button"
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    openEditSubjectModal(sub);
                                                                }}
                                                                className="text-gray-500 hover:text-brand-primary dark:text-gray-400 dark:hover:text-blue-400 font-medium"
                                                            >
                                                                Editar
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    openMoodleSubjectModal(sub);
                                                                }}
                                                                className="inline-flex items-center gap-1 text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium"
                                                                title="Vincular con curso de Moodle"
                                                            >
                                                                <GraduationCap className="w-3 h-3" />
                                                                <span>{sub.moodle_course_id ? 'Moodle' : 'Vincular'}</span>
                                                            </button>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                confirmDeleteSubject(sub);
                                                            }}
                                                            className="text-red-400 hover:text-red-600 font-medium"
                                                        >
                                                            Quitar
                                                        </button>
                                                    </div>
                                                </div>
                                            );
                                        })}

                                        {period.subjects.length === 0 && (
                                            <div className="rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 p-6 text-center text-xs text-gray-400">
                                                Sin materias asignadas
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* MODAL CREAR / EDITAR ASIGNATURA */}
            {isSubjectModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-xl bg-white dark:bg-[#161622] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-gray-800 my-8">
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            {editingSubject ? `Editar Asignatura: ${editingSubject.name}` : 'Añadir Asignatura al Pensum'}
                        </h3>
                        <form onSubmit={handleSaveSubject} className="mt-4 space-y-4">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="sm:col-span-2">
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre de la Asignatura *</label>
                                    <input
                                        type="text"
                                        value={subjectForm.data.name}
                                        onChange={(e) => subjectForm.setData('name', e.target.value)}
                                        placeholder="Ej: Programación Orientada a Objetos"
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                    {subjectForm.errors.name && <p className="text-xs text-red-500 mt-1">{subjectForm.errors.name}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Código de Materia *</label>
                                    <input
                                        type="text"
                                        value={subjectForm.data.code}
                                        onChange={(e) => subjectForm.setData('code', e.target.value)}
                                        placeholder="Ej: POO-101"
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Créditos Académicos *</label>
                                    <input
                                        type="number"
                                        value={subjectForm.data.credits}
                                        onChange={(e) => subjectForm.setData('credits', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Cuatrimestre / Período *</label>
                                    <input
                                        type="number"
                                        value={subjectForm.data.period_number}
                                        onChange={(e) => subjectForm.setData('period_number', e.target.value)}
                                        min="1"
                                        max="20"
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div className="flex items-center pt-6">
                                    <label className="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={subjectForm.data.is_elective}
                                            onChange={(e) => subjectForm.setData('is_elective', e.target.checked)}
                                            className="h-4 w-4 rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                                        />
                                        Es asignatura electiva
                                    </label>
                                </div>
                            </div>

                            {/* Prerrequisitos */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Prerrequisitos Académicos (Materias que deben aprobarse previamente):
                                </label>
                                <div className="max-h-40 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-800 p-2.5 space-y-1.5">
                                    {allSubjects
                                        .filter((s) => !editingSubject || s.id !== editingSubject.id)
                                        .map((sub) => {
                                            const isChecked = subjectForm.data.prerequisite_ids.includes(sub.id);
                                            return (
                                                <label
                                                    key={sub.id}
                                                    className="flex items-center justify-between text-xs p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer"
                                                >
                                                    <span className="text-gray-900 dark:text-white">
                                                        <strong className="font-mono text-gray-500 mr-2">{sub.code}</strong>
                                                        {sub.name} (Q{sub.period_number})
                                                    </span>
                                                    <input
                                                        type="checkbox"
                                                        checked={isChecked}
                                                        onChange={() => togglePrerequisite(sub.id)}
                                                        className="h-4 w-4 rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                                                    />
                                                </label>
                                            );
                                        })}
                                </div>
                            </div>

                            <div className="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsSubjectModalOpen(false)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={subjectForm.processing}
                                    className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50"
                                >
                                    Guardar Asignatura
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL CONFIRMAR ELIMINACIÓN DE ASIGNATURA */}
            {isDeleteModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-md bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800 text-center">
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            ¿Remover Asignatura del Pensum?
                        </h3>
                        <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Se eliminará <strong className="text-gray-900 dark:text-white">{subjectToDelete?.name}</strong> de la carrera.
                        </p>
                        <div className="mt-6 flex gap-3">
                            <button
                                type="button"
                                onClick={() => setIsDeleteModalOpen(false)}
                                className="flex-1 rounded-xl border border-gray-300 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={handleDeleteSubject}
                                className="flex-1 rounded-xl bg-red-600 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700"
                            >
                                Sí, Remover
                            </button>
                        </div>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL VINCULAR ASIGNATURA CON MOODLE */}
            {isMoodleModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-lg bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800 my-8">
                        <div className="flex items-center gap-2 text-orange-600 dark:text-orange-400 mb-2">
                            <GraduationCap className="w-5 h-5" />
                            <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                Vincular Asignatura con Moodle
                            </h3>
                        </div>
                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Asocia la asignatura <strong className="text-gray-900 dark:text-white">({moodleSubject?.code}) {moodleSubject?.name}</strong> con su correspondiente aula virtual en Moodle.
                        </p>

                        <form onSubmit={handleSaveSubjectMoodle} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Curso en Moodle (Aula Virtual)
                                </label>
                                {loadingMoodle ? (
                                    <div className="flex items-center gap-2 py-3 px-3.5 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-800 text-xs text-gray-500">
                                        <Loader2 className="w-4 h-4 animate-spin text-orange-500" />
                                        Cargando cursos desde Moodle API...
                                    </div>
                                ) : (
                                    <select
                                        value={selectedMoodleId}
                                        onChange={(e) => setSelectedMoodleId(e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    >
                                        <option value="">-- Sin Vincular / Desvincular --</option>
                                        {moodleCourses.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                [{c.id}] {c.fullname} {c.shortname ? `(${c.shortname})` : ''}
                                            </option>
                                        ))}
                                    </select>
                                )}
                            </div>

                            <div className="rounded-xl bg-orange-50/60 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/30 p-3 text-[11px] text-orange-800 dark:text-orange-300 flex items-start gap-2">
                                <BookOpen className="w-4 h-4 flex-shrink-0 mt-0.5" />
                                <div>
                                    <span className="font-semibold">Sincronización Automática:</span> Al matricular un estudiante o seleccionar esta asignatura en su pensum, el sistema enrolará al usuario automáticamente en el curso de Moodle configurado.
                                </div>
                            </div>

                            <div className="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setIsMoodleModalOpen(false);
                                        setMoodleSubject(null);
                                    }}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={savingMoodle || loadingMoodle}
                                    className="rounded-xl bg-orange-600 hover:bg-orange-700 px-5 py-2 text-xs font-semibold text-white shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5"
                                >
                                    {savingMoodle && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                                    Guardar Vinculación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
