import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

export default function ExpensesIndex({
    expenses,
    kpis,
    filters,
    expenseAccounts,
    paymentAccounts,
    assetAccounts,
    journals,
    expenseTypes606,
}) {
    const [search, setSearch] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.status_filter || '');
    const [ncfFilter, setNcfFilter] = useState(filters?.ncf_filter || '');
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [isPayModalOpen, setIsPayModalOpen] = useState(false);
    const [expenseToPay, setExpenseToPay] = useState(null);
    const [isLookingUpRnc, setIsLookingUpRnc] = useState(false);
    const [rncError, setRncError] = useState('');

    // Formulario de Creación de Gasto
    const createForm = useForm({
        supplier_rnc: '',
        supplier_name: '',
        reference_number: '',
        ncf_mode: 'supplier',
        ncf: '',
        expense_type_606: '02',
        expense_date: new Date().toISOString().split('T')[0],
        due_date: '',
        expense_account_id: expenseAccounts?.[0]?.id || '',
        payment_account_id: paymentAccounts?.[0]?.id || '',
        subtotal: '',
        itbis_amount: '',
        itbis_retained: '',
        isr_retained: '',
        status: 'paid',
        selected_journal_id: journals?.[0]?.id || '',
        description: '',
    });

    // Formulario para Saldar Factura (Pay Modal)
    const payForm = useForm({
        expense_id: '',
        pay_account_origin_id: assetAccounts?.[0]?.id || '',
        selected_journal_id: journals?.[0]?.id || '',
    });

    const handleFilter = (newSearch, newStatus, newNcfFilter = ncfFilter) => {
        router.get('/admin/finance/expenses', {
            search: newSearch ?? search,
            status_filter: newStatus ?? statusFilter,
            ncf_filter: newNcfFilter,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Cálculo automático del neto en el formulario
    const sub = parseFloat(createForm.data.subtotal) || 0;
    const itbis = parseFloat(createForm.data.itbis_amount) || 0;
    const retItbis = parseFloat(createForm.data.itbis_retained) || 0;
    const retIsr = parseFloat(createForm.data.isr_retained) || 0;
    const calculatedTotal = Math.max(0, sub + itbis - retItbis - retIsr);

    const handleRncLookup = async () => {
        setRncError('');
        const cleanRnc = createForm.data.supplier_rnc.replace(/[^0-9]/g, '');
        if (cleanRnc.length < 9) {
            setRncError('El RNC o Cédula debe tener al menos 9 dígitos.');
            return;
        }

        setIsLookingUpRnc(true);
        try {
            const res = await fetch(`/admin/finance/expenses/lookup-rnc?rnc=${cleanRnc}`);
            const data = await res.json();
            if (data.found && data.name) {
                createForm.setData((prev) => ({
                    ...prev,
                    supplier_name: data.name,
                }));
            } else {
                setRncError(data.message || 'RNC no encontrado.');
            }
        } catch (err) {
            setRncError('No se pudo verificar el RNC en el servicio de la DGII.');
        } finally {
            setIsLookingUpRnc(false);
        }
    };

    const handleCreateSubmit = (e) => {
        e.preventDefault();
        createForm.post('/admin/finance/expenses', {
            preserveScroll: true,
            onSuccess: () => {
                setIsCreateModalOpen(false);
                createForm.reset();
            },
        });
    };

    const openPayModal = (expense) => {
        setExpenseToPay(expense);
        payForm.setData({
            expense_id: expense.id,
            pay_account_origin_id: assetAccounts?.[0]?.id || '',
            selected_journal_id: journals?.[0]?.id || '',
        });
        setIsPayModalOpen(true);
    };

    const handlePaySubmit = (e) => {
        e.preventDefault();
        payForm.post('/admin/finance/expenses/pay', {
            preserveScroll: true,
            onSuccess: () => {
                setIsPayModalOpen(false);
                setExpenseToPay(null);
            },
        });
    };

    return (
        <AuthenticatedLayout title="Cuentas por Pagar & Gastos">
            <Head title="Cuentas por Pagar & Gastos - SGA" />

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
                                <span className="text-gray-600 dark:text-gray-300">Cuentas por Pagar</span>
                            </div>
                            <div className="flex flex-wrap items-center gap-2.5">
                                <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div className="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                        </svg>
                                    </div>
                                    Cuentas por Pagar & Gastos Operativos
                                </h1>
                                <PoweredByBills variant="badge" />
                            </div>
                            
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={() => setIsCreateModalOpen(true)}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuevo Gasto
                            </button>
                        </div>
                    </div>
                </header>

                {/* ── TARJETAS DE KPIS (1:1 Gridbase Bills) ── */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-800 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-gray-500 dark:text-gray-400">Total Egresado</span>
                            <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-3">
                            <span className="text-lg sm:text-xl font-bold font-mono text-gray-900 dark:text-white">
                                RD$ {(kpis?.totalExpenses || 0).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                            <p className="text-[11px] text-gray-400 mt-0.5">Gastos del Mes: RD$ {(kpis?.thisMonthExpenses || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}</p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-800 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-gray-500 dark:text-gray-400">ITBIS Adelantado (606)</span>
                            <div className="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-3">
                            <span className="text-lg sm:text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400">
                                RD$ {(kpis?.totalItbis || 0).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                            <p className="text-[11px] text-gray-400 mt-0.5">Deducible en declaración IT-1</p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-800 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-gray-500 dark:text-gray-400">Pagado / Contado</span>
                            <div className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-3">
                            <span className="text-lg sm:text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                RD$ {(kpis?.totalPaid || 0).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                            <p className="text-[11px] text-gray-400 mt-0.5">Egresos desembolsados</p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-800 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-gray-500 dark:text-gray-400">Pendiente (CxP)</span>
                            <div className="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-3">
                            <span className="text-lg sm:text-xl font-bold font-mono text-amber-600 dark:text-amber-400">
                                RD$ {(kpis?.totalPending || 0).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </span>
                            <p className="text-[11px] text-gray-400 mt-0.5">{kpis?.pendingCount || 0} facturas por saldar</p>
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-800 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-gray-500 dark:text-gray-400">Emitidos E41 / E43</span>
                            <div className="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                        <div className="mt-3">
                            <span className="text-lg sm:text-xl font-bold font-mono text-purple-600 dark:text-purple-400">
                                {(kpis?.e41Count || 0) + (kpis?.e43Count || 0)}
                            </span>
                            <p className="text-[11px] text-gray-400 mt-0.5">
                                {kpis?.e41Count || 0} Compras E41 · {kpis?.e43Count || 0} Gasto Menor E43
                            </p>
                        </div>
                    </div>
                </div>

                {/* ── BARRA DE BÚSQUEDA Y FILTRO ── */}
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
                                    handleFilter(e.target.value, statusFilter);
                                }}
                                placeholder="Buscar por Proveedor, RNC, NCF o descripción..."
                                className="w-full pl-9 pr-8 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 placeholder-gray-400 transition"
                            />
                            {search && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearch('');
                                        handleFilter('', statusFilter);
                                    }}
                                    className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            )}
                        </div>

                        <div className="w-full sm:w-auto flex flex-wrap items-center gap-2.5">
                            <select
                                value={ncfFilter}
                                onChange={(e) => {
                                    setNcfFilter(e.target.value);
                                    handleFilter(search, statusFilter, e.target.value);
                                }}
                                className="px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-800 dark:text-gray-200 font-medium"
                            >
                                <option value="">Todos los Comprobantes</option>
                                <option value="e41">Compras Informal (E41 / B11)</option>
                                <option value="e43">Gastos Menores (E43 / B13)</option>
                            </select>

                            <select
                                value={statusFilter}
                                onChange={(e) => {
                                    setStatusFilter(e.target.value);
                                    handleFilter(search, e.target.value, ncfFilter);
                                }}
                                className="px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-800 dark:text-gray-200 font-medium"
                            >
                                <option value="">Todos los Estados</option>
                                <option value="paid">Pagados / Contado</option>
                                <option value="pending">Pendientes de Pago (Crédito)</option>
                                <option value="void">Anulados</option>
                            </select>

                            <span className="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Total: <strong className="text-gray-900 dark:text-white">{expenses?.total || 0}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                {/* ── TABLA DE GASTOS ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-xs text-left">
                            <thead className="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50/70 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th className="px-6 py-3.5">Proveedor / Detalle</th>
                                    <th className="px-6 py-3.5">NCF / Ref</th>
                                    <th className="px-6 py-3.5">Cuentas Contables</th>
                                    <th className="px-6 py-3.5">Fecha / Vence</th>
                                    <th className="px-6 py-3.5 text-right">Monto Neto</th>
                                    <th className="px-6 py-3.5 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                                {expenses?.data && expenses.data.length > 0 ? (
                                    expenses.data.map((expense) => (
                                        <tr key={expense.id} className="hover:bg-gray-50/60 dark:hover:bg-gray-800/20 transition-colors">
                                            {/* Proveedor / Detalle */}
                                            <td className="px-6 py-4">
                                                <div className="font-bold text-gray-900 dark:text-white text-sm">
                                                    {expense.supplier_name}
                                                </div>
                                                <div className="flex items-center gap-2 mt-0.5">
                                                    {expense.supplier_rnc && (
                                                        <span className="text-[11px] font-mono text-gray-400">
                                                            RNC: {expense.supplier_rnc}
                                                        </span>
                                                    )}
                                                    {expense.description && (
                                                        <span className="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                                            • {expense.description}
                                                        </span>
                                                    )}
                                                </div>
                                            </td>

                                            {/* NCF / Ref */}
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-1.5">
                                                    <span className="font-mono font-bold text-gray-900 dark:text-gray-100 text-xs">
                                                        {expense.ncf || <span className="text-gray-400 font-normal">Sin NCF</span>}
                                                    </span>
                                                    {expense.is_self_issued_ecf && (
                                                        <a
                                                            href={`/admin/finance/expenses/${expense.id}/xml`}
                                                            title="Descargar XML e-CF Emitido (E41/E43)"
                                                            className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 hover:bg-purple-200 transition"
                                                        >
                                                            XML
                                                        </a>
                                                    )}
                                                </div>
                                                <div className="font-mono text-[10px] text-gray-400 mt-0.5">
                                                    Ref: {expense.reference_number || 'N/A'} • Tipo {expense.expense_type_606}
                                                </div>
                                            </td>

                                            {/* Cuentas Contables */}
                                            <td className="px-6 py-4">
                                                <div className="flex flex-col gap-1 text-[11px]">
                                                    <span className="inline-flex items-center gap-1 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-900/40">
                                                        <strong className="text-blue-500">Db:</strong> {expense.expense_account_name}
                                                    </span>
                                                    <span className="inline-flex items-center gap-1 text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded border border-amber-100 dark:border-amber-900/40">
                                                        <strong className="text-amber-500">Cr:</strong> {expense.payment_account_name}
                                                    </span>
                                                </div>
                                            </td>

                                            {/* Fecha / Vence */}
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="font-medium text-gray-900 dark:text-white">
                                                    {expense.expense_date_formatted}
                                                </div>
                                                {expense.due_date_formatted && (
                                                    <div className="text-[10px] text-red-500 font-semibold flex items-center gap-1 mt-0.5">
                                                        <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Vence: {expense.due_date_formatted}
                                                    </div>
                                                )}
                                            </td>

                                            {/* Monto Neto */}
                                            <td className="px-6 py-4 text-right whitespace-nowrap">
                                                <div className="font-bold text-gray-900 dark:text-white text-sm">
                                                    RD$ {expense.total_amount.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                </div>
                                                {expense.itbis_amount > 0 && (
                                                    <div className="text-[10px] text-gray-400 mt-0.5">
                                                        ITBIS: RD$ {expense.itbis_amount.toFixed(2)}
                                                    </div>
                                                )}
                                            </td>

                                            {/* Estado & Acción */}
                                            <td className="px-6 py-4 text-center whitespace-nowrap">
                                                {expense.status === 'paid' && (
                                                    <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                                                        Pagado
                                                    </span>
                                                )}

                                                {expense.status === 'pending' && (
                                                    <div className="flex flex-col items-center gap-1.5">
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                                            Pendiente
                                                        </span>
                                                        <button
                                                            type="button"
                                                            onClick={() => openPayModal(expense)}
                                                            className="inline-flex items-center gap-1 px-2.5 py-1 bg-brand-primary hover:bg-brand-dark text-white rounded-lg font-bold text-[10px] shadow-xs transition active:scale-95"
                                                        >
                                                            <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                            Saldar
                                                        </button>
                                                    </div>
                                                )}

                                                {expense.status === 'void' && (
                                                    <span className="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                                                        Anulado
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-gray-400">
                                            <div className="flex flex-col items-center justify-center">
                                                <svg className="h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                                </svg>
                                                <p className="font-semibold text-gray-600 dark:text-gray-300">No se encontraron gastos registrados.</p>
                                                <p className="text-xs text-gray-400 mt-1">Registra facturas de compras con el botón superior.</p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* ── PAGINACIÓN ── */}
                    {expenses?.links && expenses.links.length > 3 && (
                        <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <span className="text-xs text-gray-500 dark:text-gray-400">
                                Página {expenses.current_page} de {expenses.last_page}
                            </span>
                            <div className="flex items-center gap-1">
                                {expenses.links.map((link, idx) => {
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

            {/* ── MODAL: REGISTRAR NUEVO GASTO / FACTURA ── */}
            {isCreateModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-2xl w-full p-6 transition-all my-8 max-h-[90vh] overflow-y-auto">
                        <div className="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-5">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                                Registrar Factura / Gasto Operativo
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

                        <form onSubmit={handleCreateSubmit} className="space-y-4">
                            {/* RNC y Nombre del Proveedor */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        RNC o Cédula (Suplidor) <span className="text-red-500">*</span>
                                    </label>
                                    <div className="flex gap-1.5">
                                        <input
                                            type="text"
                                            required
                                            value={createForm.data.supplier_rnc}
                                            onChange={(e) => createForm.setData('supplier_rnc', e.target.value)}
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    handleRncLookup();
                                                }
                                            }}
                                            placeholder="Ej: 101000000"
                                            className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                        />
                                        <button
                                            type="button"
                                            onClick={handleRncLookup}
                                            disabled={isLookingUpRnc}
                                            className="px-3 py-2 bg-brand-primary hover:bg-brand-dark text-white rounded-xl text-xs font-bold shrink-0 transition disabled:opacity-50"
                                            title="Buscar en padrón DGII"
                                        >
                                            {isLookingUpRnc ? '...' : 'DGII'}
                                        </button>
                                    </div>
                                    {rncError && <p className="text-red-500 text-[11px] mt-1">{rncError}</p>}
                                    {createForm.errors.supplier_rnc && <p className="text-red-500 text-[11px] mt-1">{createForm.errors.supplier_rnc}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Nombre / Razón Social <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        value={createForm.data.supplier_name}
                                        onChange={(e) => createForm.setData('supplier_name', e.target.value)}
                                        placeholder="Ej: Suplidora Dominicana SRL"
                                        className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                    />
                                    {createForm.errors.supplier_name && <p className="text-red-500 text-[11px] mt-1">{createForm.errors.supplier_name}</p>}
                                </div>
                            </div>

                            {/* Modo de Comprobante Fiscal (Suplidor vs Auto-Emisión E41 / E43) */}
                            <div className="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 space-y-2.5">
                                <label className="block text-[11px] font-bold uppercase tracking-wider text-indigo-800 dark:text-indigo-300">
                                    Modalidad de Comprobante Fiscal (DGII 606 / e-CF)
                                </label>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    {[
                                        { id: 'supplier', title: 'NCF del Suplidor', sub: 'Digitar B01 / E31 recibido' },
                                        { id: 'e41', title: 'Auto-Emitir E41', sub: 'Proveedor Informal (B11/E41)' },
                                        { id: 'e43', title: 'Auto-Emitir E43', sub: 'Gasto Menor Caja Chica (B13/E43)' },
                                    ].map((m) => {
                                        const active = createForm.data.ncf_mode === m.id;
                                        return (
                                            <button
                                                key={m.id}
                                                type="button"
                                                onClick={() => createForm.setData('ncf_mode', m.id)}
                                                className={`text-left p-2.5 rounded-xl border transition ${
                                                    active
                                                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs'
                                                        : 'bg-white dark:bg-[#161622] text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-indigo-400'
                                                }`}
                                            >
                                                <div className="text-xs font-bold">{m.title}</div>
                                                <div className={`text-[10px] ${active ? 'text-indigo-100' : 'text-gray-400'}`}>
                                                    {m.sub}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* NCF, Tipo 606 y Factura Ref */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        NCF (Comprobante Fiscal)
                                    </label>
                                    {createForm.data.ncf_mode === 'supplier' ? (
                                        <input
                                            type="text"
                                            value={createForm.data.ncf}
                                            onChange={(e) => createForm.setData('ncf', e.target.value.toUpperCase())}
                                            placeholder="Ej: E310000000001 o B0100000001"
                                            className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-mono"
                                        />
                                    ) : (
                                        <div className="w-full px-3 py-2 text-xs bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl font-mono font-bold text-purple-700 dark:text-purple-300">
                                            {createForm.data.ncf_mode === 'e41'
                                                ? '⚡ Se asignará secuencia E41 / B11'
                                                : '⚡ Se asignará secuencia E43 / B13'}
                                        </div>
                                    )}
                                    {createForm.errors.ncf && <p className="text-red-500 text-[11px] mt-1">{createForm.errors.ncf}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Tipo 606 (DGII) <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={createForm.data.expense_type_606}
                                        onChange={(e) => createForm.setData('expense_type_606', e.target.value)}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                    >
                                        {Object.entries(expenseTypes606 || {}).map(([code, label]) => (
                                            <option key={code} value={code}>
                                                {code} - {label}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Factura # Interna / Ref
                                    </label>
                                    <input
                                        type="text"
                                        value={createForm.data.reference_number}
                                        onChange={(e) => createForm.setData('reference_number', e.target.value)}
                                        placeholder="Ej: F-1025"
                                        className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-mono"
                                    />
                                </div>
                            </div>

                            {/* Desglose Impositivo DGII */}
                            <div className="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200/70 dark:border-gray-700/50 space-y-3">
                                <div className="text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    Desglose Impositivo & Retenciones
                                </div>

                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Subtotal <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            value={createForm.data.subtotal}
                                            onChange={(e) => createForm.setData('subtotal', e.target.value)}
                                            placeholder="0.00"
                                            className="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#161622] border border-gray-200 dark:border-gray-700/60 rounded-lg text-right font-bold text-gray-900 dark:text-white"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            ITBIS Facturado
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={createForm.data.itbis_amount}
                                            onChange={(e) => createForm.setData('itbis_amount', e.target.value)}
                                            placeholder="0.00"
                                            className="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#161622] border border-gray-200 dark:border-gray-700/60 rounded-lg text-right font-bold text-gray-900 dark:text-white"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-orange-700 dark:text-orange-400 mb-1">
                                            Ret. ITBIS
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={createForm.data.itbis_retained}
                                            onChange={(e) => createForm.setData('itbis_retained', e.target.value)}
                                            placeholder="0.00"
                                            className="w-full px-3 py-1.5 text-xs bg-orange-50/50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800/40 rounded-lg text-right font-bold text-orange-900 dark:text-orange-300"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-orange-700 dark:text-orange-400 mb-1">
                                            Ret. ISR
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={createForm.data.isr_retained}
                                            onChange={(e) => createForm.setData('isr_retained', e.target.value)}
                                            placeholder="0.00"
                                            className="w-full px-3 py-1.5 text-xs bg-orange-50/50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800/40 rounded-lg text-right font-bold text-orange-900 dark:text-orange-300"
                                        />
                                    </div>
                                </div>

                                <div className="p-3 bg-white dark:bg-[#161622] rounded-lg border border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                                    <span className="text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Monto Neto a Desembolsar:
                                    </span>
                                    <span className="text-base font-black text-brand-primary dark:text-blue-400">
                                        RD$ {calculatedTotal.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                    </span>
                                </div>
                            </div>

                            {/* Cuentas Contables y Diario */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Cuenta de Gasto (Débito) <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        required
                                        value={createForm.data.expense_account_id}
                                        onChange={(e) => createForm.setData('expense_account_id', e.target.value)}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                                    >
                                        {expenseAccounts?.map((acc) => (
                                            <option key={acc.id} value={acc.id}>
                                                {acc.code} - {acc.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Cuenta de Pago / CxP (Crédito) <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        required
                                        value={createForm.data.payment_account_id}
                                        onChange={(e) => createForm.setData('payment_account_id', e.target.value)}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                                    >
                                        {paymentAccounts?.map((acc) => (
                                            <option key={acc.id} value={acc.id}>
                                                {acc.code} - {acc.name} ({acc.type === 'asset' ? 'Caja/Banco' : 'Pasivo/CxP'})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Fechas y Diario */}
                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Fecha de Emisión <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        required
                                        value={createForm.data.expense_date}
                                        onChange={(e) => createForm.setData('expense_date', e.target.value)}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Fecha Vencimiento (CxP)
                                    </label>
                                    <input
                                        type="date"
                                        value={createForm.data.due_date}
                                        onChange={(e) => createForm.setData('due_date', e.target.value)}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Estado Inicial <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={createForm.data.status}
                                        onChange={(e) => createForm.setData('status', e.target.value)}
                                        className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100 font-semibold"
                                    >
                                        <option value="paid">Pagado (Al Contado)</option>
                                        <option value="pending">Pendiente (A Crédito / CxP)</option>
                                    </select>
                                </div>
                            </div>

                            {/* Diario Contable */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Diario Contable <span className="text-red-500">*</span>
                                </label>
                                <select
                                    required
                                    value={createForm.data.selected_journal_id}
                                    onChange={(e) => createForm.setData('selected_journal_id', e.target.value)}
                                    className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                >
                                    {journals?.map((j) => (
                                        <option key={j.id} value={j.id}>
                                            {j.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Descripción */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Descripción o Justificación del Gasto
                                </label>
                                <textarea
                                    rows="2"
                                    value={createForm.data.description}
                                    onChange={(e) => createForm.setData('description', e.target.value)}
                                    placeholder="Detalles sobre el gasto o personal que lo autorizó..."
                                    className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                />
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
                                    disabled={createForm.processing}
                                    className="px-5 py-2 text-xs font-bold bg-brand-primary hover:bg-brand-dark text-white rounded-xl shadow-xs transition active:scale-95 disabled:opacity-50"
                                >
                                    {createForm.processing ? 'Registrando...' : 'Registrar Gasto y Cuadrar Póliza'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* ── MODAL: SALDAR FACTURA (PAGAR CXP) ── */}
            {isPayModalOpen && expenseToPay && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800 max-w-lg w-full p-6 transition-all">
                        <div className="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-5">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </span>
                                Saldar Factura / Cuenta por Pagar
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsPayModalOpen(false)}
                                className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                            >
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Detalle de la Deuda */}
                        <div className="mb-5 rounded-xl bg-gray-50 dark:bg-gray-800/40 p-4 border border-gray-100 dark:border-gray-800 space-y-2">
                            <div className="flex justify-between text-xs">
                                <span className="text-gray-400">Proveedor:</span>
                                <strong className="text-gray-900 dark:text-white">{expenseToPay.supplier_name}</strong>
                            </div>
                            <div className="flex justify-between text-xs">
                                <span className="text-gray-400">NCF / Factura:</span>
                                <span className="font-mono font-semibold text-gray-800 dark:text-gray-200">
                                    {expenseToPay.ncf || expenseToPay.reference_number || 'N/A'}
                                </span>
                            </div>
                            <div className="flex justify-between text-xs">
                                <span className="text-gray-400">Cuenta Pasivo a Debitar:</span>
                                <span className="font-medium text-amber-700 dark:text-amber-400">
                                    {expenseToPay.payment_account_name}
                                </span>
                            </div>
                            <div className="pt-2 border-t border-gray-200 dark:border-gray-700/60 flex justify-between items-center">
                                <span className="text-xs font-bold text-gray-700 dark:text-gray-300">Total a Saldar:</span>
                                <span className="text-lg font-black text-brand-primary dark:text-blue-400">
                                    RD$ {expenseToPay.total_amount.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                </span>
                            </div>
                        </div>

                        <form onSubmit={handlePaySubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Cuenta de Origen de Fondos (Caja o Banco - Activo) <span className="text-red-500">*</span>
                                </label>
                                <select
                                    required
                                    value={payForm.data.pay_account_origin_id}
                                    onChange={(e) => payForm.setData('pay_account_origin_id', e.target.value)}
                                    className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                                >
                                    {assetAccounts?.map((acc) => (
                                        <option key={acc.id} value={acc.id}>
                                            {acc.code} - {acc.name}
                                        </option>
                                    ))}
                                </select>
                                {payForm.errors.pay_account_origin_id && <p className="text-red-500 text-[11px] mt-1">{payForm.errors.pay_account_origin_id}</p>}
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Diario Contable <span className="text-red-500">*</span>
                                </label>
                                <select
                                    required
                                    value={payForm.data.selected_journal_id}
                                    onChange={(e) => payForm.setData('selected_journal_id', e.target.value)}
                                    className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                                >
                                    {journals?.map((j) => (
                                        <option key={j.id} value={j.id}>
                                            {j.name}
                                        </option>
                                    ))}
                                </select>
                                {payForm.errors.selected_journal_id && <p className="text-red-500 text-[11px] mt-1">{payForm.errors.selected_journal_id}</p>}
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsPayModalOpen(false)}
                                    className="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={payForm.processing}
                                    className="px-5 py-2 text-xs font-bold bg-brand-primary hover:bg-brand-dark text-white rounded-xl shadow-xs transition active:scale-95 disabled:opacity-50"
                                >
                                    {payForm.processing ? 'Procesando Pago...' : 'Procesar Asiento y Saldar Factura'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
