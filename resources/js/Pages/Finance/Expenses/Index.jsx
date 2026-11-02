import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

const EXPENSE_TYPE_LABELS = {
    '01': '01 - Gastos de Personal',
    '02': '02 - Trabajos, Suministros y Servicios',
    '03': '03 - Arrendamientos',
    '04': '04 - Gastos de Activos Fijos',
    '05': '05 - Gastos de Representación',
    '06': '06 - Otras Deducciones Admitidas',
    '07': '07 - Gastos Financieros',
    '08': '08 - Gastos Extraordinarios',
    '09': '09 - Costo de Venta (Compras)',
    '10': '10 - Adquisiciones de Activos',
    '11': '11 - Gastos de Seguros',
};

const PAYMENT_METHODS_606 = [
    { code: '01', label: '01 - Efectivo' },
    { code: '02', label: '02 - Cheques / Transferencias / Depósito' },
    { code: '03', label: '03 - Tarjeta Crédito / Débito' },
    { code: '04', label: '04 - Compra a Crédito (Cuentas por Pagar)' },
    { code: '05', label: '05 - Permuta' },
    { code: '06', label: '06 - Nota de Crédito' },
    { code: '07', label: '07 - Mixto' },
];

function formatCurrency(val) {
    const num = Number(val || 0);
    return `RD$ ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function ExpensesIndex({
    expenses,
    kpis,
    filters,
    expenseAccounts = [],
    paymentAccounts = [],
    assetAccounts = [],
    journals = [],
}) {
    const [view, setView] = useState('list'); // 'list' | 'form'
    const [search, setSearch] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.status_filter || '');
    const [ncfFilter, setNcfFilter] = useState(filters?.ncf_filter || '');
    const [isPayModalOpen, setIsPayModalOpen] = useState(false);
    const [expenseToPay, setExpenseToPay] = useState(null);
    const [isLookingUpRnc, setIsLookingUpRnc] = useState(false);
    const [rncStatus, setRncStatus] = useState(null);
    const [paymentMethod606, setPaymentMethod606] = useState('02');

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
        itbis_amount: '0',
        itbis_retained: '0',
        isr_retained: '0',
        status: 'paid',
        selected_journal_id: journals?.[0]?.id || '',
        description: '',
    });

    const payForm = useForm({
        expense_id: '',
        pay_account_origin_id: assetAccounts?.[0]?.id || '',
        selected_journal_id: journals?.[0]?.id || '',
    });

    const handleFilter = (newSearch, newStatus, newNcfFilter = ncfFilter) => {
        router.get(
            '/admin/finance/expenses',
            {
                search: newSearch ?? search,
                status_filter: newStatus ?? statusFilter,
                ncf_filter: newNcfFilter,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const sub = parseFloat(createForm.data.subtotal) || 0;
    const itbis = parseFloat(createForm.data.itbis_amount) || 0;
    const retItbis = parseFloat(createForm.data.itbis_retained) || 0;
    const retIsr = parseFloat(createForm.data.isr_retained) || 0;
    const calculatedTotal = Math.max(0, sub + itbis - retItbis - retIsr);

    const handleRncLookup = async (rawVal) => {
        const val = (rawVal ?? createForm.data.supplier_rnc).replace(/[^0-9]/g, '');
        if (val.length !== 9 && val.length !== 11) {
            setRncStatus(null);
            return;
        }
        setIsLookingUpRnc(true);
        setRncStatus({ color: 'var(--color-text-muted)', text: 'Consultando padrón DGII...' });
        try {
            const res = await fetch(`/admin/finance/expenses/lookup-rnc?rnc=${val}`);
            const data = await res.json();
            if (data.found && data.name) {
                createForm.setData((prev) => ({
                    ...prev,
                    supplier_name: data.name,
                }));
                setRncStatus({
                    color: '#16a34a',
                    text: `✓ ${data.name} (${data.status || 'ACTIVO'})`,
                });
            } else {
                setRncStatus({
                    color: '#d97706',
                    text: 'RNC no encontrado en padrón local',
                });
            }
        } catch (err) {
            setRncStatus(null);
        } finally {
            setIsLookingUpRnc(false);
        }
    };

    const handleCreateSubmit = (e) => {
        e.preventDefault();
        createForm.post('/admin/finance/expenses', {
            preserveScroll: true,
            onSuccess: () => {
                setView('list');
                createForm.reset();
                setRncStatus(null);
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

    const handleVoidExpense = (expense) => {
        if (!window.confirm('¿Está seguro de eliminar/anular este registro de gasto?')) return;
        router.post(`/admin/finance/expenses/${expense.id}/void`, {}, { preserveScroll: true });
    };

    const listRows = expenses?.data || [];

    return (
        <AuthenticatedLayout title="Control de Gastos y Egresos">
            <Head title="Control de Gastos y Egresos" />

            <div className="bills-scope">
                <div className="workspace-panel">
                    {view === 'list' ? (
                        <>
                            {/* 1:1 Gridbase Bills Header (`expenses.js` line 23) */}
                            <div className="page-header">
                                <div className="page-header-left">
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Control de Gastos y Egresos</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Monitorea compras locales y egresos compatibles con reporte fiscal 606</p>
                                </div>
                                <button
                                    type="button"
                                    className="btn btn-primary"
                                    id="btn-new-expense"
                                    onClick={() => setView('form')}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Registrar Gasto
                                </button>
                            </div>

                            {/* 1:1 Gridbase Bills 3 Stat Cards (`expenses.js` line 71) */}
                            <div className="grid-3" style={{ marginBottom: 'var(--spacing-xl)' }}>
                                <div className="stat-card">
                                    <div className="stat-card-title">Total Egresado</div>
                                    <div className="stat-card-value" id="stat-total-expenses">
                                        {formatCurrency(kpis?.totalExpenses || 0)}
                                    </div>
                                    <div className="stat-card-sub" style={{ color: 'var(--color-text-muted)' }}>
                                        Histórico acumulado
                                    </div>
                                </div>

                                <div className="stat-card">
                                    <div className="stat-card-title">ITBIS Adelantado</div>
                                    <div className="stat-card-value" id="stat-total-itbis" style={{ color: 'var(--color-primary)' }}>
                                        {formatCurrency(kpis?.totalItbis || 0)}
                                    </div>
                                    <div className="stat-card-sub">
                                        Compensable en reporte 606
                                    </div>
                                </div>

                                <div className="stat-card">
                                    <div className="stat-card-title">Gastos del Mes</div>
                                    <div className="stat-card-value" id="stat-month-expenses" style={{ color: 'var(--color-text-primary)' }}>
                                        {formatCurrency(kpis?.thisMonthExpenses || 0)}
                                    </div>
                                    <div className="stat-card-sub">
                                        Mes en curso
                                    </div>
                                </div>
                            </div>

                            {/* 1:1 Gridbase Bills Table Outer (`expenses.js` line 89) */}
                            <div className="table-outer">
                                <div className="table-toolbar" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                                    <div className="search-wrapper" style={{ maxWidth: '320px', display: 'flex', width: '100%' }}>
                                        <svg className="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <circle cx="11" cy="11" r="8" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        </svg>
                                        <input
                                            type="text"
                                            id="expense-search"
                                            className="search-input"
                                            placeholder="Buscar proveedor, RNC o NCF..."
                                            value={search}
                                            onChange={(e) => {
                                                setSearch(e.target.value);
                                                handleFilter(e.target.value, statusFilter);
                                            }}
                                        />
                                    </div>

                                    <div style={{ display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' }}>
                                        <select
                                            className="form-control"
                                            style={{ width: 'auto', height: '36px', padding: '0 28px 0 12px', fontSize: '12.5px' }}
                                            value={ncfFilter}
                                            onChange={(e) => {
                                                setNcfFilter(e.target.value);
                                                handleFilter(search, statusFilter, e.target.value);
                                            }}
                                        >
                                            <option value="">Todos los Comprobantes</option>
                                            <option value="e41">Compras Informal (E41 / B11)</option>
                                            <option value="e43">Gastos Menores (E43 / B13)</option>
                                        </select>

                                        <select
                                            className="form-control"
                                            style={{ width: 'auto', height: '36px', padding: '0 28px 0 12px', fontSize: '12.5px' }}
                                            value={statusFilter}
                                            onChange={(e) => {
                                                setStatusFilter(e.target.value);
                                                handleFilter(search, e.target.value, ncfFilter);
                                            }}
                                        >
                                            <option value="">Todos los Estados</option>
                                            <option value="paid">Pagados / Contado</option>
                                            <option value="pending">Pendientes (CxP)</option>
                                            <option value="void">Anulados</option>
                                        </select>
                                    </div>
                                </div>

                                <div className="table-wrapper">
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>NCF</th>
                                                <th>RNC</th>
                                                <th>Proveedor</th>
                                                <th>Tipo de Gasto</th>
                                                <th className="text-right">Subtotal</th>
                                                <th className="text-right">ITBIS</th>
                                                <th className="text-right">Total</th>
                                                <th className="text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="expenses-tbody">
                                            {listRows.length === 0 ? (
                                                <tr>
                                                    <td colSpan={9} className="text-center text-muted py-24">
                                                        No hay gastos registrados que coincidan
                                                    </td>
                                                </tr>
                                            ) : (
                                                listRows.map((e) => {
                                                    const subtotalVal = Number(e.subtotal ?? (Number(e.total_amount || 0) - Number(e.itbis_amount || 0)));
                                                    const itbisVal = Number(e.itbis_amount || 0);
                                                    const totalVal = Number(e.total_amount || 0);
                                                    return (
                                                        <tr key={e.id}>
                                                            <td>{e.expense_date_formatted || e.expense_date}</td>
                                                            <td>
                                                                {e.ncf ? (
                                                                    <strong style={{ fontFamily: 'monospace' }}>{e.ncf}</strong>
                                                                ) : (
                                                                    <span className="text-muted">N/A</span>
                                                                )}
                                                            </td>
                                                            <td>{e.supplier_rnc || <span className="text-muted">—</span>}</td>
                                                            <td className="font-semibold">
                                                                <div>{e.supplier_name}</div>
                                                                {e.status === 'pending' && (
                                                                    <span className="badge badge-pending" style={{ marginTop: '4px', fontSize: '10px', padding: '2px 8px' }}>
                                                                        Pendiente CxP
                                                                    </span>
                                                                )}
                                                                {e.status === 'void' && (
                                                                    <span className="badge badge-cancelled" style={{ marginTop: '4px', fontSize: '10px', padding: '2px 8px' }}>
                                                                        Anulado
                                                                    </span>
                                                                )}
                                                            </td>
                                                            <td>
                                                                <span className="badge badge-draft" style={{ fontSize: '11px' }}>
                                                                    {EXPENSE_TYPE_LABELS[e.expense_type_606] || `${e.expense_type_606 || '02'} - Gastos`}
                                                                </span>
                                                            </td>
                                                            <td className="text-right">{formatCurrency(subtotalVal)}</td>
                                                            <td className="text-right">{formatCurrency(itbisVal)}</td>
                                                            <td className="text-right font-semibold">{formatCurrency(totalVal)}</td>
                                                            <td className="text-right">
                                                                <div className="row-actions" style={{ opacity: 1 }}>
                                                                    {e.status === 'pending' && (
                                                                        <button
                                                                            type="button"
                                                                            className="btn btn-sm btn-primary"
                                                                            style={{ height: '28px', padding: '0 10px', fontSize: '11px' }}
                                                                            onClick={() => openPayModal(e)}
                                                                            title="Saldar Cuenta por Pagar"
                                                                        >
                                                                            Saldar
                                                                        </button>
                                                                    )}
                                                                    {e.is_self_issued_ecf && (
                                                                        <a
                                                                            href={`/admin/finance/expenses/${e.id}/xml`}
                                                                            className="row-action-btn"
                                                                            title="Descargar XML e-CF (E41/E43)"
                                                                        >
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                                                <polyline points="7 10 12 15 17 10" />
                                                                                <line x1="12" y1="15" x2="12" y2="3" />
                                                                            </svg>
                                                                        </a>
                                                                    )}
                                                                    {e.status !== 'void' && (
                                                                        <button
                                                                            type="button"
                                                                            className="row-action-btn danger"
                                                                            title="Eliminar / Anular"
                                                                            onClick={() => handleVoidExpense(e)}
                                                                        >
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                                <polyline points="3 6 5 6 21 6" />
                                                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                                            </svg>
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    );
                                                })
                                            )}
                                        </tbody>
                                    </table>
                                </div>

                                {expenses?.links && expenses.links.length > 3 && (
                                    <div style={{ padding: '14px 24px', borderTop: '1px solid var(--color-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px' }}>
                                        <span className="text-muted" style={{ fontSize: '12px' }}>
                                            Página {expenses.current_page} de {expenses.last_page} (Total: {expenses.total})
                                        </span>
                                        <div style={{ display: 'flex', gap: '6px' }}>
                                            {expenses.links.map((link, idx) =>
                                                link.url ? (
                                                    <Link
                                                        key={idx}
                                                        href={link.url}
                                                        className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-secondary'}`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                        preserveScroll
                                                    />
                                                ) : null
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </>
                    ) : (
                        /* 1:1 Gridbase Bills Form View (`expenses.js` line 183 `renderForm`) */
                        <>
                            <div style={{ marginBottom: '20px' }}>
                                <button
                                    type="button"
                                    className="btn btn-ghost btn-sm"
                                    id="btn-back-expenses"
                                    onClick={() => setView('list')}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <line x1="19" y1="12" x2="5" y2="12" />
                                        <polyline points="12 19 5 12 12 5" />
                                    </svg>
                                    Control de Gastos
                                </button>
                            </div>

                            <div className="page-header">
                                <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                                    <h1 className="page-title">Registrar Gasto (Egreso)</h1>
                                    <PoweredByBills variant="badge" />
                                </div>
                                <div className="btn-group">
                                    <button
                                        type="button"
                                        className="btn btn-secondary"
                                        id="btn-cancel-expense"
                                        onClick={() => setView('list')}
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </div>

                            <form id="expense-form" className="form-card" onSubmit={handleCreateSubmit}>
                                <div className="grid-2">
                                    <div className="form-group">
                                        <label className="form-label">Razón Social / Proveedor *</label>
                                        <input
                                            type="text"
                                            id="e_supplier_name"
                                            className="form-control"
                                            required
                                            placeholder="Ej. Edesur Dominicana, Altice..."
                                            value={createForm.data.supplier_name}
                                            onChange={(e) => createForm.setData('supplier_name', e.target.value)}
                                        />
                                        {createForm.errors.supplier_name && (
                                            <small style={{ color: '#dc2626', fontSize: '11px' }}>{createForm.errors.supplier_name}</small>
                                        )}
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                            <span>RNC / Cédula Proveedor</span>
                                            {rncStatus && (
                                                <span style={{ fontSize: '11px', fontWeight: 600, color: rncStatus.color }}>
                                                    {rncStatus.text}
                                                </span>
                                            )}
                                        </label>
                                        <div style={{ display: 'flex', gap: '8px' }}>
                                            <input
                                                type="text"
                                                id="e_supplier_rnc"
                                                className="form-control"
                                                placeholder="Escribe el RNC para autocompletar..."
                                                maxLength={11}
                                                value={createForm.data.supplier_rnc}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    createForm.setData('supplier_rnc', val);
                                                    const clean = val.replace(/[^0-9]/g, '');
                                                    if (clean.length === 9 || clean.length === 11) {
                                                        handleRncLookup(clean);
                                                    } else {
                                                        setRncStatus(null);
                                                    }
                                                }}
                                            />
                                            <button
                                                type="button"
                                                className="btn btn-secondary"
                                                onClick={() => handleRncLookup()}
                                                disabled={isLookingUpRnc}
                                                style={{ flexShrink: 0 }}
                                            >
                                                {isLookingUpRnc ? '...' : 'DGII'}
                                            </button>
                                        </div>
                                        <small className="text-muted">Requerido para deducción fiscal en el 606</small>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                            <span>NCF / e-NCF del Gasto</span>
                                            <div className="segmented-control" style={{ padding: '2px' }}>
                                                <button
                                                    type="button"
                                                    className={`segment-item ${createForm.data.ncf_mode === 'supplier' ? 'active' : ''}`}
                                                    style={{ padding: '2px 8px', fontSize: '11px' }}
                                                    onClick={() => createForm.setData('ncf_mode', 'supplier')}
                                                >
                                                    Suplidor
                                                </button>
                                                <button
                                                    type="button"
                                                    className={`segment-item ${createForm.data.ncf_mode === 'e41' ? 'active' : ''}`}
                                                    style={{ padding: '2px 8px', fontSize: '11px' }}
                                                    onClick={() => createForm.setData('ncf_mode', 'e41')}
                                                >
                                                    Emitir E41
                                                </button>
                                                <button
                                                    type="button"
                                                    className={`segment-item ${createForm.data.ncf_mode === 'e43' ? 'active' : ''}`}
                                                    style={{ padding: '2px 8px', fontSize: '11px' }}
                                                    onClick={() => createForm.setData('ncf_mode', 'e43')}
                                                >
                                                    Emitir E43
                                                </button>
                                            </div>
                                        </label>
                                        {createForm.data.ncf_mode === 'supplier' ? (
                                            <input
                                                type="text"
                                                id="e_ncf"
                                                className="form-control"
                                                placeholder="Ej. B0100000005 o E310000000001"
                                                maxLength={13}
                                                style={{ textTransform: 'uppercase', fontFamily: 'monospace' }}
                                                value={createForm.data.ncf}
                                                onChange={(e) => createForm.setData('ncf', e.target.value.toUpperCase())}
                                            />
                                        ) : (
                                            <input
                                                type="text"
                                                className="form-control"
                                                readOnly
                                                style={{ background: 'var(--bg-hover)', fontFamily: 'monospace', fontWeight: 700, color: '#6366f1' }}
                                                value={
                                                    createForm.data.ncf_mode === 'e41'
                                                        ? 'Auto-Emisión e-CF E41 (Compras Proveedor Informal)'
                                                        : 'Auto-Emisión e-CF E43 (Gasto Menor Caja Chica)'
                                                }
                                            />
                                        )}
                                        <small className="text-muted">B01 (11 caracteres) o E31/E41/E43 (13 caracteres)</small>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Fecha de Compra *</label>
                                        <input
                                            type="date"
                                            id="e_expense_date"
                                            className="form-control"
                                            required
                                            value={createForm.data.expense_date}
                                            onChange={(e) => createForm.setData('expense_date', e.target.value)}
                                        />
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Tipo de Gasto (DGII 606) *</label>
                                        <select
                                            id="e_expense_type"
                                            className="form-control"
                                            required
                                            value={createForm.data.expense_type_606}
                                            onChange={(e) => createForm.setData('expense_type_606', e.target.value)}
                                        >
                                            {Object.entries(EXPENSE_TYPE_LABELS).map(([code, label]) => (
                                                <option key={code} value={code}>{label}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Forma de Pago (DGII 606) *</label>
                                        <select
                                            id="e_payment_method"
                                            className="form-control"
                                            required
                                            value={paymentMethod606}
                                            onChange={(e) => {
                                                const val = e.target.value;
                                                setPaymentMethod606(val);
                                                createForm.setData('status', val === '04' ? 'pending' : 'paid');
                                            }}
                                        >
                                            {PAYMENT_METHODS_606.map((pm) => (
                                                <option key={pm.code} value={pm.code}>{pm.label}</option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Monto Subtotal (Neto) *</label>
                                        <input
                                            type="number"
                                            id="e_amount"
                                            className="form-control"
                                            step="0.01"
                                            min="0"
                                            required
                                            placeholder="0.00"
                                            value={createForm.data.subtotal}
                                            onChange={(e) => createForm.setData('subtotal', e.target.value)}
                                        />
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                            <span>Monto ITBIS (Impuesto) *</span>
                                            <button
                                                type="button"
                                                onClick={() => createForm.setData('itbis_amount', (sub * 0.18).toFixed(2))}
                                                style={{ fontSize: '11px', color: '#2563eb', background: 'none', border: 'none', cursor: 'pointer', fontWeight: 600 }}
                                            >
                                                Calcular 18%
                                            </button>
                                        </label>
                                        <input
                                            type="number"
                                            id="e_tax_amount"
                                            className="form-control"
                                            step="0.01"
                                            min="0"
                                            required
                                            value={createForm.data.itbis_amount}
                                            onChange={(e) => createForm.setData('itbis_amount', e.target.value)}
                                        />
                                        <small className="text-muted">Coloca 0 si fue exento de ITBIS</small>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Cuenta Contable de Gasto (Débito)</label>
                                        <select
                                            className="form-control"
                                            value={createForm.data.expense_account_id}
                                            onChange={(e) => createForm.setData('expense_account_id', e.target.value)}
                                        >
                                            {expenseAccounts.map((acc) => (
                                                <option key={acc.id} value={acc.id}>
                                                    {acc.code} - {acc.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Cuenta de Pago / Pasivo (Crédito)</label>
                                        <select
                                            className="form-control"
                                            value={createForm.data.payment_account_id}
                                            onChange={(e) => createForm.setData('payment_account_id', e.target.value)}
                                        >
                                            {paymentAccounts.map((acc) => (
                                                <option key={acc.id} value={acc.id}>
                                                    {acc.code} - {acc.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                                        <label className="form-label">Monto Total Compra (Cálculo Automático)</label>
                                        <input
                                            type="text"
                                            id="e_total_display"
                                            className="form-control"
                                            readOnly
                                            style={{ background: 'var(--bg-hover)', fontWeight: 700, fontSize: '16px' }}
                                            value={formatCurrency(calculatedTotal)}
                                        />
                                    </div>

                                    <div className="form-group" style={{ gridColumn: '1 / -1' }}>
                                        <label className="form-label">Notas / Concepto</label>
                                        <textarea
                                            id="e_notes"
                                            className="form-control"
                                            rows={2}
                                            placeholder="Detalle adicional sobre esta compra..."
                                            value={createForm.data.description}
                                            onChange={(e) => createForm.setData('description', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div style={{ marginTop: '24px', display: 'flex', justifyContent: 'flex-end', gap: '12px' }}>
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={createForm.processing}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                            <polyline points="17 21 17 13 7 13 7 21" />
                                            <polyline points="7 3 7 8 15 8" />
                                        </svg>
                                        {createForm.processing ? 'Guardando...' : 'Registrar Gasto'}
                                    </button>
                                </div>
                            </form>
                        </>
                    )}

                    {/* Modal para Saldar Cuenta por Pagar */}
                    {isPayModalOpen && expenseToPay && (
                        <div className="modal-overlay" onClick={() => setIsPayModalOpen(false)}>
                            <div className="modal" style={{ maxWidth: '480px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3>Saldar Cuenta por Pagar</h3>
                                    <button type="button" className="modal-close" onClick={() => setIsPayModalOpen(false)}>✕</button>
                                </div>
                                <form onSubmit={handlePaySubmit}>
                                    <div className="modal-body">
                                        <div style={{ padding: '14px', background: 'var(--bg-hover)', borderRadius: '8px', marginBottom: '16px' }}>
                                            <div className="font-semibold">{expenseToPay.supplier_name}</div>
                                            <div className="text-muted" style={{ fontSize: '12px', marginTop: '2px' }}>
                                                NCF: {expenseToPay.ncf || 'S/N'} &bull; Monto a Saldar:{' '}
                                                <strong style={{ color: 'var(--color-primary)' }}>
                                                    {formatCurrency(expenseToPay.total_amount)}
                                                </strong>
                                            </div>
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Cuenta de Banco / Caja Origen *</label>
                                            <select
                                                className="form-control"
                                                required
                                                value={payForm.data.pay_account_origin_id}
                                                onChange={(e) => payForm.setData('pay_account_origin_id', e.target.value)}
                                            >
                                                {assetAccounts.map((acc) => (
                                                    <option key={acc.id} value={acc.id}>
                                                        {acc.code} - {acc.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="form-group" style={{ marginBottom: 0 }}>
                                            <label className="form-label">Diario Contable *</label>
                                            <select
                                                className="form-control"
                                                required
                                                value={payForm.data.selected_journal_id}
                                                onChange={(e) => payForm.setData('selected_journal_id', e.target.value)}
                                            >
                                                {journals.map((j) => (
                                                    <option key={j.id} value={j.id}>
                                                        {j.code} - {j.name}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>
                                    <div className="modal-footer">
                                        <button type="button" className="btn btn-secondary" onClick={() => setIsPayModalOpen(false)}>
                                            Cancelar
                                        </button>
                                        <button type="submit" className="btn btn-primary" disabled={payForm.processing}>
                                            {payForm.processing ? 'Procesando...' : 'Confirmar Pago'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
