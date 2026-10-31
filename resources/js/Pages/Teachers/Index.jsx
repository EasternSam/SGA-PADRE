import React, { useState, useEffect, useMemo } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import Modal from '@/components/ui/Modal';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ProgressFoldButton } from '@/components/godui';
import { Activity, Users, Calendar, DollarSign, UserCheck, Plus, AlertTriangle, Check, Loader2 } from 'lucide-react';

export default function TeachersIndex({
    teachers,
    filters,
    metrics,
}) {
    const { branding } = usePage().props;
    const palette = useMemo(() => ({
        primary: branding?.palette?.primary || 'var(--brand-primary, #211170)',
        secondary: branding?.palette?.secondary || 'var(--brand-secondary, #1c1917)',
        accent: branding?.palette?.accent || 'var(--brand-accent, #ea580c)',
        neutral: branding?.palette?.neutral || 'var(--brand-neutral, #78716c)',
    }), [branding]);
    const [search, setSearch] = useState(filters?.search || '');
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [editingTeacher, setEditingTeacher] = useState(null);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [teacherToDelete, setTeacherToDelete] = useState(null);

    const teacherForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/teachers', { search }, { preserveState: true, replace: true });
    };

    const handleClearFilters = () => {
        setSearch('');
        router.get('/admin/teachers', {}, { preserveState: true });
    };

    const openCreateModal = () => {
        setEditingTeacher(null);
        teacherForm.reset();
        teacherForm.clearErrors();
        setIsFormModalOpen(true);
    };

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (params.get('create') === '1' || params.get('action') === 'create') {
            openCreateModal();
        }
        const handleOpenCreate = () => openCreateModal();
        window.addEventListener('sga:open-create-teacher', handleOpenCreate);
        return () => window.removeEventListener('sga:open-create-teacher', handleOpenCreate);
    }, []);

    const openEditModal = (teacher) => {
        setEditingTeacher(teacher);
        teacherForm.clearErrors();
        teacherForm.setData({
            name: teacher.name || '',
            email: teacher.email || '',
            password: '',
            password_confirmation: '',
        });
        setIsFormModalOpen(true);
    };

    const handleSaveTeacher = (e) => {
        e.preventDefault();
        if (editingTeacher) {
            teacherForm.put(`/admin/teachers/${editingTeacher.id}`, {
                preserveScroll: true,
                onSuccess: () => setIsFormModalOpen(false),
            });
        } else {
            teacherForm.post('/admin/teachers', {
                preserveScroll: true,
                onSuccess: () => setIsFormModalOpen(false),
            });
        }
    };

    const confirmDelete = (teacher) => {
        setTeacherToDelete(teacher);
        setIsDeleteModalOpen(true);
    };

    const handleDelete = () => {
        if (!teacherToDelete) return;
        router.delete(`/admin/teachers/${teacherToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setTeacherToDelete(null);
            },
        });
    };

    return (
        <AuthenticatedLayout title="Directorio de Docentes">
            <Head title="Directorio de Docentes — SGA" />

            <div className="space-y-6">
                {/* Encabezado y Acción Principal */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Directorio de Docentes
                        </h1>
                        
                    </div>
                    <div>
                        <ProgressFoldButton
                            variant="primary"
                            onClick={openCreateModal}
                        >
                            <span className="inline-flex items-center gap-2">
                                <Plus className="size-4" />
                                <span>Nuevo Profesor</span>
                            </span>
                        </ProgressFoldButton>
                    </div>
                </div>

                {/* Métricas Principales (KPIs) */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-[var(--brand-primary)] dark:bg-blue-900/20 dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Docentes
                                </p>
                                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {metrics?.total?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Con Carga Activa
                                </p>
                                <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                    {metrics?.with_load?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Secciones Asignadas
                                </p>
                                <p className="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    {metrics?.total_schedules?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Buscador */}
                <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <form onSubmit={handleSearch} className="flex gap-3 items-center">
                        <div className="relative flex-1">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar por nombre de profesor o correo electrónico..."
                                className="w-full rounded-xl border-0 py-2.5 pl-10 pr-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900/50 dark:text-white dark:ring-gray-700"
                            />
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <button
                            type="submit"
                            className="rounded-xl bg-[var(--brand-primary)] py-2.5 px-5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark transition"
                        >
                            Buscar
                        </button>
                        {search && (
                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="rounded-xl border border-gray-300 p-2.5 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        )}
                    </form>
                </div>

                {/* Tabla de Profesores */}
                <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-left text-sm">
                            <thead className="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th className="py-3.5 pl-6 pr-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Docente
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Rol
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Carga Académica
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Estado
                                    </th>
                                    <th className="py-3.5 pl-3 pr-6 text-right text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                {teachers?.data && teachers.data.length > 0 ? (
                                    teachers.data.map((teacher) => {
                                        const initials = teacher.name
                                            ? teacher.name.split(' ').map((n) => n[0]).slice(0, 2).join('').toUpperCase()
                                            : 'DOC';
                                        return (
                                            <tr
                                                key={teacher.id}
                                                className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors"
                                            >
                                                <td className="py-4 pl-6 pr-3">
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[var(--brand-primary)]/10 text-[var(--brand-primary)] font-bold text-sm dark:bg-blue-900/30 dark:text-blue-300">
                                                            {initials}
                                                        </div>
                                                        <div>
                                                            <Link
                                                                href={`/admin/teachers/profile/${teacher.id}`}
                                                                className="font-semibold text-gray-900 hover:text-[var(--brand-primary)] dark:text-white dark:hover:text-blue-400 transition"
                                                            >
                                                                {teacher.name}
                                                            </Link>
                                                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                                                {teacher.email}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="px-3 py-4">
                                                    <span className="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-[var(--brand-primary)] dark:bg-blue-900/20 dark:text-blue-300">
                                                        Profesor
                                                    </span>
                                                </td>

                                                <td className="px-3 py-4">
                                                    <span className="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                                        <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                        {teacher.schedules_count || 0} sección(es)
                                                    </span>
                                                </td>

                                                <td className="px-3 py-4">
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                        Activo
                                                    </span>
                                                </td>

                                                <td className="py-4 pl-3 pr-6 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Link
                                                            href={`/admin/teachers/profile/${teacher.id}`}
                                                            title="Ver Carga Académica"
                                                            className="rounded-lg p-1.5 text-gray-500 hover:bg-blue-50 hover:text-[var(--brand-primary)] dark:hover:bg-blue-900/20 dark:hover:text-blue-400 transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </Link>

                                                        <button
                                                            type="button"
                                                            onClick={() => openEditModal(teacher)}
                                                            title="Editar Docente"
                                                            className="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>

                                                        <button
                                                            type="button"
                                                            onClick={() => confirmDelete(teacher)}
                                                            title="Eliminar Docente"
                                                            className="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="py-12 text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron profesores registrados.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Paginación */}
                    {teachers?.links && teachers.links.length > 3 && (
                        <div className="flex items-center justify-between border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-[#161622]">
                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                Mostrando {teachers.from || 0} a {teachers.to || 0} de {teachers.total} profesores
                            </div>
                            <div className="flex items-center gap-1">
                                {teachers.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        preserveScroll
                                        className={`px-3 py-1.5 text-xs rounded-lg font-medium transition ${
                                            link.active
                                                ? 'bg-[var(--brand-primary)] text-white shadow-sm'
                                                : link.url
                                                ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                                                : 'text-gray-300 pointer-events-none dark:text-gray-600'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal Crear / Editar Docente */}
            <Modal
                isOpen={isFormModalOpen}
                onClose={() => setIsFormModalOpen(false)}
                title={editingTeacher ? `Editar Profesor: ${editingTeacher.name}` : 'Registrar Nuevo Profesor'}
                subtitle="Asignación del rol institucional de Profesor y credenciales de acceso."
                maxWidth="max-w-lg"
                footer={
                    <div className="flex items-center justify-end gap-3 w-full">
                        <button
                            type="button"
                            onClick={() => setIsFormModalOpen(false)}
                            className="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            form="teacher-form-modal"
                            disabled={teacherForm.processing}
                            className="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:opacity-95 disabled:opacity-50 transition cursor-pointer"
                            style={{ backgroundColor: palette.primary }}
                        >
                            {teacherForm.processing ? (
                                <>
                                    <Loader2 className="size-3.5 animate-spin" />
                                    <span>Guardando...</span>
                                </>
                            ) : (
                                <>
                                    <Check className="size-3.5" />
                                    <span>{editingTeacher ? 'Actualizar Docente' : 'Registrar Docente'}</span>
                                </>
                            )}
                        </button>
                    </div>
                }
            >
                <form id="teacher-form-modal" onSubmit={handleSaveTeacher} className="space-y-4 py-1">
                    <div>
                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nombre Completo *
                        </label>
                        <input
                            type="text"
                            value={teacherForm.data.name}
                            onChange={(e) => teacherForm.setData('name', e.target.value)}
                            placeholder="Ej: Lic. Carlos Manuel Fernández"
                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                        />
                        {teacherForm.errors.name && (
                            <p className="mt-1 text-xs text-red-500">{teacherForm.errors.name}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Correo Electrónico Institucional *
                        </label>
                        <input
                            type="email"
                            value={teacherForm.data.email}
                            onChange={(e) => teacherForm.setData('email', e.target.value)}
                            placeholder="profesor@institucion.edu.do"
                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                        />
                        {teacherForm.errors.email && (
                            <p className="mt-1 text-xs text-red-500">{teacherForm.errors.email}</p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                {editingTeacher ? 'Nueva Contraseña (Opcional)' : 'Contraseña *'}
                            </label>
                            <input
                                type="password"
                                value={teacherForm.data.password}
                                onChange={(e) => teacherForm.setData('password', e.target.value)}
                                placeholder="Mínimo 8 caracteres"
                                className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                            />
                            {teacherForm.errors.password && (
                                <p className="mt-1 text-xs text-red-500">{teacherForm.errors.password}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Confirmar Contraseña
                            </label>
                            <input
                                type="password"
                                value={teacherForm.data.password_confirmation}
                                onChange={(e) => teacherForm.setData('password_confirmation', e.target.value)}
                                placeholder="Repetir contraseña"
                                className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                            />
                        </div>
                    </div>
                </form>
            </Modal>

            {/* Modal Confirmación de Eliminación */}
            <Modal
                isOpen={isDeleteModalOpen}
                onClose={() => setIsDeleteModalOpen(false)}
                title="¿Eliminar Profesor?"
                maxWidth="max-w-md"
                footer={
                    <div className="flex items-center justify-end gap-3 w-full">
                        <button
                            type="button"
                            onClick={() => setIsDeleteModalOpen(false)}
                            className="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition text-center cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onClick={handleDelete}
                            className="flex-1 rounded-xl bg-red-600 hover:bg-red-700 py-2.5 text-sm font-bold text-white shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            Sí, Eliminar
                        </button>
                    </div>
                }
            >
                <div className="text-center py-2">
                    <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 mb-3">
                        <AlertTriangle className="size-6" />
                    </div>
                    <p className="text-xs text-center text-gray-500 dark:text-gray-400 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar al docente{' '}
                        <span className="font-semibold text-gray-900 dark:text-white">
                            {teacherToDelete?.name}
                        </span>
                        ? Esta acción es irreversible y removerá sus accesos al aula virtual y asignaciones académicas.
                    </p>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
