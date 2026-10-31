import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function TeacherProfile({
    teacher,
    schedules = [],
    metrics,
    courses = [],
    unassignedSchedules = [],
    classrooms = [],
}) {
    const [activeTab, setActiveTab] = useState('schedules'); // 'schedules' | 'weekly' | 'students'
    const [expandedScheduleId, setExpandedScheduleId] = useState(null);

    // Modal de Asignación de Carga
    const [isAssignModalOpen, setIsAssignModalOpen] = useState(false);
    const [assignMode, setAssignMode] = useState('existing'); // 'existing' | 'create'
    const [selectedUnassignedId, setSelectedUnassignedId] = useState('');

    // Estado para creación de sección directa
    const [selectedCourseId, setSelectedCourseId] = useState('');
    const [courseModulesList, setCourseModulesList] = useState([]);
    const [isLoadingModules, setIsLoadingModules] = useState(false);

    // Modal de Edición de Perfil
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const editForm = useForm({
        name: teacher.name || '',
        email: teacher.email || '',
        password: '',
        password_confirmation: '',
    });

    // Formulario de Asignación existente
    const assignForm = useForm({
        course_schedule_id: '',
    });

    // Formulario de Creación de Sección
    const createScheduleForm = useForm({
        module_id: '',
        section_name: '',
        days_of_week: [],
        start_time: '08:00',
        end_time: '10:00',
        start_date: new Date().toISOString().split('T')[0],
        end_date: new Date(Date.now() + 90 * 86400000).toISOString().split('T')[0],
        capacity: 30,
        classroom_id: '',
        modality: 'Presencial',
    });

    // Cargar módulos cuando cambia curso en modal
    const handleCourseChange = async (cId) => {
        setSelectedCourseId(cId);
        createScheduleForm.setData('module_id', '');
        if (!cId) {
            setCourseModulesList([]);
            return;
        }

        setIsLoadingModules(true);
        try {
            const res = await fetch(`/admin/teachers/course-modules/${cId}`);
            const data = await res.json();
            setCourseModulesList(data);
        } catch (e) {
            console.error(e);
        } finally {
            setIsLoadingModules(false);
        }
    };

    // Toggle de días de la semana
    const toggleDay = (day) => {
        const current = [...createScheduleForm.data.days_of_week];
        const idx = current.indexOf(day);
        if (idx > -1) {
            current.splice(idx, 1);
        } else {
            current.push(day);
        }
        createScheduleForm.setData('days_of_week', current);
    };

    // Confirmar asignación existente
    const handleConfirmAssign = (e) => {
        e.preventDefault();
        if (!selectedUnassignedId) return;

        assignForm.setData('course_schedule_id', selectedUnassignedId);
        assignForm.post(`/admin/teachers/${teacher.id}/assign-schedule`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsAssignModalOpen(false);
                setSelectedUnassignedId('');
            },
        });
    };

    // Confirmar creación de nueva sección
    const handleConfirmCreateSchedule = (e) => {
        e.preventDefault();
        createScheduleForm.post(`/admin/teachers/${teacher.id}/create-schedule`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsAssignModalOpen(false);
                createScheduleForm.reset();
            },
        });
    };

    // Desasignar sección
    const handleUnassign = (schId) => {
        if (!confirm('¿Desvincular al docente de esta sección académica?')) return;
        router.post(`/admin/teachers/${teacher.id}/unassign-schedule/${schId}`, {}, { preserveScroll: true });
    };

    // Guardar edición de docente
    const handleSaveEdit = (e) => {
        e.preventDefault();
        editForm.put(`/admin/teachers/${teacher.id}`, {
            preserveScroll: true,
            onSuccess: () => setIsEditModalOpen(false),
        });
    };

    const initials = teacher.name
        ? teacher.name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase()
        : 'DOC';

    const weekDaysList = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    return (
        <AuthenticatedLayout title={`Ficha del Docente: ${teacher.name}`}>
            <Head title={`Ficha Docente: ${teacher.name} — SGA`} />

            <div className="space-y-6">
                {/* Migajas */}
                <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <Link href="/admin/teachers" className="hover:text-brand-primary dark:hover:text-blue-400 transition">
                        Directorio de Docentes
                    </Link>
                    <span>/</span>
                    <span className="font-semibold text-gray-900 dark:text-white">{teacher.name}</span>
                </div>

                {/* Tarjeta 360° del Docente */}
                <div className="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <div className="bg-gradient-to-r from-brand-primary to-brand-dark p-6 sm:p-8 text-white relative">
                        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative z-10">
                            <div className="flex items-center gap-5">
                                <div className="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white font-black text-2xl border border-white/20 backdrop-blur-md shadow-inner">
                                    {initials}
                                </div>
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                            {teacher.name}
                                        </h1>
                                        <span className="rounded-full bg-white/20 px-3 py-0.5 text-xs font-semibold backdrop-blur-md">
                                            Docente Oficial
                                        </span>
                                    </div>
                                    <p className="mt-1 text-sm text-blue-100 flex items-center gap-3">
                                        <span>{teacher.email}</span>
                                        <span>•</span>
                                        <span>Docente ID #{teacher.id}</span>
                                    </p>
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        <span className="inline-flex items-center rounded-lg bg-blue-900/60 px-2.5 py-1 text-xs font-medium text-blue-200 border border-blue-400/30">
                                            {metrics?.total_sections || 0} Sección(es) Activa(s)
                                        </span>
                                        <span className="inline-flex items-center rounded-lg bg-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-300 border border-emerald-400/30">
                                            {metrics?.total_students || 0} Alumnos Atendidos
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Botones de acción */}
                            <div className="flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setIsEditModalOpen(true)}
                                    className="rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-md transition"
                                >
                                    Editar Datos
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setIsAssignModalOpen(true)}
                                    className="rounded-xl bg-white hover:bg-blue-50 px-4 py-2 text-xs font-bold text-brand-primary shadow-sm transition"
                                >
                                    + Asignar Carga Académica
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Barra de pestañas */}
                    <div className="border-b border-gray-100 bg-white px-6 dark:border-gray-800 dark:bg-[#161622]">
                        <nav className="-mb-px flex space-x-8" aria-label="Tabs">
                            <button
                                type="button"
                                onClick={() => setActiveTab('schedules')}
                                className={`whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition ${
                                    activeTab === 'schedules'
                                        ? 'border-brand-primary text-brand-primary dark:border-blue-400 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                }`}
                            >
                                Secciones y Carga Docente ({schedules?.length || 0})
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveTab('weekly')}
                                className={`whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition ${
                                    activeTab === 'weekly'
                                        ? 'border-brand-primary text-brand-primary dark:border-blue-400 dark:text-blue-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                }`}
                            >
                                Horario Semanal Integrado
                            </button>
                        </nav>
                    </div>
                </div>

                {/* CONTENIDO TAB 1: SECCIONES Y CARGA DOCENTE */}
                {activeTab === 'schedules' && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                Asignaturas y Grupos a Cargo
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsAssignModalOpen(true)}
                                className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                            >
                                + Asignar otra sección
                            </button>
                        </div>

                        {schedules && schedules.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {schedules.map((sch) => {
                                    const enrolledCount = sch.enrollments?.length || 0;
                                    const isExpanded = expandedScheduleId === sch.id;
                                    return (
                                        <div
                                            key={sch.id}
                                            className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622] flex flex-col justify-between"
                                        >
                                            <div>
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <span className="text-[11px] font-bold uppercase tracking-wider text-brand-primary dark:text-blue-400">
                                                            {sch.module?.course?.name || 'Programa Académico'}
                                                        </span>
                                                        <h4 className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                                            {sch.module?.name || 'Módulo'}
                                                        </h4>
                                                    </div>
                                                    <span className="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-brand-primary dark:bg-blue-900/30 dark:text-blue-300">
                                                        {sch.section_name || `Sec #${sch.id}`}
                                                    </span>
                                                </div>

                                                <div className="mt-4 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span>
                                                            Días: <strong>{Array.isArray(sch.days_of_week) ? sch.days_of_week.join(', ') : 'Días variables'}</strong>
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>
                                                            Horario: {sch.start_time ? sch.start_time.substring(0, 5) : ''} - {sch.end_time ? sch.end_time.substring(0, 5) : ''}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                        <span>Aula: {sch.classroom?.name || 'Virtual / Por asignar'} ({sch.modality || 'Presencial'})</span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                        <span>
                                                            Inscritos: <strong className="text-gray-900 dark:text-white">{enrolledCount}</strong> de {sch.capacity || 30} cupos
                                                        </span>
                                                    </div>
                                                </div>

                                                {/* Desplegable de lista de estudiantes */}
                                                {isExpanded && (
                                                    <div className="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                                                        <p className="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">
                                                            Estudiantes Matriculados ({enrolledCount})
                                                        </p>
                                                        {enrolledCount > 0 ? (
                                                            <div className="max-h-40 overflow-y-auto space-y-1.5 pr-1">
                                                                {sch.enrollments.map((enr) => (
                                                                    <div key={enr.id} className="flex items-center justify-between text-xs py-1 border-b border-gray-50 dark:border-gray-800/60">
                                                                        <Link
                                                                            href={`/admin/students/profile/${enr.student_id}`}
                                                                            className="font-medium text-gray-900 hover:text-brand-primary dark:text-white dark:hover:text-blue-400 truncate max-w-[200px]"
                                                                        >
                                                                            {enr.student?.first_name} {enr.student?.last_name}
                                                                        </Link>
                                                                        <span className="font-mono text-[10px] text-gray-400">
                                                                            {enr.student?.student_code || `ID #${enr.student_id}`}
                                                                        </span>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        ) : (
                                                            <p className="text-xs text-gray-400 py-2">No hay estudiantes matriculados en esta sección.</p>
                                                        )}
                                                    </div>
                                                )}
                                            </div>

                                            <div className="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                                <button
                                                    type="button"
                                                    onClick={() => setExpandedScheduleId(isExpanded ? null : sch.id)}
                                                    className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                                                >
                                                    {isExpanded ? 'Ocultar alumnos ▲' : `Ver alumnos (${enrolledCount}) ▼`}
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => handleUnassign(sch.id)}
                                                    className="rounded-lg border border-red-200 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 transition"
                                                >
                                                    Desvincular
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                                <svg className="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <p className="text-base font-medium text-gray-900 dark:text-white">Sin secciones asignadas</p>
                                <p className="text-xs text-gray-400 mt-1">Este docente aún no tiene carga horaria asignada en el período.</p>
                                <button
                                    type="button"
                                    onClick={() => setIsAssignModalOpen(true)}
                                    className="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-primary px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark transition"
                                >
                                    + Asignar Primera Sección
                                </button>
                            </div>
                        )}
                    </div>
                )}

                {/* CONTENIDO TAB 2: HORARIO SEMANAL INTEGRADO */}
                {activeTab === 'weekly' && (
                    <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <h3 className="text-base font-bold text-gray-900 dark:text-white mb-4">
                            Matriz Semanal de Carga Horaria
                        </h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                            {weekDaysList.map((day) => {
                                const daySchedules = schedules.filter(
                                    (s) => Array.isArray(s.days_of_week) && s.days_of_week.includes(day)
                                );
                                return (
                                    <div
                                        key={day}
                                        className="rounded-xl border border-gray-200 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-900/30 flex flex-col min-h-[160px]"
                                    >
                                        <div className="text-xs font-bold text-brand-primary dark:text-blue-400 border-b border-gray-200 pb-2 mb-2 dark:border-gray-800 text-center uppercase tracking-wider">
                                            {day}
                                        </div>
                                        <div className="space-y-2 flex-1">
                                            {daySchedules.length > 0 ? (
                                                daySchedules.map((ds) => (
                                                    <div
                                                        key={ds.id}
                                                        className="rounded-lg bg-white p-2 border border-blue-100 shadow-xs dark:bg-[#161622] dark:border-gray-800"
                                                    >
                                                        <p className="font-bold text-gray-900 dark:text-white text-[11px] truncate">
                                                            {ds.module?.name || 'Módulo'}
                                                        </p>
                                                        <p className="text-[10px] text-gray-500 font-mono mt-0.5">
                                                            {ds.start_time?.substring(0, 5)} - {ds.end_time?.substring(0, 5)}
                                                        </p>
                                                        <span className="inline-block mt-1 text-[9px] px-1.5 py-0.5 rounded bg-blue-50 text-brand-primary dark:bg-blue-900/30 dark:text-blue-300 font-semibold">
                                                            {ds.section_name}
                                                        </span>
                                                    </div>
                                                ))
                                            ) : (
                                                <p className="text-[11px] text-gray-400 text-center py-6">Libre</p>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>

            {/* MODAL: ASIGNAR CARGA ACADÉMICA (EXISTENTE O NUEVA) */}
            {isAssignModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-2xl bg-white dark:bg-[#161622] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Asignación de Carga Académica
                                </h3>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Docente: <strong className="text-gray-900 dark:text-white">{teacher.name}</strong>
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setIsAssignModalOpen(false)}
                                className="rounded-xl p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Selector de Modo: Existente vs Crear Nueva */}
                        <div className="mt-4 flex rounded-xl bg-gray-100 p-1 dark:bg-gray-900">
                            <button
                                type="button"
                                onClick={() => setAssignMode('existing')}
                                className={`flex-1 rounded-lg py-2 text-xs font-semibold transition ${
                                    assignMode === 'existing'
                                        ? 'bg-white text-gray-900 shadow-sm dark:bg-[#161622] dark:text-white'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'
                                }`}
                            >
                                Asignar Sección Existente ({unassignedSchedules.length})
                            </button>
                            <button
                                type="button"
                                onClick={() => setAssignMode('create')}
                                className={`flex-1 rounded-lg py-2 text-xs font-semibold transition ${
                                    assignMode === 'create'
                                        ? 'bg-white text-gray-900 shadow-sm dark:bg-[#161622] dark:text-white'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'
                                }`}
                            >
                                + Crear Nueva Sección Directa
                            </button>
                        </div>

                        {/* MODO 1: ASIGNAR SECCIÓN EXISTENTE */}
                        {assignMode === 'existing' && (
                            <form onSubmit={handleConfirmAssign} className="mt-6 space-y-4">
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Secciones sin profesor asignado:
                                </label>
                                {unassignedSchedules.length > 0 ? (
                                    <div className="max-h-60 overflow-y-auto space-y-2 pr-1">
                                        {unassignedSchedules.map((sch) => (
                                            <label
                                                key={sch.id}
                                                className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer text-xs transition ${
                                                    selectedUnassignedId === sch.id
                                                        ? 'border-brand-primary bg-blue-50/50 dark:border-blue-500 dark:bg-blue-900/20'
                                                        : 'border-gray-200 dark:border-gray-800'
                                                }`}
                                            >
                                                <div>
                                                    <p className="font-bold text-gray-900 dark:text-white">
                                                        {sch.module_name} — <span className="font-normal text-gray-500">{sch.section_name}</span>
                                                    </p>
                                                    <p className="text-[11px] text-gray-400 mt-0.5">
                                                        {sch.course_name} • {Array.isArray(sch.days) ? sch.days.join(', ') : ''} ({sch.start_time} - {sch.end_time})
                                                    </p>
                                                </div>
                                                <input
                                                    type="radio"
                                                    name="selected_sch"
                                                    value={sch.id}
                                                    checked={selectedUnassignedId === sch.id}
                                                    onChange={() => setSelectedUnassignedId(sch.id)}
                                                    className="text-brand-primary focus:ring-brand-primary"
                                                />
                                            </label>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-center py-6 text-xs text-gray-400">
                                        No hay secciones huérfanas disponibles. Puedes crear una nueva en la otra pestaña.
                                    </p>
                                )}

                                <div className="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsAssignModalOpen(false)}
                                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={!selectedUnassignedId || assignForm.processing}
                                        className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50 transition"
                                    >
                                        Confirmar Asignación
                                    </button>
                                </div>
                            </form>
                        )}

                        {/* MODO 2: CREAR NUEVA SECCIÓN DIRECTA */}
                        {assignMode === 'create' && (
                            <form onSubmit={handleConfirmCreateSchedule} className="mt-6 space-y-4">
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Programa o Carrera *
                                        </label>
                                        <select
                                            value={selectedCourseId}
                                            onChange={(e) => handleCourseChange(e.target.value)}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        >
                                            <option value="">Seleccione programa...</option>
                                            {courses.map((c) => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Módulo / Asignatura *
                                        </label>
                                        <select
                                            value={createScheduleForm.data.module_id}
                                            onChange={(e) => createScheduleForm.setData('module_id', e.target.value)}
                                            disabled={!selectedCourseId || isLoadingModules}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700 disabled:opacity-50"
                                        >
                                            <option value="">{isLoadingModules ? 'Cargando módulos...' : 'Seleccione módulo...'}</option>
                                            {courseModulesList.map((m) => (
                                                <option key={m.id} value={m.id}>{m.name}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Nombre de Sección *
                                        </label>
                                        <input
                                            type="text"
                                            value={createScheduleForm.data.section_name}
                                            onChange={(e) => createScheduleForm.setData('section_name', e.target.value)}
                                            placeholder="Ej: Sec-01 Matutina"
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Aula / Espacio
                                        </label>
                                        <select
                                            value={createScheduleForm.data.classroom_id}
                                            onChange={(e) => createScheduleForm.setData('classroom_id', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        >
                                            <option value="">Virtual / Sin aula fija</option>
                                            {classrooms.map((cr) => (
                                                <option key={cr.id} value={cr.id}>{cr.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>

                                {/* Días de la semana */}
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                        Días de Clase *
                                    </label>
                                    <div className="flex flex-wrap gap-2">
                                        {weekDaysList.map((day) => {
                                            const isSelected = createScheduleForm.data.days_of_week.includes(day);
                                            return (
                                                <button
                                                    type="button"
                                                    key={day}
                                                    onClick={() => toggleDay(day)}
                                                    className={`px-3 py-1 rounded-lg text-xs font-semibold border transition ${
                                                        isSelected
                                                            ? 'bg-brand-primary text-white border-brand-primary'
                                                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300'
                                                    }`}
                                                >
                                                    {day}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                {/* Horas y Fechas */}
                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Hora Inicio *
                                        </label>
                                        <input
                                            type="time"
                                            value={createScheduleForm.data.start_time}
                                            onChange={(e) => createScheduleForm.setData('start_time', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Hora Fin *
                                        </label>
                                        <input
                                            type="time"
                                            value={createScheduleForm.data.end_time}
                                            onChange={(e) => createScheduleForm.setData('end_time', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Fecha Inicio *
                                        </label>
                                        <input
                                            type="date"
                                            value={createScheduleForm.data.start_date}
                                            onChange={(e) => createScheduleForm.setData('start_date', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Fecha Fin *
                                        </label>
                                        <input
                                            type="date"
                                            value={createScheduleForm.data.end_date}
                                            onChange={(e) => createScheduleForm.setData('end_date', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                </div>

                                <div className="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                                    <button
                                        type="button"
                                        onClick={() => setIsAssignModalOpen(false)}
                                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={createScheduleForm.processing}
                                        className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50 transition"
                                    >
                                        {createScheduleForm.processing ? 'Creando...' : 'Crear y Asignar Sección'}
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>
                </div>, document.body)
            )}

            {/* MODAL: EDITAR DATOS DEL DOCENTE */}
            {isEditModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-md bg-white dark:bg-[#161622] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                Editar Datos del Profesor
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsEditModalOpen(false)}
                                className="rounded-xl p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleSaveEdit} className="mt-4 space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre Completo *
                                </label>
                                <input
                                    type="text"
                                    value={editForm.data.name}
                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                    className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Correo Electrónico *
                                </label>
                                <input
                                    type="email"
                                    value={editForm.data.email}
                                    onChange={(e) => editForm.setData('email', e.target.value)}
                                    className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Nueva Contraseña (Opcional)
                                </label>
                                <input
                                    type="password"
                                    value={editForm.data.password}
                                    onChange={(e) => editForm.setData('password', e.target.value)}
                                    placeholder="Dejar en blanco para no cambiar"
                                    className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                />
                            </div>

                            <div className="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsEditModalOpen(false)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={editForm.processing}
                                    className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50 transition"
                                >
                                    Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
