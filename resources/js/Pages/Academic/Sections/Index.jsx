import { X } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function SectionsIndex({
    sections = [],
    academicYears = [],
    gradeLevels = [],
    teachers = [],
    classrooms = [],
    filters = {},
    stats = {},
}) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editingSection, setEditingSection] = useState(null);

    // Local filters
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedYear, setSelectedYear] = useState(filters.year || '');
    const [selectedLevel, setSelectedLevel] = useState(filters.level || '');

    const form = useForm({
        academic_year_id: academicYears.find(y => y.status === 'active')?.id || academicYears[0]?.id || '',
        grade_level_id: gradeLevels[0]?.id || '',
        name: 'A',
        capacity: 35,
        homeroom_teacher_id: '',
        classroom_id: '',
    });

    const handleFilterChange = (newYear, newLevel, newSearch) => {
        router.get('/admin/sections', {
            year: newYear,
            level: newLevel,
            search: newSearch,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const openCreateModal = () => {
        setEditingSection(null);
        form.reset({
            academic_year_id: academicYears.find(y => y.status === 'active')?.id || academicYears[0]?.id || '',
            grade_level_id: gradeLevels[0]?.id || '',
            name: 'A',
            capacity: 35,
            homeroom_teacher_id: '',
            classroom_id: '',
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const openEditModal = (section) => {
        setEditingSection(section);
        form.setData({
            academic_year_id: section.academic_year_id,
            grade_level_id: section.grade_level_id,
            name: section.name,
            capacity: section.capacity,
            homeroom_teacher_id: section.homeroom_teacher_id || '',
            classroom_id: section.classroom_id || '',
        });
        form.clearErrors();
        setModalOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingSection) {
            form.put(`/admin/sections/${editingSection.id}`, {
                onSuccess: () => {
                    setModalOpen(false);
                    setEditingSection(null);
                },
            });
        } else {
            form.post('/admin/sections', {
                onSuccess: () => {
                    setModalOpen(false);
                    form.reset();
                },
            });
        }
    };

    const handleDelete = (section) => {
        if (confirm(`¿Estás seguro de que deseas eliminar la sección "${section.full_name}"?`)) {
            router.delete(`/admin/sections/${section.id}`);
        }
    };

    // Client-side search for ultra-fast filtering
    const displaySections = sections.filter(s => {
        if (!searchTerm) return true;
        const term = searchTerm.toLowerCase();
        return s.name.toLowerCase().includes(term) ||
               s.full_name.toLowerCase().includes(term) ||
               s.grade_level_name.toLowerCase().includes(term) ||
               (s.homeroom_teacher_name && s.homeroom_teacher_name.toLowerCase().includes(term));
    });

    return (
        <AuthenticatedLayout title="Secciones Escolares">
            <Head title="Secciones Escolares - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </span>
                            Gestión de Secciones Escolares
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/schedules/builder"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Horarios Escolares
                        </Link>
                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="inline-flex items-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nueva Sección
                        </button>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Secciones</span>
                            <span className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_sections || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Grupos escolares activos
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Capacidad Total</span>
                            <span className="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_capacity || 0}
                        </div>
                        <div className="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                            Cupos habilitados
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Matrícula Escolar</span>
                            <span className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_enrolled || 0}
                        </div>
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                            Alumnos asignados en secciones
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Maestros Titulares</span>
                            <span className="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.with_teacher || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Secciones con docente a cargo
                        </div>
                    </div>
                </div>

                {/* Filter Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-3 items-center justify-between">
                    <div className="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div className="w-full sm:w-48">
                            <select
                                value={selectedYear}
                                onChange={e => {
                                    setSelectedYear(e.target.value);
                                    handleFilterChange(e.target.value, selectedLevel, searchTerm);
                                }}
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                            >
                                <option value="">Todos los Años Escolares</option>
                                {academicYears.map(year => (
                                    <option key={year.id} value={year.id}>
                                        {year.name} {year.status === 'active' ? '(Activo)' : ''}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="w-full sm:w-56">
                            <select
                                value={selectedLevel}
                                onChange={e => {
                                    setSelectedLevel(e.target.value);
                                    handleFilterChange(selectedYear, e.target.value, searchTerm);
                                }}
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                            >
                                <option value="">Todos los Grados / Niveles</option>
                                {gradeLevels.map(lvl => (
                                    <option key={lvl.id} value={lvl.id}>
                                        {lvl.name} ({lvl.short_name})
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="w-full md:w-72 relative">
                        <input
                            type="text"
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            placeholder="Buscar sección o docente..."
                            className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs pl-8 pr-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                        />
                        <svg className="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {/* Sections Grid */}
                {displaySections.length === 0 ? (
                    <div className="rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 p-12 text-center bg-white dark:bg-[#161622]">
                        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 className="mt-3 text-base font-semibold text-gray-900 dark:text-white">No hay secciones encontradas</h3>
                        <p className="mt-1 text-sm text-gray-500">Crea la primera sección para este año lectivo o ajusta los filtros.</p>
                        <div className="mt-4">
                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="rounded-xl bg-brand-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark"
                            >
                                + Nueva Sección
                            </button>
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        {displaySections.map(section => {
                            const percent = Math.min(100, Math.round((section.student_count / (section.capacity || 1)) * 100));
                            const isFull = percent >= 100;

                            return (
                                <div
                                    key={section.id}
                                    className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between"
                                >
                                    <div>
                                        {/* Card Top */}
                                        <div className="flex items-start justify-between gap-2">
                                            <div>
                                                <h3 className="font-bold text-base text-gray-900 dark:text-white leading-tight">
                                                    {section.full_name}
                                                </h3>
                                                <p className="text-xs text-gray-500 mt-0.5">
                                                    {section.grade_level_name}
                                                </p>
                                            </div>
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${
                                                isFull
                                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'
                                                    : 'bg-blue-50 text-brand-primary dark:bg-blue-900/40 dark:text-blue-300'
                                            }`}>
                                                {section.student_count} / {section.capacity} cupos
                                            </span>
                                        </div>

                                        {/* Progress Bar */}
                                        <div className="mt-3">
                                            <div className="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                                                <span>Ocupación de Aula</span>
                                                <span className="font-semibold">{percent}%</span>
                                            </div>
                                            <div className="w-full bg-gray-100 dark:bg-gray-800 h-2 rounded-full overflow-hidden">
                                                <div
                                                    className={`h-full rounded-full transition-all duration-300 ${
                                                        percent >= 90
                                                            ? 'bg-red-500'
                                                            : percent >= 70
                                                            ? 'bg-amber-500'
                                                            : 'bg-brand-primary dark:bg-blue-500'
                                                    }`}
                                                    style={{ width: `${percent}%` }}
                                                ></div>
                                            </div>
                                        </div>

                                        {/* Info details */}
                                        <div className="mt-4 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                            <div className="flex items-center gap-2">
                                                <span className="text-gray-400">Año:</span>
                                                <span className="font-medium">{section.academic_year_name}</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className="text-gray-400">Maestro Titular:</span>
                                                <span className="font-medium text-gray-900 dark:text-white truncate">
                                                    {section.homeroom_teacher_name}
                                                </span>
                                            </div>
                                            {section.classroom_name && (
                                                <div className="flex items-center gap-2">
                                                    <span className="text-gray-400">Aula Base:</span>
                                                    <span className="font-medium text-brand-primary dark:text-blue-400">
                                                        {section.classroom_name}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Footer Actions */}
                                    <div className="mt-5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                        <Link
                                            href={`/admin/schedules/builder?section_id=${section.id}`}
                                            className="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary dark:text-blue-400 hover:underline"
                                        >
                                            <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Ver Horario
                                        </Link>

                                        <div className="flex items-center gap-1">
                                            <button
                                                type="button"
                                                onClick={() => openEditModal(section)}
                                                className="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                title="Editar Sección"
                                            >
                                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleDelete(section)}
                                                className="p-1.5 text-red-400 hover:text-red-600"
                                                title="Eliminar Sección"
                                            >
                                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* Modal: Crear / Editar Sección */}
            {modalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-lg rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                {editingSection ? `Editar Sección: ${editingSection.full_name}` : 'Registrar Nueva Sección'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>
                        <form onSubmit={handleSubmit} className="space-y-4 mt-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Año Escolar *
                                </label>
                                <select
                                    value={form.data.academic_year_id}
                                    onChange={e => form.setData('academic_year_id', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">Seleccione el año escolar...</option>
                                    {academicYears.map(y => (
                                        <option key={y.id} value={y.id}>
                                            {y.name} {y.status === 'active' ? '(Activo)' : ''}
                                        </option>
                                    ))}
                                </select>
                                {form.errors.academic_year_id && (
                                    <p className="text-xs text-red-500 mt-1">{form.errors.academic_year_id}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Grado Escolar *
                                    </label>
                                    <select
                                        value={form.data.grade_level_id}
                                        onChange={e => form.setData('grade_level_id', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    >
                                        <option value="">Seleccione el grado...</option>
                                        {gradeLevels.map(lvl => (
                                            <option key={lvl.id} value={lvl.id}>
                                                {lvl.name} ({lvl.short_name})
                                            </option>
                                        ))}
                                    </select>
                                    {form.errors.grade_level_id && (
                                        <p className="text-xs text-red-500 mt-1">{form.errors.grade_level_id}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Identificador / Letra *
                                    </label>
                                    <input
                                        type="text"
                                        maxLength="5"
                                        value={form.data.name}
                                        onChange={e => form.setData('name', e.target.value.toUpperCase())}
                                        placeholder="Ej: A, B, C"
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary uppercase font-bold"
                                    />
                                    {form.errors.name && (
                                        <p className="text-xs text-red-500 mt-1">{form.errors.name}</p>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Capacidad Máxima *
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="60"
                                        value={form.data.capacity}
                                        onChange={e => form.setData('capacity', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                    {form.errors.capacity && (
                                        <p className="text-xs text-red-500 mt-1">{form.errors.capacity}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Aula Base Física (Opcional)
                                    </label>
                                    <select
                                        value={form.data.classroom_id}
                                        onChange={e => form.setData('classroom_id', e.target.value)}
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    >
                                        <option value="">Sin aula fija</option>
                                        {classrooms.map(c => (
                                            <option key={c.id} value={c.id}>
                                                {c.name} ({c.type} - cap. {c.capacity})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Maestro/a Titular (Homeroom Teacher)
                                </label>
                                <select
                                    value={form.data.homeroom_teacher_id}
                                    onChange={e => form.setData('homeroom_teacher_id', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">Sin maestro titular asignado</option>
                                    {teachers.map(t => (
                                        <option key={t.id} value={t.id}>
                                            {t.name} ({t.email})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {form.processing ? 'Guardando...' : (editingSection ? 'Actualizar Sección' : 'Crear Sección')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
