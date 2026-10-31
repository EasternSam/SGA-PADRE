import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function CareersIndex({
    careers,
    filters,
    metrics,
}) {
    const [search, setSearch] = useState(filters?.search || '');
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [editingCareer, setEditingCareer] = useState(null);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [careerToDelete, setCareerToDelete] = useState(null);

    const careerForm = useForm({
        name: '',
        code: '',
        degree_title: '',
        total_credits: 120,
        duration_periods: 10,
        registration_fee: 5000,
        monthly_fee: 6500,
        credit_price: 1200,
        description: '',
        is_sequential: true,
        status: 'Activo',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/careers', { search }, { preserveState: true, replace: true });
    };

    const openCreateModal = () => {
        setEditingCareer(null);
        careerForm.reset();
        careerForm.clearErrors();
        setIsFormModalOpen(true);
    };

    const openEditModal = (c) => {
        setEditingCareer(c);
        careerForm.clearErrors();
        careerForm.setData({
            name: c.name || '',
            code: c.code || '',
            degree_title: c.degree_title || '',
            total_credits: c.total_credits || 120,
            duration_periods: c.duration_periods || 10,
            registration_fee: c.registration_fee || 0,
            monthly_fee: c.monthly_fee || 0,
            credit_price: c.credit_price || 0,
            description: c.description || '',
            is_sequential: Boolean(c.is_sequential),
            status: c.status || 'Activo',
        });
        setIsFormModalOpen(true);
    };

    const handleSaveCareer = (e) => {
        e.preventDefault();
        if (editingCareer) {
            careerForm.put(`/admin/careers/${editingCareer.id}`, {
                preserveScroll: true,
                onSuccess: () => setIsFormModalOpen(false),
            });
        } else {
            careerForm.post('/admin/careers', {
                preserveScroll: true,
                onSuccess: () => setIsFormModalOpen(false),
            });
        }
    };

    const confirmDelete = (career) => {
        setCareerToDelete(career);
        setIsDeleteModalOpen(true);
    };

    const handleDelete = () => {
        if (!careerToDelete) return;
        router.delete(`/admin/careers/${careerToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setCareerToDelete(null);
            },
        });
    };

    return (
        <AuthenticatedLayout title="Carreras Universitarias">
            <Head title="Carreras Universitarias — SGA" />

            <div className="space-y-6">
                {/* Encabezado */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Carreras Universitarias
                        </h1>
                        
                    </div>
                    <div className="flex gap-3">
                        <Link
                            href="/admin/courses"
                            className="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                        >
                            ← Ver Cursos Técnicos
                        </Link>
                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="inline-flex items-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nueva Carrera
                        </button>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total de Carreras</p>
                        <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">{metrics?.total_careers || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Carreras Activas</p>
                        <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{metrics?.active_careers || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Asignaturas en Pensum</p>
                        <p className="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{metrics?.total_curriculum_subjects || 0}</p>
                    </div>
                </div>

                {/* Buscador */}
                <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <form onSubmit={handleSearch} className="flex gap-3">
                        <div className="relative flex-1">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar carrera por nombre, código o título expedido..."
                                className="w-full rounded-xl border-0 py-2.5 pl-10 pr-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900/50 dark:text-white dark:ring-gray-700"
                            />
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                        <button
                            type="submit"
                            className="rounded-xl bg-brand-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark transition"
                        >
                            Buscar
                        </button>
                    </form>
                </div>

                {/* Grid de Carreras */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {careers?.data && careers.data.length > 0 ? (
                        careers.data.map((c) => (
                            <div
                                key={c.id}
                                className="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm hover:shadow-md transition dark:border-gray-800 dark:bg-[#161622] flex flex-col justify-between"
                            >
                                <div className="p-6">
                                    <div className="flex items-start justify-between">
                                        <span className="font-mono text-xs font-bold text-brand-primary dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2.5 py-1 rounded-lg">
                                            {c.code}
                                        </span>
                                        <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            {c.status || 'Activo'}
                                        </span>
                                    </div>

                                    <h3 className="text-lg font-bold text-gray-900 dark:text-white mt-3">
                                        {c.name}
                                    </h3>
                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Título: <strong className="text-gray-800 dark:text-gray-200">{c.degree_title || 'Licenciatura / Ingeniería'}</strong>
                                    </p>

                                    <div className="mt-5 grid grid-cols-2 gap-3 text-xs bg-gray-50 dark:bg-gray-900/40 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-800">
                                        <div>
                                            <span className="text-gray-400 text-[11px]">Créditos Totales</span>
                                            <p className="font-bold text-gray-900 dark:text-white font-mono mt-0.5">{c.total_credits || 0} cr</p>
                                        </div>
                                        <div>
                                            <span className="text-gray-400 text-[11px]">Duración</span>
                                            <p className="font-bold text-gray-900 dark:text-white mt-0.5">{c.duration_periods || 0} Períodos</p>
                                        </div>
                                        <div>
                                            <span className="text-gray-400 text-[11px]">Asignaturas Pensum</span>
                                            <p className="font-bold text-brand-primary dark:text-blue-400 mt-0.5">{c.modules_count || 0} materias</p>
                                        </div>
                                        <div>
                                            <span className="text-gray-400 text-[11px]">Precio por Crédito</span>
                                            <p className="font-bold text-gray-900 dark:text-white font-mono mt-0.5">DOP ${Number(c.credit_price || 0).toLocaleString()}</p>
                                        </div>
                                    </div>
                                </div>

                                <div className="p-4 bg-gray-50/50 dark:bg-gray-900/20 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <button
                                            type="button"
                                            onClick={() => openEditModal(c)}
                                            className="text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                        >
                                            Editar
                                        </button>
                                        <span>•</span>
                                        <button
                                            type="button"
                                            onClick={() => confirmDelete(c)}
                                            className="text-xs font-semibold text-red-500 hover:underline"
                                        >
                                            Eliminar
                                        </button>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <a
                                            href={`/admin/careers/${c.id}/curriculum/pdf`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="Descargar Pensum PDF"
                                            className="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                        >
                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                        <Link
                                            href={`/admin/careers/${c.id}/curriculum`}
                                            className="inline-flex items-center gap-1.5 rounded-xl bg-brand-primary hover:bg-brand-dark px-3.5 py-1.5 text-xs font-bold text-white shadow-sm transition"
                                        >
                                            Malla Curricular →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="col-span-3 py-16 text-center text-gray-400">
                            No se encontraron carreras universitarias registradas.
                        </div>
                    )}
                </div>

                {/* Paginación */}
                {careers?.links && careers.links.length > 3 && (
                    <div className="flex items-center justify-between border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-[#161622] rounded-2xl">
                        <div className="text-xs text-gray-500">
                            Mostrando {careers.from || 0} a {careers.to || 0} de {careers.total} carreras
                        </div>
                        <div className="flex gap-1">
                            {careers.links.map((link, idx) => (
                                <Link
                                    key={idx}
                                    href={link.url || '#'}
                                    className={`px-3 py-1 text-xs rounded-lg font-medium transition ${
                                        link.active ? 'bg-brand-primary text-white' : 'text-gray-600 hover:bg-gray-100'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* MODAL CREAR / EDITAR CARRERA */}
            {isFormModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-2xl bg-white dark:bg-[#161622] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-gray-800 my-8">
                        <div className="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                {editingCareer ? 'Editar Carrera Universitaria' : 'Nueva Carrera de Grado'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsFormModalOpen(false)}
                                className="rounded-xl p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleSaveCareer} className="mt-6 space-y-4">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="sm:col-span-2">
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre de la Carrera *</label>
                                    <input
                                        type="text"
                                        value={careerForm.data.name}
                                        onChange={(e) => careerForm.setData('name', e.target.value)}
                                        placeholder="Ej: Licenciatura en Desarrollo de Software"
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                    {careerForm.errors.name && <p className="text-xs text-red-500 mt-1">{careerForm.errors.name}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Código Oficial *</label>
                                    <input
                                        type="text"
                                        value={careerForm.data.code}
                                        onChange={(e) => careerForm.setData('code', e.target.value)}
                                        placeholder="Ej: IDS-01"
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                    {careerForm.errors.code && <p className="text-xs text-red-500 mt-1">{careerForm.errors.code}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Título Expedido *</label>
                                    <input
                                        type="text"
                                        value={careerForm.data.degree_title}
                                        onChange={(e) => careerForm.setData('degree_title', e.target.value)}
                                        placeholder="Ej: Ingeniero de Software"
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Créditos Totales *</label>
                                    <input
                                        type="number"
                                        value={careerForm.data.total_credits}
                                        onChange={(e) => careerForm.setData('total_credits', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Duración (Cuatrimestres) *</label>
                                    <input
                                        type="number"
                                        value={careerForm.data.duration_periods}
                                        onChange={(e) => careerForm.setData('duration_periods', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Arancel Inscripción (DOP)</label>
                                    <input
                                        type="number"
                                        value={careerForm.data.registration_fee}
                                        onChange={(e) => careerForm.setData('registration_fee', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Precio por Crédito (DOP)</label>
                                    <input
                                        type="number"
                                        value={careerForm.data.credit_price}
                                        onChange={(e) => careerForm.setData('credit_price', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                            </div>

                            <div className="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsFormModalOpen(false)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={careerForm.processing}
                                    className="rounded-xl bg-brand-primary px-6 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50"
                                >
                                    {careerForm.processing ? 'Guardando...' : editingCareer ? 'Actualizar Carrera' : 'Crear Carrera'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL CONFIRMAR ELIMINACIÓN */}
            {isDeleteModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-md bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800 text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 mb-4">
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                            ¿Eliminar Carrera {careerToDelete?.name}?
                        </h3>
                        <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Esta acción eliminará el plan de estudios y todas las materias vinculadas.
                        </p>
                        <div className="mt-6 flex gap-3">
                            <button
                                type="button"
                                onClick={() => setIsDeleteModalOpen(false)}
                                className="flex-1 rounded-xl border border-gray-300 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={handleDelete}
                                className="flex-1 rounded-xl bg-red-600 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700"
                            >
                                Sí, Eliminar
                            </button>
                        </div>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
