import React, { useState, useMemo, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

const NCF_TYPE_LABELS = {
    '31': 'E31 · Crédito Fiscal',
    '01': 'B01 · Crédito Fiscal',
    '32': 'E32 · Consumo',
    '02': 'B02 · Consumo',
    '33': 'E33 · Nota de Débito',
    '03': 'B03 · Nota de Débito',
    '34': 'E34 · Nota de Crédito',
    '04': 'B04 · Nota de Crédito',
    '41': 'E41 · Compras',
    '11': 'B11 · Compras',
    '43': 'E43 · Gastos Menores',
    '13': 'B13 · Gastos Menores',
    '44': 'E44 · Regímenes Especiales',
    '14': 'B14 · Regímenes Especiales',
    '45': 'E45 · Gubernamental',
    '15': 'B15 · Gubernamental',
};

const VOID_TYPES_608 = [
    { code: '01', label: '01 - Deterioro de Factura Pre-impresa' },
    { code: '02', label: '02 - Errores de Impresión (Factura Pre-impresa)' },
    { code: '03', label: '03 - Impresión Defectuosa' },
    { code: '04', label: '04 - Corrección de la Información' },
    { code: '05', label: '05 - Cambio de Productos / Servicios' },
    { code: '06', label: '06 - Devolución de Productos / Cancelación Académica' },
    { code: '07', label: '07 - Omisión de Productos / Servicios' },
    { code: '08', label: '08 - Errores en Secuencia de NCF' },
    { code: '09', label: '09 - Por Cese de Operaciones' },
    { code: '10', label: '10 - Pérdida o Hurto de Talonarios' },
];

const SIM_TYPES = [
    { type: 31, code: 'E31', name: 'Factura de Crédito Fiscal Electrónica', group: 'Ventas B2B' },
    { type: 32, code: 'E32', name: 'Factura de Consumo Electrónica (< 250k RFCE)', group: 'Consumo' },
    { type: 32, code: 'E32+', name: 'Factura de Consumo Electrónica (>= 250k)', group: 'Consumo' },
    { type: 33, code: 'E33', name: 'Nota de Débito Electrónica', group: 'Modificadores' },
    { type: 34, code: 'E34', name: 'Nota de Crédito Electrónica', group: 'Modificadores' },
    { type: 41, code: 'E41', name: 'Compras Electrónico (Proveedores Informales)', group: 'Especiales' },
    { type: 43, code: 'E43', name: 'Gastos Menores Electrónico (Caja Chica)', group: 'Especiales' },
    { type: 44, code: 'E44', name: 'Regímenes Especiales Electrónico', group: 'Especiales' },
    { type: 45, code: 'E45', name: 'Gubernamental Electrónico', group: 'Gobierno' },
    { type: 46, code: 'E46', name: 'Comprobante de Exportaciones Electrónico', group: 'Exterior' },
    { type: 47, code: 'E47', name: 'Comprobante para Pagos al Exterior Electrónico', group: 'Exterior' },
];

const fmtMoney = (n, currency = 'DOP') => {
    const prefix = currency === 'USD' ? 'US$' : currency === 'EUR' ? '€' : 'RD$';
    return `${prefix} ${Number(n || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};

export default function FinanceInvoicesIndex({
    invoices,
    stats = {},
    students = [],
    concepts = [],
    filters = {},
    dgiiConfig = {},
}) {
    const [billsModule, setBillsModule] = useState(
        filters.module || (filters.tab === 'dgii_audit' ? 'dgii-logs' : 'invoices')
    );

    useEffect(() => {
        if (filters.module) {
            setBillsModule(filters.module);
        } else if (filters.tab === 'dgii_audit') {
            setBillsModule('dgii-logs');
        } else {
            setBillsModule('invoices');
        }
    }, [filters.module, filters.tab]);

    // Sub-view inside Invoices ('list' | 'detail' | 'new')
    const [selectedInvoice, setSelectedInvoice] = useState(null);
    const [showNewForm, setShowNewForm] = useState(false);
    const [selectedIds, setSelectedIds] = useState([]);

    const [search, setSearch] = useState(filters.search || '');
    const [activeTab, setActiveTab] = useState(filters.tab || 'all');

    // New Invoice Form State (1:1 `invoices.js` `renderForm`)
    const todayStr = new Date().toISOString().split('T')[0];
    const nextWeekStr = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0];
    const [formIsEcf, setFormIsEcf] = useState(true);
    const [formEcfType, setFormEcfType] = useState('32');
    const [formStudentId, setFormStudentId] = useState('');
    const [formClientName, setFormClientName] = useState('');
    const [formClientRnc, setFormClientRnc] = useState('');
    const [formCurrency, setFormCurrency] = useState('DOP');
    const [formIssueDate, setFormIssueDate] = useState(todayStr);
    const [formDueDate, setFormDueDate] = useState(nextWeekStr);
    const [formNotes, setFormNotes] = useState('');
    const [formDiscountPct, setFormDiscountPct] = useState(0);
    const [formTaxPct, setFormTaxPct] = useState(0);
    const [formItems, setFormItems] = useState([
        { description: '', quantity: 1, unit_price: 0 },
    ]);
    const [formSaving, setFormSaving] = useState(false);

    // Audit Trail (Seguimiento de Comprobante) state
    const [auditQuery, setAuditQuery] = useState('');
    const [tracedInvoice, setTracedInvoice] = useState(null);

    // Received Invoices (ACECF) state
    const [receivedXmlModal, setReceivedXmlModal] = useState(false);
    const [receivedXmlText, setReceivedXmlText] = useState('');
    const [receivedList, setReceivedList] = useState([]);

    // DGII Certification / Simulation state
    const [simResults, setSimResults] = useState({});
    const [diagRunning, setDiagRunning] = useState(false);
    const [diagResult, setDiagResult] = useState(null);

    // Modal Nota de Crédito (E34)
    const [creditNoteTarget, setCreditNoteTarget] = useState(null);
    const [cnReason, setCnReason] = useState('');
    const [cnModCode, setCnModCode] = useState('1');
    const [cnAmount, setCnAmount] = useState('');
    const [cnProcessing, setCnProcessing] = useState(false);

    // Modal Anulación (608)
    const [voidTarget, setVoidTarget] = useState(null);
    const [voidType608, setVoidType608] = useState('04');
    const [voidReason, setVoidReason] = useState('');
    const [voidProcessing, setVoidProcessing] = useState(false);

    // Modal Emisión e-CF Manual
    const [emitTarget, setEmitTarget] = useState(null);
    const [emitEcfType, setEmitEcfType] = useState('32');
    const [emitProcessing, setEmitProcessing] = useState(false);

    const rows = invoices?.data || [];

    const applyFilters = (nextTab = activeTab, nextSearch = search) => {
        setActiveTab(nextTab);
        router.get(
            '/admin/finance/invoices',
            {
                tab: nextTab,
                search: nextSearch,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const toggleSelectAll = (checked) => {
        if (checked) {
            setSelectedIds(rows.map((r) => r.id));
        } else {
            setSelectedIds([]);
        }
    };

    const toggleSelectRow = (id) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]
        );
    };

    // Totals for New Invoice Form
    const formSubtotal = useMemo(
        () => formItems.reduce((acc, it) => acc + Number(it.quantity || 0) * Number(it.unit_price || 0), 0),
        [formItems]
    );
    const formDiscountAmt = useMemo(
        () => formSubtotal * (Number(formDiscountPct || 0) / 100),
        [formSubtotal, formDiscountPct]
    );
    const formTaxAmt = useMemo(
        () => Math.max(0, formSubtotal - formDiscountAmt) * (Number(formTaxPct || 0) / 100),
        [formSubtotal, formDiscountAmt, formTaxPct]
    );
    const formTotal = useMemo(
        () => Math.max(0, formSubtotal - formDiscountAmt + formTaxAmt),
        [formSubtotal, formDiscountAmt, formTaxAmt]
    );

    const submitNewInvoice = (statusMode = 'paid') => {
        setFormSaving(true);
        router.post(
            '/admin/finance/invoices',
            {
                student_id: formStudentId || null,
                client_name: formClientName || null,
                client_rnc: formClientRnc || null,
                issue_date: formIssueDate,
                due_date: formDueDate,
                status: statusMode,
                ecf_type: formIsEcf ? formEcfType : null,
                notes: formNotes,
                discount: formDiscountAmt,
                items: formItems.map((it) => ({
                    description: it.description || 'Servicio Educativo',
                    quantity: Number(it.quantity) || 1,
                    unit_price: Number(it.unit_price) || 0,
                    tax_rate: Number(formTaxPct) || 0,
                })),
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setShowNewForm(false);
                },
                onFinish: () => {
                    setFormSaving(false);
                },
            }
        );
    };

    const openCreditNoteModal = (inv) => {
        setCreditNoteTarget(inv);
        setCnReason('');
        setCnModCode('1');
        setCnAmount(String(inv.amount || 0));
    };

    const submitCreditNote = (e) => {
        e.preventDefault();
        if (!creditNoteTarget) return;
        setCnProcessing(true);
        router.post(
            `/admin/finance/invoices/${creditNoteTarget.id}/credit-note`,
            {
                reason: cnReason,
                modification_code: cnModCode,
                amount: cnAmount,
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    setCnProcessing(false);
                    setCreditNoteTarget(null);
                },
            }
        );
    };

    const openVoidModal = (inv) => {
        setVoidTarget(inv);
        setVoidType608('04');
        setVoidReason('');
    };

    const submitVoid = (e) => {
        e.preventDefault();
        if (!voidTarget) return;
        setVoidProcessing(true);
        router.post(
            `/admin/finance/invoices/${voidTarget.id}/void`,
            {
                void_type_608: voidType608,
                reason: voidReason,
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    setVoidProcessing(false);
                    setVoidTarget(null);
                },
            }
        );
    };

    const openEmitModal = (inv) => {
        setEmitTarget(inv);
        setEmitEcfType(inv.client_rnc && String(inv.client_rnc).replace(/\D/g, '').length === 9 ? '31' : '32');
    };

    const submitEmitEcf = (e) => {
        e.preventDefault();
        if (!emitTarget) return;
        setEmitProcessing(true);
        router.post(
            `/admin/finance/invoices/${emitTarget.id}/emit-ecf`,
            { ecf_type: Number(emitEcfType) },
            {
                preserveScroll: true,
                onFinish: () => {
                    setEmitProcessing(false);
                    setEmitTarget(null);
                },
            }
        );
    };

    const openTraceFor = (inv) => {
        setTracedInvoice(inv);
        setAuditQuery(inv.ncf || inv.invoice_number);
        setBillsModule('audit-trail');
    };

    const handleAuditSearch = (e) => {
        e.preventDefault();
        const q = auditQuery.trim().toUpperCase();
        if (!q) return;
        const match = rows.find(
            (r) =>
                (r.ncf && r.ncf.toUpperCase().includes(q)) ||
                (r.invoice_number && r.invoice_number.toUpperCase().includes(q)) ||
                (r.client_name && r.client_name.toUpperCase().includes(q))
        );
        if (match) {
            setTracedInvoice(match);
        } else if (rows.length > 0) {
            setTracedInvoice(rows[0]);
        }
    };

    // Related credit notes for tracedInvoice
    const relatedCreditNotes = useMemo(() => {
        if (!tracedInvoice) return [];
        return rows.filter(
            (r) =>
                r.is_credit_note &&
                r.id !== tracedInvoice.id &&
                ((tracedInvoice.ncf && r.notes && r.notes.includes(tracedInvoice.ncf)) ||
                    r.client_name === tracedInvoice.client_name)
        );
    }, [tracedInvoice, rows]);

    // 1:1 status badge matching `invoices.js` `statusLabel` + `badge-${status}`
    const renderStatusBadge = (inv) => {
        if (inv.is_voided) return <span className="badge badge-cancelled">Cancelada</span>;
        if (inv.is_credit_note) return <span className="badge badge-partial">Nota de Crédito</span>;
        const st = String(inv.status || '').toLowerCase();
        if (['paid', 'pagado', 'completado'].includes(st)) {
            return <span className="badge badge-paid">Pagada</span>;
        }
        if (['overdue', 'vencida'].includes(st)) {
            return <span className="badge badge-overdue">Vencida</span>;
        }
        if (['draft', 'borrador'].includes(st)) {
            return <span className="badge badge-draft">Borrador</span>;
        }
        if (['partial', 'parcial'].includes(st)) {
            return <span className="badge badge-partial">Pago Parcial</span>;
        }
        return <span className="badge badge-sent">Pendiente de Pago</span>;
    };

    // 1:1 DGII badge matching `invoices.js` line 197 (`dgiiBadgeClass` + `dgiiLabel`)
    const renderDgiiBadge = (inv) => {
        if (!inv.ncf) return <span style={{ color: 'var(--color-text-muted)' }}>—</span>;
        const st = String(inv.dgii_status || '').toLowerCase();
        if (st === 'rejected' || st === 'rechazado') {
            return <span className="badge badge-overdue" style={{ fontSize: '9px' }}>Rechazado</span>;
        }
        if (st === 'contingency' || st === 'contingencia') {
            return <span className="badge badge-draft" style={{ fontSize: '9px' }}>Contingencia</span>;
        }
        if (st === 'pending' || st === 'procesando') {
            return <span className="badge badge-sent" style={{ fontSize: '9px' }}>Procesando</span>;
        }
        return <span className="badge badge-paid" style={{ fontSize: '9px' }}>Aceptado</span>;
    };

    return (
        <AuthenticatedLayout title="Facturas">
            <Head title="Facturas" />

            <div className="bills-scope">
                <div className="workspace-panel">
                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 1: LISTA DE FACTURAS (`invoices.js` `renderList` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'invoices' && !selectedInvoice && !showNewForm && (
                        <>
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Facturas</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Administra tu facturación y pagos</p>
                                </div>
                                <div style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
                                    <a
                                        href="/admin/finance/dgii-reports/607/export-excel"
                                        className="btn btn-secondary"
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', textDecoration: 'none' }}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="7 10 12 15 17 10" />
                                            <line x1="12" y1="15" x2="12" y2="3" />
                                        </svg>
                                        Exportar Excel
                                    </a>
                                    <button
                                        type="button"
                                        className="btn btn-primary"
                                        onClick={() => setShowNewForm(true)}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        Nueva Factura
                                    </button>
                                </div>
                            </div>

                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 'var(--spacing-lg)', flexWrap: 'wrap', gap: '12px' }}>
                                <div className="segmented-control" id="inv-status-tabs" style={{ marginBottom: 0 }}>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'all' ? 'active' : ''}`}
                                        onClick={() => applyFilters('all', search)}
                                    >
                                        Todas <span style={{ opacity: 0.5, marginLeft: '4px' }} id="inv-count">{invoices?.total ?? rows.length}</span>
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'draft' ? 'active' : ''}`}
                                        onClick={() => applyFilters('draft', search)}
                                    >
                                        Borrador
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'pending' ? 'active' : ''}`}
                                        onClick={() => applyFilters('pending', search)}
                                    >
                                        Pendiente
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'paid' ? 'active' : ''}`}
                                        onClick={() => applyFilters('paid', search)}
                                    >
                                        Pagada
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'credit_notes' ? 'active' : ''}`}
                                        onClick={() => applyFilters('credit_notes', search)}
                                    >
                                        Notas de Crédito
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'voided' ? 'active' : ''}`}
                                        onClick={() => applyFilters('voided', search)}
                                    >
                                        Anuladas
                                    </button>
                                </div>

                                <div className="search-wrapper">
                                    <svg className="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                    </svg>
                                    <input
                                        className="search-input"
                                        type="text"
                                        id="inv-search"
                                        placeholder="Buscar factura..."
                                        value={search}
                                        onChange={(e) => {
                                            setSearch(e.target.value);
                                            applyFilters(activeTab, e.target.value);
                                        }}
                                    />
                                </div>
                            </div>

                            <div className="table-outer">
                                <div className="table-wrapper">
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th style={{ width: '40px', paddingRight: 0 }}>
                                                    <input
                                                        type="checkbox"
                                                        id="bulk-select-all"
                                                        className="table-checkbox"
                                                        checked={rows.length > 0 && selectedIds.length === rows.length}
                                                        onChange={(e) => toggleSelectAll(e.target.checked)}
                                                        style={{ width: '16px', height: '16px', cursor: 'pointer', accentColor: '#111827' }}
                                                    />
                                                </th>
                                                <th>Número</th>
                                                <th>Cliente</th>
                                                <th>Emisión</th>
                                                <th>Vencimiento</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <th>DGII</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="inv-tbody">
                                            {rows.length === 0 ? (
                                                <tr>
                                                    <td colSpan={9} className="text-center text-muted" style={{ padding: '48px' }}>
                                                        No hay facturas que coincidan
                                                    </td>
                                                </tr>
                                            ) : (
                                                rows.map((inv) => (
                                                    <tr key={inv.id}>
                                                        <td style={{ width: '40px', paddingRight: 0 }}>
                                                            <input
                                                                type="checkbox"
                                                                className="bulk-select-item table-checkbox"
                                                                checked={selectedIds.includes(inv.id)}
                                                                onChange={() => toggleSelectRow(inv.id)}
                                                                style={{ width: '16px', height: '16px', cursor: 'pointer', accentColor: '#111827' }}
                                                            />
                                                        </td>
                                                        <td style={{ whiteSpace: 'nowrap' }}>
                                                            <a
                                                                href={`#facturas/${inv.id}`}
                                                                onClick={(e) => {
                                                                    e.preventDefault();
                                                                    setSelectedInvoice(inv);
                                                                }}
                                                                className="link-id"
                                                            >
                                                                {inv.ncf || inv.invoice_number}
                                                            </a>
                                                            {inv.ncf && (
                                                                <span className="badge badge-ecf" style={{ marginLeft: '6px' }}>
                                                                    e-CF
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td>
                                                            <div className="user-cell">
                                                                <div className="user-avatar-sm">
                                                                    {(inv.client_name || '?').charAt(0).toUpperCase()}
                                                                </div>
                                                                <div className="user-details">
                                                                    <span className="user-name">{inv.client_name}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td style={{ whiteSpace: 'nowrap' }}>{inv.date_formatted || inv.date}</td>
                                                        <td style={{ whiteSpace: 'nowrap' }}>{inv.due_date_formatted || '—'}</td>
                                                        <td style={{ fontWeight: 600, color: 'var(--color-text-primary)', whiteSpace: 'nowrap' }}>
                                                            <div>{fmtMoney(inv.amount, inv.currency)}</div>
                                                        </td>
                                                        <td>{renderStatusBadge(inv)}</td>
                                                        <td>{renderDgiiBadge(inv)}</td>
                                                        <td>
                                                            <div className="row-actions" style={{ gap: '6px' }}>
                                                                <button
                                                                    type="button"
                                                                    className="btn-icon"
                                                                    style={{ width: '28px', height: '28px' }}
                                                                    title="Ver Detalle"
                                                                    onClick={() => setSelectedInvoice(inv)}
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                                        <circle cx="12" cy="12" r="3" />
                                                                    </svg>
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    className="btn-icon"
                                                                    style={{ width: '28px', height: '28px' }}
                                                                    title="Imprimir Ticket Térmico (80mm con QR)"
                                                                    onClick={() => window.open(`/admin/finance/invoices/${inv.id}/pdf?template=thermal`, '_blank')}
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                        <path d="M6 9V2h12v7" />
                                                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                                                        <rect x="6" y="14" width="12" height="8" />
                                                                    </svg>
                                                                </button>
                                                                <a
                                                                    href={`/admin/finance/invoices/${inv.id}/pdf?template=normal&download=1`}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    className="btn btn-secondary"
                                                                    style={{ padding: '4px 8px', fontSize: '11px', height: 'auto', display: 'inline-flex', alignItems: 'center', gap: '4px', textDecoration: 'none' }}
                                                                    title="Descargar PDF (Carta/A4 con QR)"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                                        <polyline points="7 10 12 15 17 10" />
                                                                        <line x1="12" y1="15" x2="12" y2="3" />
                                                                    </svg>
                                                                    Descargar PDF
                                                                </a>
                                                                {!inv.is_voided && (
                                                                    <button
                                                                        type="button"
                                                                        className="btn-icon"
                                                                        style={{ width: '28px', height: '28px' }}
                                                                        title="Anular"
                                                                        onClick={() => openVoidModal(inv)}
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-danger-icon)" strokeWidth="2">
                                                                            <polyline points="3 6 5 6 21 6" />
                                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                                        </svg>
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>

                                {invoices?.links && invoices.links.length > 3 && (
                                    <div className="pagination">
                                        <span className="pagination-info">
                                            Mostrando página {invoices.current_page} de {invoices.last_page} ({invoices.total} facturas)
                                        </span>
                                        <div className="pagination-nav">
                                            {invoices.links.map((link, idx) =>
                                                link.url ? (
                                                    <Link
                                                        key={idx}
                                                        href={link.url}
                                                        className={`pagination-btn ${link.active ? 'active' : ''}`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                        preserveScroll
                                                    />
                                                ) : null
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Floating Bulk Actions Bar (`invoices.js` line 68) */}
                            <div className={`bulk-actions-bar ${selectedIds.length > 0 ? 'active' : ''}`}>
                                <div className="bulk-count">
                                    {selectedIds.length} seleccionada{selectedIds.length !== 1 ? 's' : ''}
                                </div>
                                <div className="bulk-actions-btns">
                                    <button
                                        type="button"
                                        className="bulk-btn bulk-btn-primary"
                                        onClick={() => {
                                            const first = rows.find((r) => selectedIds.includes(r.id));
                                            if (first) openEmitModal(first);
                                        }}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                                        </svg>
                                        e-CF
                                    </button>
                                    <button
                                        type="button"
                                        className="bulk-btn"
                                        style={{ backgroundColor: 'var(--amber)', borderColor: 'var(--amber)' }}
                                        onClick={() => {
                                            const first = rows.find((r) => selectedIds.includes(r.id));
                                            if (first) openVoidModal(first);
                                        }}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
                                        </svg>
                                        Anular
                                    </button>
                                    <button
                                        type="button"
                                        className="bulk-btn"
                                        onClick={() => setSelectedIds([])}
                                    >
                                        Deseleccionar
                                    </button>
                                </div>
                            </div>
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        NUEVA FACTURA (`invoices.js` `renderForm` lines 515–656 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'invoices' && showNewForm && (
                        <>
                            <div style={{ marginBottom: '14px' }}>
                                <button type="button" className="btn-back" onClick={() => setShowNewForm(false)}>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                        <line x1="19" y1="12" x2="5" y2="12" />
                                        <polyline points="12 19 5 12 12 5" />
                                    </svg>
                                    Facturas
                                </button>
                            </div>
                            <div className="page-header">
                                <div>
                                    <h1 className="page-title">Nueva Factura</h1>
                                    <p className="page-subtitle">Crea una factura para un cliente</p>
                                </div>
                                <button type="button" className="btn btn-secondary" onClick={() => setShowNewForm(false)}>
                                    Cancelar
                                </button>
                            </div>

                            <form
                                id="invoice-form"
                                className="form-card"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    submitNewInvoice('pending');
                                }}
                            >
                                <div style={{ padding: 'var(--spacing-xl)' }}>
                                    <div className="grid-2">
                                        <div
                                            className="form-group"
                                            style={{
                                                gridColumn: 'span 2',
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '24px',
                                                background: 'var(--bg-hover)',
                                                padding: '12px 16px',
                                                borderRadius: 'var(--radius-md)',
                                                border: '1px solid var(--color-border)',
                                                flexWrap: 'wrap',
                                            }}
                                        >
                                            <label style={{ display: 'flex', alignItems: 'center', gap: '8px', fontWeight: 600, cursor: 'pointer', margin: 0 }}>
                                                <input
                                                    type="checkbox"
                                                    checked={formIsEcf}
                                                    onChange={(e) => setFormIsEcf(e.target.checked)}
                                                    style={{ width: '18px', height: '18px' }}
                                                />
                                                Emitir Factura Electrónica (e-CF)
                                            </label>
                                            {formIsEcf && (
                                                <div style={{ flex: 1, minWidth: '220px' }}>
                                                    <select
                                                        className="form-control"
                                                        style={{ maxWidth: '320px' }}
                                                        value={formEcfType}
                                                        onChange={(e) => setFormEcfType(e.target.value)}
                                                    >
                                                        <option value="31">Crédito Fiscal (B2B - Tipo 31)</option>
                                                        <option value="32">Consumo (B2C - Tipo 32)</option>
                                                        <option value="33">Nota de Débito (Tipo 33)</option>
                                                        <option value="34">Nota de Crédito (Tipo 34)</option>
                                                        <option value="41">Compras (Tipo 41)</option>
                                                        <option value="43">Gastos Menores (Tipo 43)</option>
                                                        <option value="44">Regímenes Especiales (Tipo 44)</option>
                                                        <option value="45">Gubernamental (Tipo 45)</option>
                                                    </select>
                                                </div>
                                            )}
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Cliente</label>
                                            <select
                                                className="form-control"
                                                value={formStudentId}
                                                onChange={(e) => {
                                                    const val = e.target.value;
                                                    setFormStudentId(val);
                                                    const st = students.find((s) => String(s.id) === String(val));
                                                    if (st) {
                                                        setFormClientName(st.name);
                                                        setFormClientRnc(st.rnc || '');
                                                    }
                                                }}
                                            >
                                                <option value="">Consumidor Final (Sin Cliente)</option>
                                                {students.map((c) => (
                                                    <option key={c.id} value={c.id}>
                                                        {c.name} {c.rnc ? `(${c.rnc})` : ''}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Moneda</label>
                                            <select
                                                className="form-control"
                                                value={formCurrency}
                                                onChange={(e) => setFormCurrency(e.target.value)}
                                            >
                                                <option value="DOP">DOP - Pesos Dominicanos</option>
                                                <option value="USD">USD - Dólares</option>
                                                <option value="EUR">EUR - Euros</option>
                                            </select>
                                        </div>

                                        {!formStudentId && (
                                            <div
                                                className="grid-2"
                                                style={{
                                                    gridColumn: 'span 2',
                                                    gap: '16px',
                                                    background: 'var(--bg-hover)',
                                                    padding: '12px 16px',
                                                    borderRadius: 'var(--radius-md)',
                                                    border: '1px solid var(--color-border)',
                                                }}
                                            >
                                                <div className="form-group" style={{ margin: 0 }}>
                                                    <label className="form-label">Razón Social o Nombre</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        placeholder="Ej: Juan Pérez"
                                                        value={formClientName}
                                                        onChange={(e) => setFormClientName(e.target.value)}
                                                    />
                                                </div>
                                                <div className="form-group" style={{ margin: 0 }}>
                                                    <label className="form-label">RNC o Cédula</label>
                                                    <input
                                                        type="text"
                                                        className="form-control"
                                                        placeholder="Ej: 00100000000"
                                                        value={formClientRnc}
                                                        onChange={(e) => setFormClientRnc(e.target.value)}
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        <div className="form-group">
                                            <label className="form-label">Fecha de Emisión *</label>
                                            <input
                                                type="date"
                                                className="form-control"
                                                required
                                                value={formIssueDate}
                                                onChange={(e) => setFormIssueDate(e.target.value)}
                                            />
                                        </div>
                                        <div className="form-group">
                                            <label className="form-label">Fecha de Vencimiento *</label>
                                            <input
                                                type="date"
                                                className="form-control"
                                                required
                                                value={formDueDate}
                                                onChange={(e) => setFormDueDate(e.target.value)}
                                            />
                                        </div>
                                    </div>

                                    <h3 className="mt-24 mb-16" style={{ fontSize: '15px', fontWeight: 600, borderBottom: '1px solid var(--color-border)', paddingBottom: '8px' }}>
                                        Conceptos
                                    </h3>

                                    <div id="invoice-items-container">
                                        {formItems.map((it, idx) => (
                                            <div key={idx} className="invoice-item-row">
                                                <div className="invoice-item-desc">
                                                    <input
                                                        type="text"
                                                        list="bills-concepts-list"
                                                        className="form-control"
                                                        placeholder="Descripción del concepto o artículo..."
                                                        required
                                                        value={it.description}
                                                        onChange={(e) => {
                                                            const val = e.target.value;
                                                            const matched = concepts.find((c) => c.name === val);
                                                            setFormItems((prev) =>
                                                                prev.map((row, i) =>
                                                                    i === idx
                                                                        ? {
                                                                              ...row,
                                                                              description: val,
                                                                              unit_price: matched ? matched.price : row.unit_price,
                                                                          }
                                                                        : row
                                                                )
                                                            );
                                                        }}
                                                    />
                                                </div>
                                                <div className="invoice-item-qty">
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        placeholder="Cant."
                                                        min="1"
                                                        step="1"
                                                        required
                                                        value={it.quantity}
                                                        onChange={(e) =>
                                                            setFormItems((prev) =>
                                                                prev.map((row, i) => (i === idx ? { ...row, quantity: e.target.value } : row))
                                                            )
                                                        }
                                                    />
                                                </div>
                                                <div className="invoice-item-price">
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        placeholder="Precio"
                                                        min="0"
                                                        step="0.01"
                                                        required
                                                        value={it.unit_price}
                                                        onChange={(e) =>
                                                            setFormItems((prev) =>
                                                                prev.map((row, i) => (i === idx ? { ...row, unit_price: e.target.value } : row))
                                                            )
                                                        }
                                                    />
                                                </div>
                                                {formItems.length > 1 && (
                                                    <button
                                                        type="button"
                                                        className="btn-icon invoice-item-delete"
                                                        onClick={() => setFormItems((prev) => prev.filter((_, i) => i !== idx))}
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-danger-icon)" strokeWidth="2">
                                                            <line x1="18" y1="6" x2="6" y2="18" />
                                                            <line x1="6" y1="6" x2="18" y2="18" />
                                                        </svg>
                                                    </button>
                                                )}
                                            </div>
                                        ))}
                                        <datalist id="bills-concepts-list">
                                            {concepts.map((c) => (
                                                <option key={c.id} value={c.name} />
                                            ))}
                                        </datalist>
                                    </div>

                                    <button
                                        type="button"
                                        className="btn btn-ghost mt-16"
                                        onClick={() => setFormItems((prev) => [...prev, { description: '', quantity: 1, unit_price: 0 }])}
                                    >
                                        + Agregar Concepto
                                    </button>

                                    <div className="grid-2 mt-24">
                                        <div>
                                            <div className="form-group">
                                                <label className="form-label">Notas</label>
                                                <textarea
                                                    className="form-control"
                                                    rows={3}
                                                    value={formNotes}
                                                    onChange={(e) => setFormNotes(e.target.value)}
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <div className="grid-2">
                                                <div className="form-group">
                                                    <label className="form-label">Descuento (%)</label>
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        min="0"
                                                        max="100"
                                                        step="0.01"
                                                        value={formDiscountPct}
                                                        onChange={(e) => setFormDiscountPct(e.target.value)}
                                                    />
                                                </div>
                                                <div className="form-group">
                                                    <label className="form-label">ITBIS (%)</label>
                                                    <input
                                                        type="number"
                                                        className="form-control"
                                                        min="0"
                                                        max="100"
                                                        step="0.01"
                                                        value={formTaxPct}
                                                        onChange={(e) => setFormTaxPct(e.target.value)}
                                                    />
                                                </div>
                                            </div>
                                            <div style={{ background: 'var(--bg-hover)', padding: '16px', borderRadius: 'var(--radius-lg)', marginTop: '16px' }}>
                                                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '13px' }}>
                                                    <span style={{ color: 'var(--color-text-muted)' }}>Subtotal:</span>
                                                    <span className="font-semibold">{fmtMoney(formSubtotal, formCurrency)}</span>
                                                </div>
                                                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '13px' }}>
                                                    <span style={{ color: 'var(--color-text-muted)' }}>Descuento:</span>
                                                    <span className="font-semibold text-red">-{fmtMoney(formDiscountAmt, formCurrency)}</span>
                                                </div>
                                                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '13px' }}>
                                                    <span style={{ color: 'var(--color-text-muted)' }}>Impuesto:</span>
                                                    <span className="font-semibold">{fmtMoney(formTaxAmt, formCurrency)}</span>
                                                </div>
                                                <div style={{ display: 'flex', justifyContent: 'space-between', borderTop: '1px solid var(--color-border)', paddingTop: '8px', marginTop: '8px' }}>
                                                    <span style={{ fontWeight: 700, fontSize: '18px' }}>Total:</span>
                                                    <span style={{ fontWeight: 700, fontSize: '18px', color: 'var(--color-primary)' }}>
                                                        {fmtMoney(formTotal, formCurrency)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-24" style={{ display: 'flex', gap: '12px', flexWrap: 'wrap' }}>
                                        <button
                                            type="submit"
                                            disabled={formSaving}
                                            className="btn btn-secondary"
                                            style={{ flex: 1, minWidth: '140px' }}
                                        >
                                            Guardar Factura
                                        </button>
                                        <button
                                            type="button"
                                            disabled={formSaving}
                                            className="btn btn-primary"
                                            style={{ flex: 1, minWidth: '160px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px' }}
                                            onClick={() => submitNewInvoice('paid')}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                                                <line x1="12" y1="18" x2="12.01" y2="18" />
                                            </svg>
                                            Guardar y Cobrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        VISTA DETALLE DE FACTURA (`invoices.js` `renderDetails` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'invoices' && selectedInvoice && !showNewForm && (
                        <>
                            <div className="invoice-detail-header">
                                <div className="detail-header-row-1">
                                    <div className="detail-breadcrumb" style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                        <button
                                            type="button"
                                            className="btn-back"
                                            onClick={() => setSelectedInvoice(null)}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                                <line x1="19" y1="12" x2="5" y2="12" />
                                                <polyline points="12 19 5 12 12 5" />
                                            </svg>
                                            Facturas
                                        </button>
                                        <span style={{ color: 'var(--color-text-muted)', fontSize: '12px', opacity: 0.5 }}>/</span>
                                        <span style={{ fontSize: '12px', fontWeight: 600, color: 'var(--color-text-primary)', fontFamily: "'JetBrains Mono', monospace" }}>
                                            {selectedInvoice.invoice_number}
                                        </span>
                                    </div>
                                    <div className="detail-title-block">
                                        <h1 className="page-title" style={{ margin: 0 }}>
                                            {selectedInvoice.ncf
                                                ? `e-CF ${selectedInvoice.ncf}`
                                                : `Factura ${selectedInvoice.invoice_number}`}
                                        </h1>
                                    </div>
                                </div>
                                <div className="detail-header-row-2">
                                    <p className="page-subtitle detail-issue-date" style={{ margin: 0, fontSize: '13px', color: 'var(--color-text-muted)', whiteSpace: 'nowrap' }}>
                                        Emitida el {selectedInvoice.datetime_formatted || selectedInvoice.date_formatted || selectedInvoice.date}
                                    </p>
                                    <div className="invoice-actions detail-actions">
                                        <button
                                            type="button"
                                            className="btn btn-primary btn-sm"
                                            onClick={() => window.open(`/admin/finance/invoices/${selectedInvoice.id}/pdf?template=thermal`, '_blank')}
                                            style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M6 9V2h12v7" />
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                                <rect x="6" y="14" width="12" height="8" />
                                            </svg>
                                            Imprimir Ticket
                                        </button>
                                        <button
                                            type="button"
                                            className="btn btn-secondary btn-sm"
                                            onClick={() => window.open(`/admin/finance/invoices/${selectedInvoice.id}/pdf?template=normal`, '_blank')}
                                            style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <polyline points="6 9 6 2 18 2 18 9" />
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                                <rect x="6" y="14" width="12" height="8" />
                                            </svg>
                                            Imprimir A4
                                        </button>
                                        <a
                                            href={`/admin/finance/invoices/${selectedInvoice.id}/pdf?template=normal&download=1`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="btn btn-secondary btn-sm"
                                            style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', textDecoration: 'none' }}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="7 10 12 15 17 10" />
                                                <line x1="12" y1="15" x2="12" y2="3" />
                                            </svg>
                                            Descargar PDF
                                        </a>
                                        <a
                                            href={`/admin/finance/invoices/${selectedInvoice.id}/pdf?template=thermal&download=1`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="btn btn-secondary btn-sm"
                                            style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', textDecoration: 'none' }}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="7 10 12 15 17 10" />
                                                <line x1="12" y1="15" x2="12" y2="3" />
                                            </svg>
                                            Descargar Ticket
                                        </a>
                                        <button
                                            type="button"
                                            className="btn btn-secondary btn-sm"
                                            onClick={() => openTraceFor(selectedInvoice)}
                                            style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <circle cx="12" cy="12" r="10" />
                                                <polyline points="12 6 12 12 16 14" />
                                            </svg>
                                            Seguimiento
                                        </button>
                                        {selectedInvoice.ncf && !selectedInvoice.is_credit_note && !selectedInvoice.is_voided && (
                                            <button
                                                type="button"
                                                className="btn btn-secondary btn-sm"
                                                style={{ color: 'var(--color-danger-icon)', borderColor: 'rgba(239,68,68,0.2)' }}
                                                onClick={() => openCreditNoteModal(selectedInvoice)}
                                            >
                                                Nota de Crédito
                                            </button>
                                        )}
                                        {!selectedInvoice.is_voided && (
                                            <button
                                                type="button"
                                                className="btn btn-secondary btn-sm"
                                                style={{ color: 'var(--color-danger-icon)', borderColor: 'rgba(239,68,68,0.2)' }}
                                                onClick={() => openVoidModal(selectedInvoice)}
                                            >
                                                Anular
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {selectedInvoice.ncf && (
                                <div className="table-outer mb-24 ecf-status-card">
                                    <div className="ecf-status-card-body" style={{ padding: 'var(--spacing-xl)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
                                        <div>
                                            <div style={{ fontSize: '15px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '6px' }}>
                                                Factura Electrónica (e-CF)
                                                {renderDgiiBadge(selectedInvoice)}
                                            </div>
                                            <p style={{ margin: 0, fontFamily: "'JetBrains Mono', monospace", fontSize: '12px', color: 'var(--color-text-muted)' }}>
                                                <strong>e-NCF:</strong> {selectedInvoice.ncf} | <strong>Cód. Seguridad:</strong> {selectedInvoice.security_code || '—'}
                                            </p>
                                            {selectedInvoice.dgii_track_id && (
                                                <p style={{ margin: '4px 0 0 0', fontSize: '12px', color: 'var(--color-text-muted)' }}>
                                                    <strong>DGII Track ID:</strong> <code>{selectedInvoice.dgii_track_id}</code>
                                                </p>
                                            )}
                                        </div>
                                        <div className="ecf-status-actions" style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                            <span
                                                className="ecf-cert-badge"
                                                style={{
                                                    display: 'inline-flex',
                                                    alignItems: 'center',
                                                    gap: '6px',
                                                    padding: '5px 12px',
                                                    background: 'var(--color-success-bg)',
                                                    border: '1px solid rgba(16,185,129,0.25)',
                                                    borderRadius: 'var(--radius-full)',
                                                    color: 'var(--color-success-text)',
                                                    fontSize: '12px',
                                                    fontWeight: 600,
                                                    lineHeight: 1,
                                                    whiteSpace: 'nowrap',
                                                }}
                                            >
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                    <polyline points="9 12 11 14 15 10" />
                                                </svg>
                                                Certificado DGII
                                            </span>
                                            <a
                                                href={`/admin/finance/invoices/${selectedInvoice.id}/xml`}
                                                className="btn btn-secondary btn-sm"
                                                style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', textDecoration: 'none' }}
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                    <polyline points="7 10 12 15 17 10" />
                                                    <line x1="12" y1="15" x2="12" y2="3" />
                                                </svg>
                                                Descargar XML
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="table-outer mb-24">
                                <div style={{ padding: 'var(--spacing-xl)' }}>
                                    <div className="grid-2">
                                        <div className="invoice-meta-left">
                                            <div style={{ fontSize: '11px', fontWeight: 600, color: 'var(--color-text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '8px' }}>
                                                Facturado a
                                            </div>
                                            <p style={{ fontSize: '16px', fontWeight: 600, margin: 0 }}>{selectedInvoice.client_name}</p>
                                            {selectedInvoice.client_rnc && selectedInvoice.client_rnc !== '—' && (
                                                <p style={{ margin: '4px 0 0 0', color: 'var(--color-text-secondary)', fontSize: '13px' }}>
                                                    RNC/Cédula: {selectedInvoice.client_rnc}
                                                </p>
                                            )}
                                        </div>
                                        <div className="invoice-meta-right text-right">
                                            <div style={{ fontSize: '11px', fontWeight: 600, color: 'var(--color-text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '8px' }}>
                                                Detalles
                                            </div>
                                            <p style={{ margin: 0, fontSize: '13px' }}>
                                                <strong>Vence:</strong> {selectedInvoice.due_date_formatted || selectedInvoice.date_formatted}
                                            </p>
                                            <p style={{ margin: '4px 0 0 0', fontSize: '13px' }}>
                                                <strong>Estado:</strong> {renderStatusBadge(selectedInvoice)}
                                            </p>
                                            <p style={{ margin: '4px 0 0 0', color: 'var(--color-text-muted)', fontSize: '12px' }}>
                                                Vía: {selectedInvoice.gateway || 'Caja'}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="mt-24">
                                        <table className="data-table responsive-invoice-table" style={{ border: '1px solid var(--color-border)', borderRadius: 'var(--radius-lg)', overflow: 'hidden', width: '100%' }}>
                                            <thead>
                                                <tr>
                                                    <th>Descripción</th>
                                                    <th className="text-right">Cant.</th>
                                                    <th className="text-right">Precio</th>
                                                    <th className="text-right">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td data-label="Descripción" style={{ color: 'var(--color-text-primary)', fontWeight: 600, whiteSpace: 'normal', fontSize: '14px' }}>
                                                        {selectedInvoice.concept}
                                                    </td>
                                                    <td data-label="Cant." className="text-right">1</td>
                                                    <td data-label="Precio" className="text-right">
                                                        {fmtMoney(selectedInvoice.amount + (selectedInvoice.discount_amount || 0), selectedInvoice.currency)}
                                                    </td>
                                                    <td data-label="Total" className="text-right font-bold" style={{ color: 'var(--color-primary)' }}>
                                                        {fmtMoney(selectedInvoice.amount, selectedInvoice.currency)}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div className="grid-2 mt-24">
                                        <div>
                                            {selectedInvoice.notes && (
                                                <>
                                                    <div style={{ fontSize: '11px', fontWeight: 600, color: 'var(--color-text-muted)', textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: '8px' }}>
                                                        Notas
                                                    </div>
                                                    <p style={{ whiteSpace: 'pre-wrap', fontSize: '13px', color: 'var(--color-text-secondary)' }}>
                                                        {selectedInvoice.notes}
                                                    </p>
                                                </>
                                            )}
                                        </div>
                                        <div className="invoice-totals-wrapper">
                                            <table className="invoice-totals-table" style={{ width: '100%', borderCollapse: 'collapse' }}>
                                                <tbody>
                                                    <tr>
                                                        <td style={{ padding: '8px 0', textAlign: 'left', color: 'var(--color-text-muted)', fontSize: '13px' }}>Subtotal</td>
                                                        <td style={{ padding: '8px 0', textAlign: 'right', fontWeight: 500, fontSize: '13px' }}>
                                                            {fmtMoney(selectedInvoice.amount + (selectedInvoice.discount_amount || 0), selectedInvoice.currency)}
                                                        </td>
                                                    </tr>
                                                    {selectedInvoice.discount_amount > 0 && (
                                                        <tr>
                                                            <td style={{ padding: '8px 0', textAlign: 'left', color: 'var(--color-text-muted)', fontSize: '13px' }}>Descuento</td>
                                                            <td style={{ padding: '8px 0', textAlign: 'right', fontWeight: 500, fontSize: '13px' }}>
                                                                -{fmtMoney(selectedInvoice.discount_amount, selectedInvoice.currency)}
                                                            </td>
                                                        </tr>
                                                    )}
                                                    <tr style={{ borderTop: '2px solid var(--color-border)' }}>
                                                        <td style={{ padding: '12px 0', textAlign: 'left', fontSize: '18px', fontWeight: 600 }}>Total</td>
                                                        <td style={{ padding: '12px 0', textAlign: 'right', fontSize: '18px', fontWeight: 700, color: 'var(--color-primary)' }}>
                                                            {fmtMoney(selectedInvoice.amount, selectedInvoice.currency)}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 2: SEGUIMIENTO DE COMPROBANTE (`audit-trail.js` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'audit-trail' && (
                        <>
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Seguimiento de Comprobante</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Grafo interactivo de relaciones fiscales, notas de crédito, pagos y auditoría cronológica</p>
                                </div>
                                <Link href="/admin/finance/invoices" className="btn btn-secondary">
                                    Volver a Facturas
                                </Link>
                            </div>

                            <div className="form-card" style={{ marginBottom: '24px', padding: '20px 24px' }}>
                                <form onSubmit={handleAuditSearch} style={{ display: 'flex', gap: '12px', alignItems: 'center', flexWrap: 'wrap' }}>
                                    <div className="search-wrapper" style={{ flex: 1, minWidth: '260px' }}>
                                        <svg className="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <circle cx="11" cy="11" r="8" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        </svg>
                                        <input
                                            type="text"
                                            className="search-input"
                                            style={{ width: '100%' }}
                                            placeholder="Ingrese NCF / e-NCF (ej. E310000000001), número de factura o cliente..."
                                            value={auditQuery}
                                            onChange={(e) => setAuditQuery(e.target.value)}
                                        />
                                    </div>
                                    <button type="submit" className="btn btn-primary">
                                        Rastrear Comprobante
                                    </button>
                                </form>

                                {rows.length > 0 && (
                                    <div style={{ marginTop: '12px', display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
                                        <span className="text-muted" style={{ fontSize: '11px', fontWeight: 600, textTransform: 'uppercase' }}>
                                            Comprobantes recientes:
                                        </span>
                                        {rows.slice(0, 5).map((r) => (
                                            <button
                                                key={r.id}
                                                type="button"
                                                onClick={() => {
                                                    setTracedInvoice(r);
                                                    setAuditQuery(r.ncf || r.invoice_number);
                                                }}
                                                className="badge badge-draft"
                                                style={{ cursor: 'pointer', border: tracedInvoice?.id === r.id ? '1.5px solid var(--color-primary)' : '1px solid var(--color-border)' }}
                                            >
                                                {r.ncf || r.invoice_number} — {r.client_name}
                                            </button>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {(tracedInvoice || rows[0]) && (() => {
                                const activeTrace = tracedInvoice || rows[0];
                                return (
                                    <div className="node-graph-viewport">
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                                            <span style={{ fontSize: '12px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--color-text-muted)' }}>
                                                Grafo de Trazabilidad del Comprobante
                                            </span>
                                            <span className="badge badge-ecf">{activeTrace.ncf || activeTrace.invoice_number}</span>
                                        </div>

                                        <div className="grid-3" style={{ alignItems: 'center', gap: '24px' }}>
                                            <div className="form-card" style={{ borderLeft: '4px solid #2563eb', margin: 0, padding: '18px' }}>
                                                <div style={{ fontSize: '11px', fontWeight: 700, color: '#2563eb', textTransform: 'uppercase', marginBottom: '6px' }}>
                                                    1. Documento Base Emitido
                                                </div>
                                                <div style={{ fontSize: '16px', fontWeight: 700 }}>{activeTrace.invoice_number}</div>
                                                {activeTrace.ncf && (
                                                    <div className="badge badge-ecf" style={{ marginTop: '4px' }}>{activeTrace.ncf}</div>
                                                )}
                                                <div className="text-muted" style={{ fontSize: '12px', marginTop: '8px' }}>
                                                    Cliente: <strong>{activeTrace.client_name}</strong>
                                                </div>
                                                <div style={{ fontSize: '18px', fontWeight: 800, marginTop: '10px' }}>
                                                    {fmtMoney(activeTrace.amount)}
                                                </div>
                                            </div>

                                            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                                <div className="form-card" style={{ borderLeft: '4px solid #10b981', margin: 0, padding: '16px' }}>
                                                    <div style={{ fontSize: '11px', fontWeight: 700, color: '#10b981', textTransform: 'uppercase', marginBottom: '4px' }}>
                                                        2A. Cobro / Transacción en Caja
                                                    </div>
                                                    <div style={{ fontSize: '13px', fontWeight: 600 }}>
                                                        Vía: {activeTrace.gateway || 'Caja Institucional'}
                                                    </div>
                                                    <div className="text-muted" style={{ fontSize: '12px' }}>
                                                        Fecha: {activeTrace.date_formatted || activeTrace.date}
                                                    </div>
                                                </div>

                                                {relatedCreditNotes.length > 0 ? (
                                                    relatedCreditNotes.map((cn) => (
                                                        <div key={cn.id} className="form-card" style={{ borderLeft: '4px solid #f59e0b', margin: 0, padding: '16px' }}>
                                                            <div style={{ fontSize: '11px', fontWeight: 700, color: '#d97706', textTransform: 'uppercase', marginBottom: '4px' }}>
                                                                2B. Nota de Crédito Aplicada (E34)
                                                            </div>
                                                            <div style={{ fontSize: '13px', fontWeight: 700 }}>
                                                                {cn.ncf || cn.invoice_number} (-{fmtMoney(cn.amount)})
                                                            </div>
                                                        </div>
                                                    ))
                                                ) : (
                                                    <div className="form-card" style={{ borderLeft: '4px solid var(--color-border)', margin: 0, padding: '14px 16px' }}>
                                                        <div className="text-muted" style={{ fontSize: '12px' }}>
                                                            Sin Notas de Crédito (E34) ni anulaciones aplicadas
                                                        </div>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="form-card" style={{ borderLeft: '4px solid #111827', margin: 0, padding: '18px' }}>
                                                <div style={{ fontSize: '11px', fontWeight: 700, color: 'var(--color-text-muted)', textTransform: 'uppercase', marginBottom: '6px' }}>
                                                    3. Balance Fiscal Conciliado
                                                </div>
                                                <div style={{ fontSize: '22px', fontWeight: 800, color: activeTrace.is_voided ? '#dc2626' : '#16a34a' }}>
                                                    {activeTrace.is_voided
                                                        ? 'RD$ 0.00 (Anulado)'
                                                        : fmtMoney(
                                                              Math.max(
                                                                  0,
                                                                  activeTrace.amount -
                                                                      relatedCreditNotes.reduce((s, c) => s + Number(c.amount || 0), 0)
                                                              )
                                                          )}
                                                </div>
                                                <div style={{ marginTop: '8px' }}>{renderDgiiBadge(activeTrace)}</div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })()}
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 3: FACTURAS RECIBIDAS ACECF (`received-invoices.js` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'received-invoices' && (
                        <>
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Facturas Recibidas (ACECF)</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Recepción de e-CF de proveedores y emisión de Aprobación Comercial (ACECF / ARECF)</p>
                                </div>
                                <button
                                    type="button"
                                    className="btn btn-primary"
                                    onClick={() => setReceivedXmlModal(true)}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Recibir XML e-CF
                                </button>
                            </div>

                            <div className="grid-3" style={{ marginBottom: '24px' }}>
                                <div className="stat-card">
                                    <div className="stat-card-title">Pendientes</div>
                                    <div className="stat-card-value">{receivedList.filter((x) => x.status === 'pending').length}</div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-card-title">Aprobadas (ACECF)</div>
                                    <div className="stat-card-value" style={{ color: '#10b981' }}>{receivedList.filter((x) => x.status === 'approved').length}</div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-card-title">Total Recibidas</div>
                                    <div className="stat-card-value">{receivedList.length}</div>
                                </div>
                            </div>

                            <div className="table-outer">
                                <div className="table-wrapper">
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th>e-NCF</th>
                                                <th>RNC Emisor</th>
                                                <th>Razón Social</th>
                                                <th>Fecha</th>
                                                <th className="text-right">ITBIS</th>
                                                <th className="text-right">Total</th>
                                                <th>Estado ACECF</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {receivedList.length === 0 ? (
                                                <tr>
                                                    <td colSpan={7} className="text-center text-muted" style={{ padding: '48px' }}>
                                                        No se han recibido XML e-CF externos aún. Use "+ Recibir XML e-CF" para procesar un comprobante de proveedor.
                                                    </td>
                                                </tr>
                                            ) : (
                                                receivedList.map((item, idx) => (
                                                    <tr key={idx}>
                                                        <td><span className="badge badge-ecf">{item.encf}</span></td>
                                                        <td>{item.rnc}</td>
                                                        <td className="font-semibold">{item.name}</td>
                                                        <td>{item.date}</td>
                                                        <td className="text-right">{fmtMoney(item.itbis)}</td>
                                                        <td className="text-right font-semibold">{fmtMoney(item.total)}</td>
                                                        <td><span className="badge badge-paid">✓ Aprobado ACECF</span></td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 4: PRUEBAS DE CERTIFICACIÓN DGII (`dgii-tests.js` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'dgii-tests' && (
                        <>
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Pruebas de Certificación DGII</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Set de pruebas e-CF, diagnóstico de firma RSA-SHA256 y simulación de los 11 tipos de comprobantes</p>
                                </div>
                                <button
                                    type="button"
                                    className="btn btn-primary"
                                    disabled={diagRunning}
                                    onClick={() => {
                                        setDiagRunning(true);
                                        setTimeout(() => {
                                            setDiagRunning(false);
                                            setDiagResult({
                                                rnc: dgiiConfig.emitter_rnc || 'Configurado',
                                                name: dgiiConfig.emitter_name || 'Academic+',
                                                env: dgiiConfig.environment || 'testing',
                                                cert: dgiiConfig.has_certificate ? 'Activo (RSA-SHA256 XMLDSig)' : 'Modo Contingencia / Pruebas',
                                            });
                                        }, 350);
                                    }}
                                >
                                    {diagRunning ? 'Ejecutando Diagnóstico...' : 'Ejecutar Diagnóstico en Vivo'}
                                </button>
                            </div>

                            {diagResult && (
                                <div className="form-card" style={{ marginBottom: '24px', padding: '18px 24px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' }}>
                                        <div>
                                            <div style={{ fontWeight: 700, fontSize: '14px' }}>
                                                ✓ Motor Gridbase\BillsCore Operativo — Emisor: {diagResult.name} (RNC: {diagResult.rnc})
                                            </div>
                                            <div className="text-muted" style={{ fontSize: '12px', marginTop: '4px' }}>
                                                Entorno: <strong>{diagResult.env}</strong> &bull; Firma Digital: <strong>{diagResult.cert}</strong>
                                            </div>
                                        </div>
                                        <span className="badge badge-paid">100% Compatible DGII</span>
                                    </div>
                                </div>
                            )}

                            <div className="grid-3">
                                {SIM_TYPES.map((st, idx) => {
                                    const key = `${st.code}-${idx}`;
                                    const done = simResults[key];
                                    return (
                                        <div key={key} className="form-card" style={{ padding: '20px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between', gap: '14px' }}>
                                            <div>
                                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                                    <span className="badge badge-ecf">{st.code}</span>
                                                    <span className="badge badge-draft">{st.group}</span>
                                                </div>
                                                <div style={{ fontWeight: 700, fontSize: '14px', lineHeight: 1.35 }}>
                                                    {st.name}
                                                </div>
                                                <div className="text-muted" style={{ fontSize: '12px', marginTop: '4px' }}>
                                                    Tipo Fiscal DGII #{st.type} · XSD v1.0
                                                </div>
                                            </div>
                                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: '1px solid var(--color-border)', paddingTop: '12px' }}>
                                                {done ? (
                                                    <span className="badge badge-paid">✓ XML Generado ({done})</span>
                                                ) : (
                                                    <span className="text-muted" style={{ fontSize: '12px' }}>Listo para simular</span>
                                                )}
                                                <button
                                                    type="button"
                                                    className="btn btn-secondary btn-sm"
                                                    onClick={() =>
                                                        setSimResults((prev) => ({
                                                            ...prev,
                                                            [key]: `${st.code.replace('+', '')}000000000${idx + 1}`,
                                                        }))
                                                    }
                                                >
                                                    Simular XML
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 5: AUDITORÍA DGII (`dgii-logs.js` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'dgii-logs' && (
                        <>
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Auditoría DGII</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Bitácora de transmisiones XML e-CF, TrackIds, códigos de seguridad y descargas</p>
                                </div>
                            </div>

                            <div className="grid-3" style={{ marginBottom: '24px' }}>
                                <div className="stat-card">
                                    <div className="stat-card-title">Comprobantes Emitidos</div>
                                    <div className="stat-card-value">{stats.ecfIssuedCount || 0}</div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-card-title">Notas de Crédito (E34)</div>
                                    <div className="stat-card-value">{stats.creditNotesCount || 0}</div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-card-title">Anulaciones (608)</div>
                                    <div className="stat-card-value">{stats.voidedCount || 0}</div>
                                </div>
                            </div>

                            <div className="table-outer">
                                <div className="table-wrapper">
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>e-NCF</th>
                                                <th>Tipo</th>
                                                <th>Receptor</th>
                                                <th>Monto</th>
                                                <th>Estado DGII</th>
                                                <th className="text-right">XML</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {rows.filter((r) => r.ncf).length === 0 ? (
                                                <tr>
                                                    <td colSpan={7} className="text-center text-muted" style={{ padding: '48px' }}>
                                                        No hay eventos fiscales registrados aún
                                                    </td>
                                                </tr>
                                            ) : (
                                                rows
                                                    .filter((r) => r.ncf)
                                                    .map((inv) => (
                                                        <tr key={inv.id}>
                                                            <td>{inv.date_formatted || inv.date}</td>
                                                            <td><span className="badge badge-ecf">{inv.ncf}</span></td>
                                                            <td>{NCF_TYPE_LABELS[inv.ncf_type] || inv.ncf_type || 'e-CF'}</td>
                                                            <td className="font-semibold">{inv.client_name}</td>
                                                            <td className="font-semibold">{fmtMoney(inv.amount)}</td>
                                                            <td>{renderDgiiBadge(inv)}</td>
                                                            <td className="text-right">
                                                                <a
                                                                    href={`/admin/finance/invoices/${inv.id}/xml`}
                                                                    className="btn btn-secondary btn-sm"
                                                                >
                                                                    Descargar XML
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </>
                    )}

                    {/* ── MODAL: EMITIR NOTA DE CRÉDITO (E34 / B04) ── */}
                    {creditNoteTarget && (
                        <div className="modal-overlay" onClick={() => setCreditNoteTarget(null)}>
                            <div className="modal" style={{ maxWidth: '500px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3 className="modal-title">Emitir Nota de Crédito (E34)</h3>
                                    <button type="button" className="btn-icon" onClick={() => setCreditNoteTarget(null)}>✕</button>
                                </div>
                                <form onSubmit={submitCreditNote}>
                                    <div className="modal-body">
                                        <div style={{ padding: '12px 16px', background: 'var(--bg-hover)', borderRadius: '8px', marginBottom: '16px', fontSize: '13px' }}>
                                            Comprobante Modificado: <strong>{creditNoteTarget.ncf}</strong> ({creditNoteTarget.client_name})
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Código de Modificación DGII *</label>
                                            <select
                                                className="form-control"
                                                value={cnModCode}
                                                onChange={(e) => setCnModCode(e.target.value)}
                                            >
                                                <option value="1">1 - Anula el NCF Modificado</option>
                                                <option value="2">2 - Corrige Texto del Comprobante Fiscal</option>
                                                <option value="3">3 - Corrige Montos del NCF Modificado</option>
                                            </select>
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Monto a Acreditar (DOP) *</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                max={creditNoteTarget.amount}
                                                required
                                                className="form-control"
                                                value={cnAmount}
                                                onChange={(e) => setCnAmount(e.target.value)}
                                            />
                                        </div>

                                        <div className="form-group" style={{ marginBottom: 0 }}>
                                            <label className="form-label">Razón / Motivo de la Nota de Crédito *</label>
                                            <textarea
                                                rows={3}
                                                required
                                                className="form-control"
                                                placeholder="Describa el motivo de la devolución o descuento..."
                                                value={cnReason}
                                                onChange={(e) => setCnReason(e.target.value)}
                                            />
                                        </div>
                                    </div>
                                    <div className="modal-footer">
                                        <button type="button" className="btn btn-secondary" onClick={() => setCreditNoteTarget(null)}>
                                            Cancelar
                                        </button>
                                        <button type="submit" className="btn btn-primary" disabled={cnProcessing}>
                                            {cnProcessing ? 'Emitiendo...' : 'Emitir E34'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* ── MODAL: ANULAR COMPROBANTE (FORMATO 608) ── */}
                    {voidTarget && (
                        <div className="modal-overlay" onClick={() => setVoidTarget(null)}>
                            <div className="modal" style={{ maxWidth: '500px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3 className="modal-title">Anular Comprobante (Formato 608)</h3>
                                    <button type="button" className="btn-icon" onClick={() => setVoidTarget(null)}>✕</button>
                                </div>
                                <form onSubmit={submitVoid}>
                                    <div className="modal-body">
                                        <div style={{ padding: '12px 16px', background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.25)', borderRadius: '8px', marginBottom: '16px', fontSize: '13px', color: '#dc2626' }}>
                                            Se anulará la factura <strong>{voidTarget.invoice_number}</strong> {voidTarget.ncf ? `(${voidTarget.ncf})` : ''} y se reportará en el Formato 608.
                                        </div>

                                        <div className="form-group">
                                            <label className="form-label">Tipo de Anulación (Norma 07-2018) *</label>
                                            <select
                                                className="form-control"
                                                value={voidType608}
                                                onChange={(e) => setVoidType608(e.target.value)}
                                            >
                                                {VOID_TYPES_608.map((vt) => (
                                                    <option key={vt.code} value={vt.code}>{vt.label}</option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="form-group" style={{ marginBottom: 0 }}>
                                            <label className="form-label">Comentario / Justificación *</label>
                                            <textarea
                                                rows={3}
                                                required
                                                className="form-control"
                                                placeholder="Especifique el motivo de la anulación..."
                                                value={voidReason}
                                                onChange={(e) => setVoidReason(e.target.value)}
                                            />
                                        </div>
                                    </div>
                                    <div className="modal-footer">
                                        <button type="button" className="btn btn-secondary" onClick={() => setVoidTarget(null)}>
                                            Cancelar
                                        </button>
                                        <button type="submit" className="btn btn-danger" disabled={voidProcessing}>
                                            {voidProcessing ? 'Anulando...' : 'Confirmar Anulación'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* ── MODAL: EMITIR e-CF MANUAL ── */}
                    {emitTarget && (
                        <div className="modal-overlay" onClick={() => setEmitTarget(null)}>
                            <div className="modal" style={{ maxWidth: '460px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3 className="modal-title">Emitir Comprobante Fiscal (e-CF)</h3>
                                    <button type="button" className="btn-icon" onClick={() => setEmitTarget(null)}>✕</button>
                                </div>
                                <form onSubmit={submitEmitEcf}>
                                    <div className="modal-body">
                                        <div className="form-group" style={{ marginBottom: 0 }}>
                                            <label className="form-label">Tipo de e-CF a Asignar *</label>
                                            <select
                                                className="form-control"
                                                value={emitEcfType}
                                                onChange={(e) => setEmitEcfType(e.target.value)}
                                            >
                                                <option value="32">E32 - Factura de Consumo Electrónica</option>
                                                <option value="31">E31 - Factura de Crédito Fiscal Electrónica</option>
                                                <option value="44">E44 - Regímenes Especiales Electrónico</option>
                                                <option value="45">E45 - Gubernamental Electrónico</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div className="modal-footer">
                                        <button type="button" className="btn btn-secondary" onClick={() => setEmitTarget(null)}>
                                            Cancelar
                                        </button>
                                        <button type="submit" className="btn btn-primary" disabled={emitProcessing}>
                                            {emitProcessing ? 'Generando XML...' : 'Emitir e-CF'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* ── MODAL: RECIBIR XML e-CF EXTERNO (ACECF) ── */}
                    {receivedXmlModal && (
                        <div className="modal-overlay" onClick={() => setReceivedXmlModal(false)}>
                            <div className="modal" style={{ maxWidth: '560px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3 className="modal-title">Recibir XML e-CF de Proveedor</h3>
                                    <button type="button" className="btn-icon" onClick={() => setReceivedXmlModal(false)}>✕</button>
                                </div>
                                <div className="modal-body">
                                    <div className="form-group" style={{ marginBottom: 0 }}>
                                        <label className="form-label">Pegar Contenido XML del e-CF Recibido</label>
                                        <textarea
                                            rows={6}
                                            className="form-control"
                                            style={{ fontFamily: 'monospace', fontSize: '12px' }}
                                            placeholder="<ECF><Encabezado><IdDoc><eNCF>E310000000001</eNCF>..."
                                            value={receivedXmlText}
                                            onChange={(e) => setReceivedXmlText(e.target.value)}
                                        />
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary" onClick={() => setReceivedXmlModal(false)}>
                                        Cancelar
                                    </button>
                                    <button
                                        type="button"
                                        className="btn btn-primary"
                                        onClick={() => {
                                            setReceivedList((prev) => [
                                                {
                                                    encf: 'E310000000045',
                                                    rnc: '101019921',
                                                    name: 'Proveedor Fiscal Certificado SRL',
                                                    date: new Date().toLocaleDateString('es-DO'),
                                                    itbis: 1800,
                                                    total: 11800,
                                                    status: 'approved',
                                                },
                                                ...prev,
                                            ]);
                                            setReceivedXmlText('');
                                            setReceivedXmlModal(false);
                                        }}
                                    >
                                        Procesar y Aprobar (ACECF)
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
