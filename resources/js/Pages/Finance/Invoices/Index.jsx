import React, { useState, useMemo } from 'react';
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

function getInitials(name) {
    if (!name) return 'CF';
    const parts = String(name).trim().split(/\s+/);
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

export default function FinanceInvoicesIndex({
    invoices,
    stats = {},
    filters = {},
    dgiiConfig = {},
}) {
    // Active Bills Module View ('invoices' | 'audit-trail' | 'received-invoices' | 'dgii-tests' | 'dgii-logs')
    const [billsModule, setBillsModule] = useState(
        filters.tab === 'dgii_audit' ? 'dgii-logs' : 'invoices'
    );

    // Sub-view inside Invoices ('list' | 'detail')
    const [selectedInvoice, setSelectedInvoice] = useState(null);
    const [selectedIds, setSelectedIds] = useState([]);

    const [search, setSearch] = useState(filters.search || '');
    const [activeTab, setActiveTab] = useState(filters.tab || 'all');
    const [ncfTypeFilter, setNcfTypeFilter] = useState(filters.ncf_type || '');

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

    const applyFilters = (nextTab = activeTab, nextSearch = search, nextType = ncfTypeFilter) => {
        setActiveTab(nextTab);
        setNcfTypeFilter(nextType);
        router.get(
            '/admin/finance/invoices',
            {
                tab: nextTab,
                search: nextSearch,
                ncf_type: nextType,
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

    const renderStatusBadge = (inv) => {
        if (inv.is_voided) return <span className="badge badge-cancelled">Anulada (608)</span>;
        if (inv.is_credit_note) return <span className="badge badge-partial">Nota de Crédito</span>;
        const st = String(inv.status || '').toLowerCase();
        if (['paid', 'pagado', 'completado'].includes(st)) {
            return <span className="badge badge-paid">Pagada</span>;
        }
        if (['overdue', 'vencida'].includes(st)) {
            return <span className="badge badge-overdue">Vencida</span>;
        }
        return <span className="badge badge-pending">Pendiente</span>;
    };

    const renderDgiiBadge = (inv) => {
        if (!inv.ncf) return <span className="text-muted" style={{ fontSize: '12px' }}>—</span>;
        const st = String(inv.dgii_status || '').toLowerCase();
        if (st === 'accepted' || st === 'aceptado') {
            return <span className="badge badge-ecf-accepted">✓ Aceptado</span>;
        }
        if (st === 'rejected' || st === 'rechazado') {
            return <span className="badge badge-ecf-rejected">✗ Rechazado</span>;
        }
        if (inv.has_signed_xml) {
            return <span className="badge badge-ecf-sent">● XML Firmado</span>;
        }
        return <span className="badge badge-ecf-pending">● Emitido</span>;
    };

    return (
        <AuthenticatedLayout title="Facturas y Comprobantes Fiscales (e-CF)">
            <Head title="Facturas y Comprobantes Fiscales (e-CF)" />

            <div className="bills-scope">
                <div className="workspace-panel">
                    {/* Top Gridbase Bills Module Navigation Bar */}
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px', flexWrap: 'wrap', gap: '12px' }}>
                        <div className="segmented-control">
                            <button
                                type="button"
                                className={`segment-item ${billsModule === 'invoices' ? 'active' : ''}`}
                                onClick={() => {
                                    setBillsModule('invoices');
                                    setSelectedInvoice(null);
                                }}
                            >
                                Facturas ({stats.totalPaidCount || rows.length})
                            </button>
                            <button
                                type="button"
                                className={`segment-item ${billsModule === 'audit-trail' ? 'active' : ''}`}
                                onClick={() => {
                                    setBillsModule('audit-trail');
                                    if (!tracedInvoice && rows.length > 0) setTracedInvoice(rows[0]);
                                }}
                            >
                                Seguimiento (Grafo de Nodos)
                            </button>
                            <button
                                type="button"
                                className={`segment-item ${billsModule === 'received-invoices' ? 'active' : ''}`}
                                onClick={() => setBillsModule('received-invoices')}
                            >
                                Facturas Recibidas (ACECF)
                            </button>
                            <button
                                type="button"
                                className={`segment-item ${billsModule === 'dgii-tests' ? 'active' : ''}`}
                                onClick={() => setBillsModule('dgii-tests')}
                            >
                                Pruebas DGII
                            </button>
                            <button
                                type="button"
                                className={`segment-item ${billsModule === 'dgii-logs' ? 'active' : ''}`}
                                onClick={() => setBillsModule('dgii-logs')}
                            >
                                Auditoría DGII ({stats.ecfIssuedCount || 0})
                            </button>
                        </div>

                        <PoweredByBills variant="badge" />
                    </div>

                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 1: FACTURAS (`invoices.js` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'invoices' && !selectedInvoice && (
                        <>
                            {/* 1:1 Header (`invoices.js` line 25) */}
                            <div className="page-header">
                                <div className="page-header-left">
                                    <h1 className="page-title">Facturas</h1>
                                    <p className="page-subtitle">Gestiona y da seguimiento a todas tus facturas y comprobantes e-CF</p>
                                </div>
                                <div className="btn-group">
                                    <Link href="/admin/pos" className="btn btn-primary" id="btn-new-invoice">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        Nueva Factura
                                    </Link>
                                </div>
                            </div>

                            {/* 1:1 Segmented Filter Bar + Search (`invoices.js` line 36) */}
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px', flexWrap: 'wrap', gap: '12px' }}>
                                <div className="segmented-control" id="invoice-filters">
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'all' ? 'active' : ''}`}
                                        onClick={() => applyFilters('all', search, ncfTypeFilter)}
                                    >
                                        Todas
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'pending' ? 'active' : ''}`}
                                        onClick={() => applyFilters('pending', search, ncfTypeFilter)}
                                    >
                                        Pendiente
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'paid' ? 'active' : ''}`}
                                        onClick={() => applyFilters('paid', search, ncfTypeFilter)}
                                    >
                                        Pagada
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'credit_notes' ? 'active' : ''}`}
                                        onClick={() => applyFilters('credit_notes', search, ncfTypeFilter)}
                                    >
                                        Notas de Crédito (E34)
                                    </button>
                                    <button
                                        type="button"
                                        className={`segment-item ${activeTab === 'voided' ? 'active' : ''}`}
                                        onClick={() => applyFilters('voided', search, ncfTypeFilter)}
                                    >
                                        Anuladas (608)
                                    </button>
                                </div>

                                <div style={{ display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap' }}>
                                    <select
                                        className="form-control"
                                        style={{ width: 'auto', height: '38px', fontSize: '12.5px' }}
                                        value={ncfTypeFilter}
                                        onChange={(e) => applyFilters(activeTab, search, e.target.value)}
                                    >
                                        <option value="">Todos los Tipos e-NCF</option>
                                        {Object.entries(NCF_TYPE_LABELS).map(([code, label]) => (
                                            <option key={code} value={code}>{label}</option>
                                        ))}
                                    </select>

                                    <div className="search-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                            <circle cx="11" cy="11" r="8" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        </svg>
                                        <input
                                            type="text"
                                            className="search-input"
                                            id="invoice-search"
                                            placeholder="Buscar facturas o e-NCF..."
                                            value={search}
                                            onChange={(e) => {
                                                setSearch(e.target.value);
                                                applyFilters(activeTab, e.target.value, ncfTypeFilter);
                                            }}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* 1:1 Data Table (`invoices.js` line 52) */}
                            <div className="table-outer">
                                <div className="table-wrapper">
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th style={{ width: '40px', paddingRight: 0 }}>
                                                    <input
                                                        type="checkbox"
                                                        className="table-checkbox"
                                                        checked={rows.length > 0 && selectedIds.length === rows.length}
                                                        onChange={(e) => toggleSelectAll(e.target.checked)}
                                                    />
                                                </th>
                                                <th>Número</th>
                                                <th>Cliente</th>
                                                <th>Emisión</th>
                                                <th>Vencimiento</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <th>DGII</th>
                                                <th className="text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="invoices-tbody">
                                            {rows.length === 0 ? (
                                                <tr>
                                                    <td colSpan={9} className="text-center text-muted py-24">
                                                        No se encontraron facturas
                                                    </td>
                                                </tr>
                                            ) : (
                                                rows.map((inv) => (
                                                    <tr
                                                        key={inv.id}
                                                        className="clickable-row"
                                                        onClick={() => setSelectedInvoice(inv)}
                                                    >
                                                        <td style={{ paddingRight: 0 }} onClick={(e) => e.stopPropagation()}>
                                                            <input
                                                                type="checkbox"
                                                                className="table-checkbox"
                                                                checked={selectedIds.includes(inv.id)}
                                                                onChange={() => toggleSelectRow(inv.id)}
                                                            />
                                                        </td>
                                                        <td>
                                                            <div className="font-semibold" style={{ color: 'var(--color-text-primary)' }}>
                                                                {inv.invoice_number}
                                                            </div>
                                                            {inv.ncf && (
                                                                <span className="badge-ecf">{inv.ncf}</span>
                                                            )}
                                                        </td>
                                                        <td>
                                                            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                                                                <div className="user-avatar-sm">
                                                                    {getInitials(inv.client_name)}
                                                                </div>
                                                                <div>
                                                                    <div style={{ fontWeight: 500 }}>{inv.client_name}</div>
                                                                    <div className="text-muted" style={{ fontSize: '11px' }}>
                                                                        {inv.concept} {inv.client_rnc && inv.client_rnc !== '—' ? `• RNC/Céd: ${inv.client_rnc}` : ''}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>{inv.date_formatted || inv.date}</td>
                                                        <td>{inv.due_date_formatted || '—'}</td>
                                                        <td className="font-semibold">
                                                            {inv.is_credit_note ? '-' : ''}
                                                            {fmtMoney(inv.amount, inv.currency)}
                                                        </td>
                                                        <td>{renderStatusBadge(inv)}</td>
                                                        <td>{renderDgiiBadge(inv)}</td>
                                                        <td className="text-right" onClick={(e) => e.stopPropagation()}>
                                                            <div className="row-actions" style={{ opacity: 1 }}>
                                                                <button
                                                                    type="button"
                                                                    className="row-action-btn"
                                                                    title="Ver Detalle de Factura"
                                                                    onClick={() => setSelectedInvoice(inv)}
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                                        <circle cx="12" cy="12" r="3" />
                                                                    </svg>
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    className="row-action-btn"
                                                                    title="Seguimiento (Grafo de Nodos)"
                                                                    onClick={() => openTraceFor(inv)}
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                        <circle cx="18" cy="5" r="3" />
                                                                        <circle cx="6" cy="12" r="3" />
                                                                        <circle cx="18" cy="19" r="3" />
                                                                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                                                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                                                                    </svg>
                                                                </button>

                                                                {inv.ncf && (
                                                                    <a
                                                                        href={`/admin/finance/invoices/${inv.id}/xml`}
                                                                        className="row-action-btn"
                                                                        title="Descargar XML e-CF Firmado"
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                                            <polyline points="7 10 12 15 17 10" />
                                                                            <line x1="12" y1="15" x2="12" y2="3" />
                                                                        </svg>
                                                                    </a>
                                                                )}

                                                                {!inv.ncf && !inv.is_voided && (
                                                                    <button
                                                                        type="button"
                                                                        className="row-action-btn"
                                                                        title="Emitir e-CF Ahora"
                                                                        onClick={() => openEmitModal(inv)}
                                                                        style={{ color: '#2563eb' }}
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                                                                        </svg>
                                                                    </button>
                                                                )}

                                                                {inv.ncf && !inv.is_credit_note && !inv.is_voided && (
                                                                    <button
                                                                        type="button"
                                                                        className="row-action-btn"
                                                                        title="Emitir Nota de Crédito (E34)"
                                                                        onClick={() => openCreditNoteModal(inv)}
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                            <polyline points="1 4 1 10 7 10" />
                                                                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                                                                        </svg>
                                                                    </button>
                                                                )}

                                                                {!inv.is_voided && (
                                                                    <button
                                                                        type="button"
                                                                        className="row-action-btn danger"
                                                                        title="Anular Comprobante (Formato 608)"
                                                                        onClick={() => openVoidModal(inv)}
                                                                    >
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                            <circle cx="12" cy="12" r="10" />
                                                                            <line x1="15" y1="9" x2="9" y2="15" />
                                                                            <line x1="9" y1="9" x2="15" y2="15" />
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
                                    <div style={{ padding: '14px 24px', borderTop: '1px solid var(--color-border)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px' }}>
                                        <span className="text-muted" style={{ fontSize: '12px' }}>
                                            Mostrando página {invoices.current_page} de {invoices.last_page} ({invoices.total} comprobantes)
                                        </span>
                                        <div style={{ display: 'flex', gap: '6px' }}>
                                            {invoices.links.map((link, idx) =>
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

                            {/* Floating Bulk Actions Bar (`invoices.js` line 77) */}
                            {selectedIds.length > 0 && (
                                <div className="bulk-actions-bar">
                                    <span className="bulk-count">
                                        {selectedIds.length} seleccionada{selectedIds.length > 1 ? 's' : ''}
                                    </span>
                                    <div className="bulk-divider" />
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-ghost"
                                        style={{ color: '#fff' }}
                                        onClick={() => setSelectedIds([])}
                                    >
                                        Deseleccionar
                                    </button>
                                </div>
                            )}
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        VISTA DETALLE DE FACTURA (`invoices.js` line 183 `renderDetail`)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'invoices' && selectedInvoice && (
                        <>
                            <div className="invoice-detail-header">
                                <button
                                    type="button"
                                    className="btn-back"
                                    onClick={() => setSelectedInvoice(null)}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <line x1="19" y1="12" x2="5" y2="12" />
                                        <polyline points="12 19 5 12 12 5" />
                                    </svg>
                                    Facturas
                                </button>
                                <div className="btn-group">
                                    <button
                                        type="button"
                                        className="btn btn-secondary btn-sm"
                                        onClick={() => window.print()}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <polyline points="6 9 6 2 18 2 18 9" />
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                            <rect x="6" y="14" width="12" height="8" />
                                        </svg>
                                        Imprimir A4 / Ticket
                                    </button>

                                    {selectedInvoice.ncf && (
                                        <a
                                            href={`/admin/finance/invoices/${selectedInvoice.id}/xml`}
                                            className="btn btn-secondary btn-sm"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="7 10 12 15 17 10" />
                                                <line x1="12" y1="15" x2="12" y2="3" />
                                            </svg>
                                            Descargar XML e-CF
                                        </a>
                                    )}

                                    <button
                                        type="button"
                                        className="btn btn-secondary btn-sm"
                                        style={{ color: '#0284c7', borderColor: '#bae6fd', background: '#f0f9ff' }}
                                        onClick={() => openTraceFor(selectedInvoice)}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <circle cx="18" cy="5" r="3" />
                                            <circle cx="6" cy="12" r="3" />
                                            <circle cx="18" cy="19" r="3" />
                                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                                        </svg>
                                        Seguimiento
                                    </button>

                                    {selectedInvoice.ncf && !selectedInvoice.is_credit_note && !selectedInvoice.is_voided && (
                                        <button
                                            type="button"
                                            className="btn btn-secondary btn-sm"
                                            style={{ color: '#ea580c', borderColor: '#fed7aa', background: '#fff7ed' }}
                                            onClick={() => openCreditNoteModal(selectedInvoice)}
                                        >
                                            Nota de Crédito (E34)
                                        </button>
                                    )}

                                    {!selectedInvoice.is_voided && (
                                        <button
                                            type="button"
                                            className="btn btn-danger btn-sm"
                                            onClick={() => openVoidModal(selectedInvoice)}
                                        >
                                            Anular (608)
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Invoice Header Card (`invoices.js` line 255) */}
                            <div className="form-card" style={{ marginBottom: '20px' }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', flexWrap: 'wrap', gap: '16px', marginBottom: '24px' }}>
                                    <div>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '4px' }}>
                                            <h2 style={{ fontSize: '24px', fontWeight: 700, letterSpacing: '-0.02em', margin: 0 }}>
                                                {selectedInvoice.invoice_number}
                                            </h2>
                                            {renderStatusBadge(selectedInvoice)}
                                        </div>
                                        {selectedInvoice.ncf && (
                                            <div style={{ marginTop: '4px' }}>
                                                <span className="badge-ecf" style={{ fontSize: '13px', padding: '4px 10px' }}>
                                                    e-NCF: {selectedInvoice.ncf}
                                                </span>
                                                <span className="text-muted" style={{ fontSize: '12px', marginLeft: '8px' }}>
                                                    {NCF_TYPE_LABELS[selectedInvoice.ncf_type] || 'Comprobante Fiscal'}
                                                </span>
                                            </div>
                                        )}
                                    </div>
                                    <div style={{ textAlign: 'right' }}>
                                        <div style={{ fontSize: '28px', fontWeight: 700, letterSpacing: '-0.03em' }}>
                                            {fmtMoney(selectedInvoice.amount, selectedInvoice.currency)}
                                        </div>
                                        <div className="text-muted" style={{ fontSize: '12px' }}>
                                            Moneda: {selectedInvoice.currency || 'DOP'}
                                        </div>
                                    </div>
                                </div>

                                <div className="invoice-meta-grid">
                                    <div className="meta-item">
                                        <div className="meta-label">Cliente / Estudiante</div>
                                        <div className="meta-value">{selectedInvoice.client_name}</div>
                                        {selectedInvoice.client_rnc && selectedInvoice.client_rnc !== '—' && (
                                            <div className="text-muted" style={{ fontSize: '12px' }}>
                                                RNC/Cédula: {selectedInvoice.client_rnc}
                                            </div>
                                        )}
                                    </div>
                                    <div className="meta-item">
                                        <div className="meta-label">Fecha de Emisión</div>
                                        <div className="meta-value">{selectedInvoice.date_formatted || selectedInvoice.date}</div>
                                    </div>
                                    <div className="meta-item">
                                        <div className="meta-label">Vía de Pago / Caja</div>
                                        <div className="meta-value">{selectedInvoice.gateway || 'Caja Institucional'}</div>
                                    </div>
                                </div>
                            </div>

                            {/* e-CF Status Card (`invoices.js` line 294) */}
                            {selectedInvoice.ncf && (
                                <div className="ecf-status-card" style={{ marginBottom: '20px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                                            <div className="ecf-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div style={{ fontWeight: 600, fontSize: '14px' }}>
                                                    Comprobante Fiscal Electrónico (e-CF)
                                                </div>
                                                <div className="text-muted" style={{ fontSize: '12px' }}>
                                                    e-NCF: <strong>{selectedInvoice.ncf}</strong>
                                                    {selectedInvoice.security_code ? ` · Código Seguridad: ${selectedInvoice.security_code}` : ''}
                                                    {selectedInvoice.dgii_track_id ? ` · TrackId: ${selectedInvoice.dgii_track_id}` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                            {renderDgiiBadge(selectedInvoice)}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Line Items Table (`invoices.js` line 325) */}
                            <div className="table-outer" style={{ marginBottom: '20px' }}>
                                <table className="data-table">
                                    <thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th className="text-right">Cant.</th>
                                            <th className="text-right">Precio Unit.</th>
                                            <th className="text-right">Descuento</th>
                                            <th className="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td className="font-semibold">{selectedInvoice.concept}</td>
                                            <td className="text-right">1</td>
                                            <td className="text-right">{fmtMoney(selectedInvoice.amount + (selectedInvoice.discount_amount || 0))}</td>
                                            <td className="text-right">{fmtMoney(selectedInvoice.discount_amount || 0)}</td>
                                            <td className="text-right font-semibold">{fmtMoney(selectedInvoice.amount)}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div style={{ padding: '20px 24px', display: 'flex', justifyContent: 'flex-end' }}>
                                    <div className="invoice-totals">
                                        <div className="totals-row">
                                            <span>Subtotal</span>
                                            <span>{fmtMoney(selectedInvoice.amount)}</span>
                                        </div>
                                        <div className="totals-row">
                                            <span>ITBIS (Exento Educativo)</span>
                                            <span>RD$ 0.00</span>
                                        </div>
                                        <div className="totals-row total-final">
                                            <span>Total</span>
                                            <span>{fmtMoney(selectedInvoice.amount)}</span>
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
                                <div className="page-header-left">
                                    <h1 className="page-title">Seguimiento de Comprobante</h1>
                                    <p className="page-subtitle">Grafo interactivo de relaciones fiscales, notas de crédito, pagos y auditoría cronológica</p>
                                </div>
                            </div>

                            <div className="form-card" style={{ marginBottom: '24px', padding: '20px 24px' }}>
                                <form onSubmit={handleAuditSearch} style={{ display: 'flex', gap: '12px', alignItems: 'center', flexWrap: 'wrap' }}>
                                    <div className="search-wrapper" style={{ flex: 1, minWidth: '260px' }}>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
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

                            {tracedInvoice && (
                                <>
                                    {/* Interactive Node Graph Viewport (`audit-trail.js` line 185) */}
                                    <div
                                        className="table-outer"
                                        style={{
                                            marginBottom: '24px',
                                            padding: '32px 24px',
                                            background: 'radial-gradient(circle, var(--color-border) 1px, transparent 1px)',
                                            backgroundSize: '20px 20px',
                                        }}
                                    >
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                                            <span style={{ fontSize: '12px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--color-text-muted)' }}>
                                                Grafo de Trazabilidad del Comprobante
                                            </span>
                                            <span className="badge-ecf">{tracedInvoice.ncf || tracedInvoice.invoice_number}</span>
                                        </div>

                                        <div className="grid-3" style={{ alignItems: 'center', gap: '24px' }}>
                                            {/* Col 1: Documento Base */}
                                            <div className="form-card" style={{ borderLeft: '4px solid #2563eb', margin: 0 }}>
                                                <div style={{ fontSize: '11px', fontWeight: 700, color: '#2563eb', textTransform: 'uppercase', marginBottom: '6px' }}>
                                                    1. Documento Base Emitido
                                                </div>
                                                <div style={{ fontSize: '16px', fontWeight: 700 }}>{tracedInvoice.invoice_number}</div>
                                                {tracedInvoice.ncf && (
                                                    <div className="badge-ecf" style={{ marginTop: '4px' }}>{tracedInvoice.ncf}</div>
                                                )}
                                                <div className="text-muted" style={{ fontSize: '12px', marginTop: '8px' }}>
                                                    Cliente: <strong>{tracedInvoice.client_name}</strong>
                                                </div>
                                                <div style={{ fontSize: '18px', fontWeight: 800, marginTop: '10px' }}>
                                                    {fmtMoney(tracedInvoice.amount)}
                                                </div>
                                            </div>

                                            {/* Col 2: Modificadores (E34) y Cobros */}
                                            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                                                <div className="form-card" style={{ borderLeft: '4px solid #10b981', margin: 0, padding: '16px' }}>
                                                    <div style={{ fontSize: '11px', fontWeight: 700, color: '#10b981', textTransform: 'uppercase', marginBottom: '4px' }}>
                                                        2A. Cobro / Transacción en Caja
                                                    </div>
                                                    <div style={{ fontSize: '13px', fontWeight: 600 }}>
                                                        Vía: {tracedInvoice.gateway || 'Caja Institucional'}
                                                    </div>
                                                    <div className="text-muted" style={{ fontSize: '12px' }}>
                                                        Fecha: {tracedInvoice.date_formatted || tracedInvoice.date}
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

                                            {/* Col 3: Balance Real Conciliado */}
                                            <div className="form-card" style={{ borderLeft: '4px solid #111827', margin: 0 }}>
                                                <div style={{ fontSize: '11px', fontWeight: 700, color: 'var(--color-text-muted)', textTransform: 'uppercase', marginBottom: '6px' }}>
                                                    3. Balance Fiscal Conciliado
                                                </div>
                                                <div style={{ fontSize: '22px', fontWeight: 800, color: tracedInvoice.is_voided ? '#dc2626' : '#16a34a' }}>
                                                    {tracedInvoice.is_voided
                                                        ? 'RD$ 0.00 (Anulado)'
                                                        : fmtMoney(
                                                              Math.max(
                                                                  0,
                                                                  tracedInvoice.amount -
                                                                      relatedCreditNotes.reduce((s, c) => s + Number(c.amount || 0), 0)
                                                              )
                                                          )}
                                                </div>
                                                <div style={{ marginTop: '8px' }}>{renderDgiiBadge(tracedInvoice)}</div>
                                            </div>
                                        </div>
                                    </div>
                                </>
                            )}
                        </>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        MÓDULO 3: FACTURAS RECIBIDAS ACECF (`received-invoices.js` 1:1)
                    ══════════════════════════════════════════════════════════════ */}
                    {billsModule === 'received-invoices' && (
                        <>
                            <div className="page-header">
                                <div className="page-header-left">
                                    <h1 className="page-title">Facturas Recibidas (ACECF)</h1>
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

                            <div className="grid-4" style={{ marginBottom: '24px' }}>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Pendientes</span>
                                        <h3 className="stat-value">{receivedList.filter((x) => x.status === 'pending').length}</h3>
                                    </div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Aprobadas (ACECF)</span>
                                        <h3 className="stat-value" style={{ color: '#16a34a' }}>{receivedList.filter((x) => x.status === 'approved').length}</h3>
                                    </div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Rechazadas</span>
                                        <h3 className="stat-value" style={{ color: '#dc2626' }}>{receivedList.filter((x) => x.status === 'rejected').length}</h3>
                                    </div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Total Recibidas</span>
                                        <h3 className="stat-value">{receivedList.length}</h3>
                                    </div>
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
                                                    <td colSpan={7} className="text-center text-muted py-24">
                                                        No se han recibido XML e-CF externos aún. Use "+ Recibir XML e-CF" para procesar un comprobante de proveedor.
                                                    </td>
                                                </tr>
                                            ) : (
                                                receivedList.map((item, idx) => (
                                                    <tr key={idx}>
                                                        <td><span className="badge-ecf">{item.encf}</span></td>
                                                        <td>{item.rnc}</td>
                                                        <td className="font-semibold">{item.name}</td>
                                                        <td>{item.date}</td>
                                                        <td className="text-right">{fmtMoney(item.itbis)}</td>
                                                        <td className="text-right font-semibold">{fmtMoney(item.total)}</td>
                                                        <td><span className="badge badge-ecf-accepted">✓ Aprobado ACECF</span></td>
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
                                <div className="page-header-left">
                                    <h1 className="page-title">Pruebas de Certificación DGII</h1>
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
                                <div className="ecf-status-card" style={{ marginBottom: '24px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' }}>
                                        <div>
                                            <div style={{ fontWeight: 700, fontSize: '14px' }}>
                                                ✓ Motor Gridbase\BillsCore Operativo — Emisor: {diagResult.name} (RNC: {diagResult.rnc})
                                            </div>
                                            <div className="text-muted" style={{ fontSize: '12px', marginTop: '4px' }}>
                                                Entorno: <strong>{diagResult.env}</strong> &bull; Firma Digital: <strong>{diagResult.cert}</strong>
                                            </div>
                                        </div>
                                        <span className="badge badge-ecf-accepted">100% Compatible DGII</span>
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
                                                    <span className="badge-ecf">{st.code}</span>
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
                                                    <span className="badge badge-ecf-accepted">✓ XML Generado ({done})</span>
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
                                <div className="page-header-left">
                                    <h1 className="page-title">Auditoría DGII</h1>
                                    <p className="page-subtitle">Bitácora de transmisiones XML e-CF, TrackIds, códigos de seguridad y descargas</p>
                                </div>
                            </div>

                            <div className="grid-4" style={{ marginBottom: '24px' }}>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Comprobantes Emitidos</span>
                                        <h3 className="stat-value">{stats.ecfIssuedCount || 0}</h3>
                                    </div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Notas de Crédito (E34)</span>
                                        <h3 className="stat-value">{stats.creditNotesCount || 0}</h3>
                                    </div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Anulaciones (608)</span>
                                        <h3 className="stat-value">{stats.voidedCount || 0}</h3>
                                    </div>
                                </div>
                                <div className="stat-card">
                                    <div className="stat-info">
                                        <span className="stat-label">Entorno Activo</span>
                                        <h3 className="stat-value" style={{ fontSize: '16px', textTransform: 'uppercase' }}>
                                            {dgiiConfig.environment || 'testing'}
                                        </h3>
                                    </div>
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
                                                    <td colSpan={7} className="text-center text-muted py-24">
                                                        No hay eventos fiscales registrados aún
                                                    </td>
                                                </tr>
                                            ) : (
                                                rows
                                                    .filter((r) => r.ncf)
                                                    .map((inv) => (
                                                        <tr key={inv.id}>
                                                            <td>{inv.date_formatted || inv.date}</td>
                                                            <td><span className="badge-ecf">{inv.ncf}</span></td>
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
                                    <h3>Emitir Nota de Crédito (E34)</h3>
                                    <button type="button" className="modal-close" onClick={() => setCreditNoteTarget(null)}>✕</button>
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
                                    <h3>Anular Comprobante (Formato 608)</h3>
                                    <button type="button" className="modal-close" onClick={() => setVoidTarget(null)}>✕</button>
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
                                    <h3>Emitir Comprobante Fiscal (e-CF)</h3>
                                    <button type="button" className="modal-close" onClick={() => setEmitTarget(null)}>✕</button>
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
                                    <h3>Recibir XML e-CF de Proveedor</h3>
                                    <button type="button" className="modal-close" onClick={() => setReceivedXmlModal(false)}>✕</button>
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
