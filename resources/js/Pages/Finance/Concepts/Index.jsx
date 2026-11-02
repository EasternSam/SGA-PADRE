import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

export default function PaymentConceptsIndex({ concepts, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [editingConcept, setEditingConcept] = useState(null);
    const [conceptToDelete, setConceptToDelete] = useState(null);
    const [isMassDeleteModalOpen, setIsMassDeleteModalOpen] = useState(false);

    // Formulario para Crear / Editar Concepto
    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        id: '',
        name: '',
        description: '',
        amount: '0.00',
        is_fixed_amount: false,
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/finance/payment-concepts', { search }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleClearSearch = () => {
        setSearch('');
        router.get('/admin/finance/payment-concepts', {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const openCreateModal = () => {
        clearErrors();
        reset();
        setEditingConcept(null);
        setData({
            id: '',
            name: '',
            description: '',
            amount: '0.00',
            is_fixed_amount: false,
        });
        setIsCreateModalOpen(true);
    };

    const openEditModal = (concept) => {
        clearErrors();
        setEditingConcept(concept);
        setData({
            id: concept.id,
            name: concept.name,
            description: concept.description || '',
            amount: concept.amount > 0 ? concept.amount.toFixed(2) : '0.00',
            is_fixed_amount: concept.amount > 0 || concept.is_fixed_amount,
        });
        setIsCreateModalOpen(true);
    };

    const handleSubmitConcept = (e) => {
        e.preventDefault();
        post('/admin/finance/payment-concepts', {
            preserveScroll: true,
            onSuccess: () => {
                setIsCreateModalOpen(false);
                reset();
            },
        });
    };

    const confirmDelete = (concept) => {
        setConceptToDelete(concept);
    };

    const handleDelete = () => {
        if (!conceptToDelete) return;
        router.delete(`/admin/finance/payment-concepts/${conceptToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => setConceptToDelete(null),
        });
    };

    const handleMassDelete = () => {
        router.post('/admin/finance/payment-concepts/mass-delete', {}, {
            preserveScroll: true,
            onSuccess: () => setIsMassDeleteModalOpen(false),
        });
    };

    return (
        <AuthenticatedLayout title="Conceptos de Pago">
            <Head title="Conceptos de Pago - SGA" />

            <div className="space-y-6">
                {/* ── ENCABEZADO SUPERIOR ── */}
                <header className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-6 transition-colors">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                <Link href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</Link>
                                <span>/</span>
                                <Link href="/admin/finance/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Finanzas</Link>
                                <span>/</span>
                                <span className="text-gray-600 dark:text-gray-300">Conceptos de Pago</span>
                            </div>
                            <div className="flex flex-wrap items-center gap-2.5">
                                <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    Conceptos de Pago
                                </h1>
                                <PoweredByBills variant="badge" />
                            </div>
                            
                        </div>

                        <div className="flex flex-wrap items-center gap-2.5">
                            <button
                                type="button"
                                onClick={() => setIsMassDeleteModalOpen(true)}
                                className="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-[#1a1a2e] border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl font-medium text-xs shadow-xs transition active:scale-95"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Limpiar Sin Uso
                            </button>

                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuevo Concepto
                            </button>
                        </div>
                    </div>
                </header>

                {/* ── BARRA DE BÚSQUEDA Y FILTRO ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4">
                    <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <div className="relative w-full sm:max-w-md">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar por concepto o descripción..."
                                className="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 placeholder-gray-400 transition"
                            />
                            {search && (
                                <button
                                    type="button"
                                    onClick={handleClearSearch}
                                    className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            )}
                        </div>

                        <div className="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span>Mostrando <strong className="text-gray-900 dark:text-white">{concepts?.data?.length || 0}</strong> de <strong className="text-gray-900 dark:text-white">{concepts?.total || 0}</strong> conceptos</span>
                            <button
                                type="submit"
                                className="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg text-xs transition"
                            >
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                {/* ── TABLA DE CONCEPTOS ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-xs text-left">
                            <thead className="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50/70 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th className="px-6 py-3.5">Concepto</th>
                                    <th className="px-6 py-3.5">Descripción</th>
                                    <th className="px-6 py-3.5">Monto / Arancel</th>
                                    <th className="px-6 py-3.5 text-center">Tipo</th>
                                    <th className="px-6 py-3.5 text-center">Historial</th>
                                    <th className="px-6 py-3.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                                {concepts?.data && concepts.data.length > 0 ? (
                                    concepts.data.map((concept) => (
                                        <tr key={concept.id} className="hover:bg-gray-50/60 dark:hover:bg-gray-800/20 transition-colors">
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-brand-primary dark:text-blue-400 font-bold text-xs shrink-0">
                                                        {concept.name.charAt(0).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="font-semibold text-gray-900 dark:text-white text-sm">
                                                            {concept.name}
                                                        </div>
                                                        <div className="text-[10px] text-gray-400">
                                                            ID #{concept.id}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td className="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                                                {concept.description || <span className="text-gray-300 dark:text-gray-600 italic">Sin descripción</span>}
                                            </td>

                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {concept.amount > 0 ? (
                                                    <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/40">
                                                        RD$ {concept.amount.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                                        Variable
                                                    </span>
                                                )}
                                            </td>

                                            <td className="px-6 py-4 text-center whitespace-nowrap">
                                                {concept.amount > 0 ? (
                                                    <span className="text-[11px] font-medium text-blue-600 dark:text-blue-400">
                                                        Monto Fijo
                                                    </span>
                                                ) : (
                                                    <span className="text-[11px] font-medium text-amber-600 dark:text-amber-400">
                                                        Al momento del cobro
                                                    </span>
                                                )}
                                            </td>

                                            <td className="px-6 py-4 text-center whitespace-nowrap">
                                                {concept.payments_count > 0 ? (
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-brand-primary dark:text-blue-300 border border-blue-100 dark:border-blue-900/50">
                                                        {concept.payments_count} {concept.payments_count === 1 ? 'pago' : 'pagos'}
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                                                        Sin uso
                                                    </span>
                                                )}
                                            </td>

                                            <td className="px-6 py-4 text-right whitespace-nowrap">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => openEditModal(concept)}
                                                        className="p-1.5 text-gray-500 hover:text-brand-primary dark:text-gray-400 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
                                                        title="Editar concepto"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => confirmDelete(concept)}
                                                        className="p-1.5 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition"
                                                        title="Eliminar concepto"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-gray-400">
                                            <div className="flex flex-col items-center justify-center">
                                                <svg className="h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <p className="font-semibold text-gray-600 dark:text-gray-300">No se encontraron conceptos de pago.</p>
                                                <p className="text-xs text-gray-400 mt-1">Crea un nuevo concepto con el botón superior.</p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* ── PAGINACIÓN ── */}
                    {concepts?.links && concepts.links.length > 3 && (
                        <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <span className="text-xs text-gray-500 dark:text-gray-400">
                                Página {concepts.current_page} de {concepts.last_page}
                            </span>
                            <div className="flex items-center gap-1">
                                {concepts.links.map((link, idx) => {
                                    if (!link.url) {
                                        return (
                                            <span
                                                key={idx}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                className="px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600 rounded-lg cursor-not-allowed"
                                            />
                                        );
                                    }
                                    return (
                                        <Link
                                            key={idx}
                                            href={link.url}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                            className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition ${
                                                link.active
                                                    ? 'bg-brand-primary text-white shadow-xs'
                                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                                            }`}
                                            preserveScroll
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* ── MODAL: CREAR / EDITAR CONCEPTO ── */}
            {isCreateModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-lg w-full p-6 transition-all">
                        <div className="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-5">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                                {editingConcept ? `Editar Concepto #${editingConcept.id}` : 'Nuevo Concepto de Pago'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsCreateModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleSubmitConcept} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre del Concepto <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Ej: Emisión de Diploma, Convalidación de Asignatura..."
                                    className="w-full px-3.5 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                />
                                {errors.name && <p className="text-red-500 text-[11px] mt-1">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Descripción (Opcional)
                                </label>
                                <textarea
                                    rows="3"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Detalles sobre el uso, alcance o justificación de este arancel..."
                                    className="w-full px-3.5 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                />
                                {errors.description && <p className="text-red-500 text-[11px] mt-1">{errors.description}</p>}
                            </div>

                            <div className="bg-gray-50/80 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200/70 dark:border-gray-700/50">
                                <label className="flex items-center gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.is_fixed_amount}
                                        onChange={(e) => setData('is_fixed_amount', e.target.checked)}
                                        className="w-4 h-4 rounded text-brand-primary focus:ring-brand-primary/30 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                    />
                                    <div>
                                        <span className="text-xs font-bold text-gray-900 dark:text-white">¿Tiene un precio institucional fijo?</span>
                                        <p className="text-[11px] text-gray-500 dark:text-gray-400">Si está marcado, se cargará este monto exacto al registrar el cobro en Caja.</p>
                                    </div>
                                </label>

                                {data.is_fixed_amount && (
                                    <div className="mt-3.5 pt-3 border-t border-gray-200/50 dark:border-gray-700/40">
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Monto Fijo (RD$) <span className="text-red-500">*</span>
                                        </label>
                                        <div className="relative">
                                            <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-gray-400">RD$</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required={data.is_fixed_amount}
                                                value={data.amount}
                                                onChange={(e) => setData('amount', e.target.value)}
                                                className="w-full pl-12 pr-4 py-2 text-xs sm:text-sm bg-white dark:bg-[#161622] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-semibold"
                                            />
                                        </div>
                                        {errors.amount && <p className="text-red-500 text-[11px] mt-1">{errors.amount}</p>}
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsCreateModalOpen(false)}
                                    className="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2 text-xs font-bold bg-brand-primary hover:bg-brand-dark text-white rounded-xl shadow-xs transition active:scale-95 disabled:opacity-50"
                                >
                                    {processing ? 'Guardando...' : (editingConcept ? 'Actualizar Concepto' : 'Crear Concepto')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* ── MODAL: CONFIRMAR ELIMINACIÓN INDIVIDUAL ── */}
            {conceptToDelete && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-md w-full p-6">
                        <div className="flex items-start gap-3.5 mb-4">
                            <div className="p-2.5 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 shrink-0">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">¿Eliminar Concepto?</h3>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Estás a punto de eliminar el concepto <strong>"{conceptToDelete.name}"</strong>.
                                </p>
                            </div>
                        </div>

                        {conceptToDelete.payments_count > 0 ? (
                            <div className="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 p-3 rounded-xl mb-4 text-xs text-amber-800 dark:text-amber-300">
                                <strong>Acción Bloqueada:</strong> Este concepto tiene {conceptToDelete.payments_count} transacción(es) registrada(s). Por integridad contable y tributaria, no se permite su eliminación física.
                            </div>
                        ) : (
                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                Esta acción eliminará el concepto permanentemente ya que no posee transacciones vinculadas.
                            </p>
                        )}

                        <div className="flex items-center justify-end gap-2.5 pt-2">
                            <button
                                type="button"
                                onClick={() => setConceptToDelete(null)}
                                className="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                            >
                                Cancelar
                            </button>
                            {conceptToDelete.payments_count === 0 && (
                                <button
                                    type="button"
                                    onClick={handleDelete}
                                    className="px-4 py-2 text-xs font-bold bg-red-600 hover:bg-red-700 text-white rounded-xl shadow-xs transition active:scale-95"
                                >
                                    Sí, Eliminar
                                </button>
                            )}
                        </div>
                    </div>
                </div>, document.body)
            )}

            {/* ── MODAL: CONFIRMAR LIMPIEZA MASIVA ── */}
            {isMassDeleteModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-md w-full p-6">
                        <div className="flex items-start gap-3.5 mb-4">
                            <div className="p-2.5 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 shrink-0">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div>
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">Limpieza de Conceptos Sin Uso</h3>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Esta acción eliminará de forma segura <strong>únicamente</strong> aquellos conceptos que <strong>nunca</strong> han sido utilizados en un pago.
                                </p>
                            </div>
                        </div>

                        <p className="text-xs text-gray-500 dark:text-gray-400 mb-5 bg-gray-50 dark:bg-gray-800/40 p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                            Los conceptos con historial contable se conservarán protegidos. ¿Deseas ejecutar la limpieza?
                        </p>

                        <div className="flex items-center justify-end gap-2.5">
                            <button
                                type="button"
                                onClick={() => setIsMassDeleteModalOpen(false)}
                                className="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={handleMassDelete}
                                className="px-4 py-2 text-xs font-bold bg-red-600 hover:bg-red-700 text-white rounded-xl shadow-xs transition active:scale-95"
                            >
                                Ejecutar Limpieza
                            </button>
                        </div>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
