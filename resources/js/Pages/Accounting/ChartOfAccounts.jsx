import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ChartOfAccounts({ accounts, parentCandidates, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [typeFilter, setTypeFilter] = useState(filters?.type_filter || '');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingAccount, setEditingAccount] = useState(null);
    const [accountToDelete, setAccountToDelete] = useState(null);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        id: '',
        parent_id: '',
        code: '',
        name: '',
        type: 'asset',
        is_active: true,
    });

    const handleFilter = (newSearch, newType) => {
        router.get('/admin/finance/chart-of-accounts', {
            search: newSearch ?? search,
            type_filter: newType ?? typeFilter,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const openCreateModal = () => {
        clearErrors();
        reset();
        setEditingAccount(null);
        setData({
            id: '',
            parent_id: '',
            code: '',
            name: '',
            type: 'asset',
            is_active: true,
        });
        setIsModalOpen(true);
    };

    const openEditModal = (acc) => {
        clearErrors();
        setEditingAccount(acc);
        setData({
            id: acc.id,
            parent_id: acc.parent_id || '',
            code: acc.code,
            name: acc.name,
            type: acc.type,
            is_active: acc.is_active,
        });
        setIsModalOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/finance/chart-of-accounts', {
            preserveScroll: true,
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
            },
        });
    };

    const handleDelete = () => {
        if (!accountToDelete) return;
        router.delete(`/admin/finance/chart-of-accounts/${accountToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => setAccountToDelete(null),
        });
    };

    const getTypeBadge = (type) => {
        switch (type) {
            case 'asset':
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">
                        <span className="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Activo (Deudora)
                    </span>
                );
            case 'liability':
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-800/40">
                        <span className="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Pasivo (Acreedora)
                    </span>
                );
            case 'equity':
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40">
                        <span className="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Capital (Acreedora)
                    </span>
                );
            case 'revenue':
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Ingreso (Acreedora)
                    </span>
                );
            case 'cost':
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-200 dark:border-stone-700">
                        <span className="w-1.5 h-1.5 rounded-full bg-stone-500"></span> Costo (Deudora)
                    </span>
                );
            case 'expense':
                return (
                    <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                        <span className="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Gasto (Deudora)
                    </span>
                );
            default:
                return <span className="text-gray-400 text-xs">{type}</span>;
        }
    };

    return (
        <AuthenticatedLayout title="Catálogo de Cuentas">
            <Head title="Catálogo de Cuentas Contables - SGA" />

            <div className="space-y-6">
                {/* ── ENCABEZADO ── */}
                <header className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-6 transition-colors">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                <Link href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</Link>
                                <span>/</span>
                                <Link href="/admin/finance/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Finanzas</Link>
                                <span>/</span>
                                <span className="text-gray-600 dark:text-gray-300">Catálogo de Cuentas</span>
                            </div>
                            <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                Catálogo de Cuentas Contables
                            </h1>
                            
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                Nueva Cuenta
                            </button>
                        </div>
                    </div>
                </header>

                {/* ── FILTROS Y BÚSQUEDA ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4">
                    <div className="flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <div className="relative w-full sm:max-w-md">
                            <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => {
                                    setSearch(e.target.value);
                                    handleFilter(e.target.value, typeFilter);
                                }}
                                placeholder="Buscar por código (ej. 1.1) o nombre de cuenta..."
                                className="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 placeholder-gray-400 transition"
                            />
                            {search && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearch('');
                                        handleFilter('', typeFilter);
                                    }}
                                    className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            )}
                        </div>

                        <div className="w-full sm:w-auto flex items-center gap-3">
                            <select
                                value={typeFilter}
                                onChange={(e) => {
                                    setTypeFilter(e.target.value);
                                    handleFilter(search, e.target.value);
                                }}
                                className="px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-800 dark:text-gray-200 font-medium"
                            >
                                <option value="">Todas las Naturalezas</option>
                                <option value="asset">Activos (Assets)</option>
                                <option value="liability">Pasivos (Liabilities)</option>
                                <option value="equity">Capital (Equity)</option>
                                <option value="revenue">Ingresos (Revenue)</option>
                                <option value="cost">Costos (Cost)</option>
                                <option value="expense">Gastos (Expense)</option>
                            </select>

                            <span className="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Total: <strong className="text-gray-900 dark:text-white">{accounts?.total || 0}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                {/* ── TABLA JERÁRQUICA ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-xs text-left">
                            <thead className="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50/70 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th className="px-6 py-3.5">Código Estructurado</th>
                                    <th className="px-6 py-3.5">Nombre de la Cuenta Contable</th>
                                    <th className="px-6 py-3.5">Naturaleza (Tipo)</th>
                                    <th className="px-6 py-3.5 text-center">Estado</th>
                                    <th className="px-6 py-3.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                                {accounts?.data && accounts.data.length > 0 ? (
                                    accounts.data.map((account) => {
                                        const dots = (account.code.match(/\./g) || []).length;
                                        const paddingLeft = dots > 0 ? `${dots * 1.5 + 1.5}rem` : '1.5rem';
                                        const isParent = dots <= 1;

                                        return (
                                            <tr
                                                key={account.id}
                                                className={`hover:bg-gray-50/60 dark:hover:bg-gray-800/20 transition-colors ${
                                                    account.is_active ? '' : 'opacity-60 bg-gray-50/40 dark:bg-gray-900/20'
                                                }`}
                                            >
                                                {/* Código con Indentación */}
                                                <td className="py-3.5 pr-4 whitespace-nowrap" style={{ paddingLeft }}>
                                                    <div className="flex items-center gap-2">
                                                        {dots > 0 && (
                                                            <svg className="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        )}
                                                        <span className={`font-mono text-xs ${isParent ? 'font-black text-gray-900 dark:text-white' : 'font-medium text-gray-600 dark:text-gray-400'}`}>
                                                            {account.code}
                                                        </span>
                                                    </div>
                                                </td>

                                                {/* Nombre */}
                                                <td className="px-6 py-3.5">
                                                    <div className={`${isParent ? 'font-bold text-gray-900 dark:text-white text-sm' : 'font-medium text-gray-700 dark:text-gray-300 text-xs'}`}>
                                                        {account.name}
                                                    </div>
                                                    {account.parent_code && (
                                                        <span className="text-[10px] text-gray-400 dark:text-gray-500">
                                                            Subcuenta de: {account.parent_code} - {account.parent_name}
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Naturaleza */}
                                                <td className="px-6 py-3.5 whitespace-nowrap">
                                                    {getTypeBadge(account.type)}
                                                </td>

                                                {/* Estado */}
                                                <td className="px-6 py-3.5 text-center whitespace-nowrap">
                                                    {account.is_active ? (
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                                                            Operativa
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700">
                                                            Inactiva
                                                        </span>
                                                    )}
                                                </td>

                                                {/* Acciones */}
                                                <td className="px-6 py-3.5 text-right whitespace-nowrap">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        <button
                                                            type="button"
                                                            onClick={() => openEditModal(account)}
                                                            className="p-1.5 text-gray-400 hover:text-brand-primary dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
                                                            title="Editar cuenta"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => setAccountToDelete(account)}
                                                            className="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition"
                                                            title="Eliminar cuenta"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <td colSpan="5" className="px-6 py-12 text-center text-gray-400">
                                            <div className="flex flex-col items-center justify-center">
                                                <svg className="h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                <p className="font-semibold text-gray-600 dark:text-gray-300">No se encontraron cuentas contables.</p>
                                                <p className="text-xs text-gray-400 mt-1">Crea una cuenta estructurada con el botón superior.</p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* ── PAGINACIÓN ── */}
                    {accounts?.links && accounts.links.length > 3 && (
                        <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <span className="text-xs text-gray-500 dark:text-gray-400">
                                Página {accounts.current_page} de {accounts.last_page}
                            </span>
                            <div className="flex items-center gap-1">
                                {accounts.links.map((link, idx) => {
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

            {/* ── MODAL: CREAR / EDITAR CUENTA ── */}
            {isModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-lg w-full p-6 transition-all">
                        <div className="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-5">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                                {editingAccount ? `Editar Cuenta: ${editingAccount.code}` : 'Nueva Cuenta Contable'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Cuenta Padre */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Cuenta Padre (Opcional)
                                </label>
                                <select
                                    value={data.parent_id}
                                    onChange={(e) => {
                                        const pId = e.target.value;
                                        setData('parent_id', pId);
                                        const selectedP = parentCandidates?.find((p) => p.id == pId);
                                        if (selectedP) {
                                            setData((prev) => ({ ...prev, parent_id: pId, type: selectedP.type }));
                                        }
                                    }}
                                    className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                >
                                    <option value="">Ninguna (Es una cuenta raíz / principal)</option>
                                    {parentCandidates?.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            {p.code} - {p.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Código y Nombre */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Código Estructurado <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value)}
                                        placeholder="Ej: 1.1.01"
                                        className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-mono"
                                    />
                                    {errors.code && <p className="text-red-500 text-[11px] mt-1">{errors.code}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Naturaleza / Tipo <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        required
                                        value={data.type}
                                        onChange={(e) => setData('type', e.target.value)}
                                        disabled={!!data.parent_id}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 disabled:opacity-60 font-semibold"
                                    >
                                        <option value="asset">Activo (Deudora)</option>
                                        <option value="liability">Pasivo (Acreedora)</option>
                                        <option value="equity">Capital (Acreedora)</option>
                                        <option value="revenue">Ingreso (Acreedora)</option>
                                        <option value="cost">Costo (Deudora)</option>
                                        <option value="expense">Gasto (Deudora)</option>
                                    </select>
                                    {data.parent_id && (
                                        <p className="text-[10px] text-gray-400 mt-1">Heredada automáticamente de la cuenta padre.</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Nombre de la Cuenta <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Ej: Banco de Reservas - Operaciones"
                                    className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                />
                                {errors.name && <p className="text-red-500 text-[11px] mt-1">{errors.name}</p>}
                            </div>

                            <div className="pt-2">
                                <label className="flex items-center gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="w-4 h-4 rounded text-brand-primary focus:ring-brand-primary/30 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"
                                    />
                                    <div>
                                        <span className="text-xs font-bold text-gray-900 dark:text-white">Cuenta Activa / Operativa</span>
                                        <p className="text-[11px] text-gray-400">Permite registrar transacciones y asientos de diario.</p>
                                    </div>
                                </label>
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsModalOpen(false)}
                                    className="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2 text-xs font-bold bg-brand-primary hover:bg-brand-dark text-white rounded-xl shadow-xs transition active:scale-95 disabled:opacity-50"
                                >
                                    {processing ? 'Guardando...' : (editingAccount ? 'Actualizar Cuenta' : 'Crear Cuenta')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* ── MODAL: CONFIRMAR ELIMINACIÓN ── */}
            {accountToDelete && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-md w-full p-6">
                        <div className="flex items-start gap-3.5 mb-4">
                            <div className="p-2.5 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 shrink-0">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">¿Eliminar Cuenta Contable?</h3>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Estás a punto de eliminar la cuenta <strong className="font-mono text-gray-900 dark:text-white">{accountToDelete.code} - {accountToDelete.name}</strong>.
                                </p>
                            </div>
                        </div>

                        {accountToDelete.has_children ? (
                            <div className="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 p-3 rounded-xl mb-4 text-xs text-amber-800 dark:text-amber-300">
                                <strong>Bloqueo:</strong> Esta cuenta contiene subcuentas subordinadas. Debes reasignarlas o eliminarlas previamente.
                            </div>
                        ) : accountToDelete.has_entries ? (
                            <div className="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 p-3 rounded-xl mb-4 text-xs text-amber-800 dark:text-amber-300">
                                <strong>Bloqueo:</strong> Esta cuenta posee asientos contables registrados en el Libro Mayor. No se permite su eliminación por integridad legal. Desactívala en su lugar.
                            </div>
                        ) : (
                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                Esta cuenta no posee movimientos ni subcuentas, por lo que será removida del plan general.
                            </p>
                        )}

                        <div className="flex items-center justify-end gap-2.5 pt-2">
                            <button
                                type="button"
                                onClick={() => setAccountToDelete(null)}
                                className="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                            >
                                Cancelar
                            </button>
                            {!accountToDelete.has_children && !accountToDelete.has_entries && (
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
        </AuthenticatedLayout>
    );
}
