import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import PoweredByBills from '@/components/bills/PoweredByBills';

const MONTHS = [
    { val: '01', label: '01 - Enero' },
    { val: '02', label: '02 - Febrero' },
    { val: '03', label: '03 - Marzo' },
    { val: '04', label: '04 - Abril' },
    { val: '05', label: '05 - Mayo' },
    { val: '06', label: '06 - Junio' },
    { val: '07', label: '07 - Julio' },
    { val: '08', label: '08 - Agosto' },
    { val: '09', label: '09 - Septiembre' },
    { val: '10', label: '10 - Octubre' },
    { val: '11', label: '11 - Noviembre' },
    { val: '12', label: '12 - Diciembre' },
];

const EXPENSE_TYPES_606 = [
    { code: '01', label: '01 - Personal' },
    { code: '02', label: '02 - Trabajos y Servicios' },
    { code: '03', label: '03 - Arrendamientos' },
    { code: '04', label: '04 - Activos Fijos' },
    { code: '05', label: '05 - Gastos Representación' },
    { code: '06', label: '06 - Otras Deducciones' },
    { code: '07', label: '07 - Financieros' },
    { code: '08', label: '08 - Extraordinarios' },
    { code: '09', label: '09 - Costo de Venta' },
    { code: '10', label: '10 - Adquisición Activos' },
    { code: '11', label: '11 - Seguros' },
];

const PAYMENT_METHODS_606 = [
    { code: '01', label: '01 - Efectivo' },
    { code: '02', label: '02 - Cheques/Transferencia' },
    { code: '03', label: '03 - Tarjeta' },
    { code: '04', label: '04 - Crédito' },
    { code: '05', label: '05 - Permuta' },
    { code: '06', label: '06 - Nota de Crédito' },
    { code: '07', label: '07 - Mixto' },
];

const ANULATION_TYPES_608 = [
    { code: '01', label: '01 - Deterioro de Factura Pre-Impresa' },
    { code: '02', label: '02 - Errores de Impresión (Factura Pre-Impresa)' },
    { code: '03', label: '03 - Impresión Defectuosa' },
    { code: '04', label: '04 - Duplicidad de Factura' },
    { code: '05', label: '05 - Corrección de la Información' },
    { code: '06', label: '06 - Cambio de Productos' },
    { code: '07', label: '07 - Devolución de Productos' },
    { code: '08', label: '08 - Omisión de Productos' },
    { code: '09', label: '09 - Errores en Secuencias de NCF' },
];

export default function Index({
    auth,
    yearMonth,
    records606 = [],
    records607 = [],
    records608 = [],
    validation606,
    validation607,
    validation608,
    it1Declaration,
    ir2Declaration,
    otherDeclarations = {},
    billsCore,
}) {
    const initialParts = (yearMonth || '2026-11').split('-');
    const [selectedYear, setSelectedYear] = useState(initialParts[0] || '2026');
    const [selectedMonth, setSelectedMonth] = useState(initialParts[1] || '11');

    const [currentCategory, setCurrentCategory] = useState('general');
    const [currentTab, setCurrentTab] = useState('607');

    const [rows606, setRows606] = useState(records606);
    const [rows607, setRows607] = useState(records607);
    const [rows608, setRows608] = useState(records608);

    const [prevalModal, setPrevalModal] = useState(null);
    const [isPrevalidating, setIsPrevalidating] = useState(false);

    useEffect(() => {
        setRows606(records606);
        setRows607(records607);
        setRows608(records608);
    }, [records606, records607, records608]);

    const formatCurrency = (val) => {
        const num = Number(val || 0);
        return `RD$ ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    const formatDgiiDate = (dateStr) => {
        if (!dateStr || String(dateStr).length !== 8) return dateStr || '—';
        const s = String(dateStr);
        return `${s.slice(6, 8)}/${s.slice(4, 6)}/${s.slice(0, 4)}`;
    };

    const handleLoadPeriod = () => {
        const ym = `${selectedYear}-${selectedMonth}`;
        router.get(
            '/admin/finance/dgii-reports',
            { yearMonth: ym },
            { preserveState: true, preserveScroll: true }
        );
    };

    const handleCategoryChange = (cat) => {
        setCurrentCategory(cat);
        if (cat === 'general') setCurrentTab('607');
        else if (cat === 'rst') setCurrentTab('rs2');
        else if (cat === 'sectorial') setCurrentTab('dss');
    };

    const update606Field = (idx, field, value) => {
        setRows606((prev) => {
            const copy = [...prev];
            copy[idx] = { ...copy[idx], [field]: value };
            if (field === 'forma_pago' && value === '04') {
                copy[idx].fecha_pago = '';
            }
            return copy;
        });
    };

    const update608Field = (idx, field, value) => {
        setRows608((prev) => {
            const copy = [...prev];
            copy[idx] = { ...copy[idx], [field]: value };
            return copy;
        });
    };

    const handlePrevalidate = async () => {
        const activeRecords =
            currentTab === '607' ? rows607 : currentTab === '606' ? rows606 : rows608;
        const periodStr = `${selectedYear}${selectedMonth}`;

        setIsPrevalidating(true);
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch(
                `/admin/finance/dgii-reports/${currentTab}/prevalidate`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                    },
                    body: JSON.stringify({
                        period: periodStr,
                        rnc: billsCore?.emitterRnc,
                        records: activeRecords,
                    }),
                }
            );
            const res = await response.json();
            setPrevalModal(res);
        } catch (err) {
            const fallback =
                currentTab === '607'
                    ? validation607
                    : currentTab === '606'
                      ? validation606
                      : validation608;
            setPrevalModal(fallback);
        } finally {
            setIsPrevalidating(false);
        }
    };

    const handleExportExcel = () => {
        const ym = `${selectedYear}-${selectedMonth}`;
        window.location.href = `/admin/finance/dgii-reports/${currentTab}/export-excel?yearMonth=${ym}`;
    };

    const handleExportTxt = () => {
        const ym = `${selectedYear}-${selectedMonth}`;
        if (currentTab === '607') {
            window.location.href = `/admin/finance/dgii-reports/download-607?yearMonth=${ym}`;
        } else if (currentTab === '606') {
            window.location.href = `/admin/finance/dgii-reports/download-606?yearMonth=${ym}`;
        } else if (currentTab === '608') {
            window.location.href = `/admin/finance/dgii-reports/download-608?yearMonth=${ym}`;
        }
    };

    const isFormatTab = ['607', '606', '608'].includes(currentTab);

    const getExcelBtnLabel = () => {
        if (currentTab === 'it1') return 'Descargar Formulario Oficial IT-1 (.xls)';
        if (currentTab === 'ir2') return 'Descargar Formulario Oficial IR-2 (.xls)';
        if (currentTab === 'dss') return 'Descargar Formulario Oficial DSS-07 (.xls)';
        if (currentTab === 'daf') return 'Descargar Formulario Oficial DAF (.xls)';
        if (['rs1', 'rs2', 'rs3', 'rs4'].includes(currentTab)) {
            return `Descargar Formulario Oficial ${currentTab.toUpperCase()} (.xlsx)`;
        }
        return 'Descargar Plantilla Excel (.xls)';
    };

    const dIt1 = it1Declaration || {};
    const it1 = dIt1.it1 || {};
    const axA = dIt1.anexo_a || {};

    const dIr2 = ir2Declaration || {};
    const ir2 = dIr2.ir2 || {};
    const b1 = dIr2.b1 || {};
    const a1 = dIr2.a1 || {};

    const dRs1 = otherDeclarations.rs1 || {};
    const rs1 = dRs1.rs1 || {};
    const dRs2 = otherDeclarations.rs2 || {};
    const rs2 = dRs2.rs2 || {};
    const dRs3 = otherDeclarations.rs3 || {};
    const rs3 = dRs3.rs3 || {};
    const dRs4 = otherDeclarations.rs4 || {};
    const rs4 = dRs4.rs4 || {};
    const dDss = otherDeclarations.dss || {};
    const dss = dDss.dss || {};
    const dDaf = otherDeclarations.daf || {};
    const daf = dDaf.daf || {};

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Reportes Fiscales DGII" />

            <div className="bills-scope">
                <div className="workspace-panel">
                    {/* 1:1 Gridbase Bills Header (`reports.js` line 73) */}
                    <div className="page-header">
                        <div className="page-header-left" style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                            <h1 className="page-title">Reportes Fiscales DGII</h1>
                            <PoweredByBills variant="badge" />
                        </div>
                    </div>

                    {/* 1:1 Period Selector Bar (`reports.js` line 79) */}
                    <div className="table-outer" style={{ marginBottom: '24px' }}>
                        <div style={{ padding: '20px 24px', display: 'flex', gap: '16px', alignItems: 'flex-end', flexWrap: 'wrap', borderBottom: '1px solid var(--color-border)' }}>
                            <div className="form-group" style={{ margin: 0, minWidth: '140px' }}>
                                <label className="form-label">Año Fiscal</label>
                                <select
                                    id="report-year"
                                    className="form-control"
                                    value={selectedYear}
                                    onChange={(e) => setSelectedYear(e.target.value)}
                                >
                                    {['2026', '2025', '2024'].map((y) => (
                                        <option key={y} value={y}>{y}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="form-group" style={{ margin: 0, minWidth: '160px' }}>
                                <label className="form-label">Mes del Período</label>
                                <select
                                    id="report-month"
                                    className="form-control"
                                    value={selectedMonth}
                                    onChange={(e) => setSelectedMonth(e.target.value)}
                                >
                                    {MONTHS.map((m) => (
                                        <option key={m.val} value={m.val}>{m.label}</option>
                                    ))}
                                </select>
                            </div>
                            <button
                                type="button"
                                className="btn btn-primary"
                                id="btn-load-reports"
                                style={{ height: '40px' }}
                                onClick={handleLoadPeriod}
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                </svg>
                                Cargar Período
                            </button>
                        </div>
                    </div>

                    {/* 1:1 Selector Principal de Régimen / Categoría (`reports.js` line 98) */}
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '12px', marginBottom: '14px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' }}>
                            <span style={{ fontSize: '12px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.05em', color: 'var(--color-text-muted)' }}>
                                Régimen / Categoría:
                            </span>
                            <div className="segmented-control" id="report-category-tabs" style={{ background: 'var(--bg-hover)' }}>
                                <button
                                    type="button"
                                    onClick={() => handleCategoryChange('general')}
                                    className={`segment-item ${currentCategory === 'general' ? 'active' : ''}`}
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                >
                                    <span style={{ display: 'inline-block', width: '8px', height: '8px', borderRadius: '50%', background: '#16a34a' }} />
                                    Régimen General / Obligatorios (5)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleCategoryChange('rst')}
                                    className={`segment-item ${currentCategory === 'rst' ? 'active' : ''}`}
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                >
                                    <span style={{ display: 'inline-block', width: '8px', height: '8px', borderRadius: '50%', background: '#8b5cf6' }} />
                                    Régimen Simplificado - RST (4)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleCategoryChange('sectorial')}
                                    className={`segment-item ${currentCategory === 'sectorial' ? 'active' : ''}`}
                                    style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                >
                                    <span style={{ display: 'inline-block', width: '8px', height: '8px', borderRadius: '50%', background: '#0d9488' }} />
                                    Sectoriales Especiales (2)
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* 1:1 Sub-selector de Formularios según Categoría Activa + Botones (`reports.js` line 118) */}
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px', flexWrap: 'wrap', gap: '12px' }}>
                        <div className="segmented-control" id="report-tabs" style={{ flexWrap: 'wrap' }}>
                            {currentCategory === 'general' && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('607')}
                                        className={`segment-item ${currentTab === '607' ? 'active' : ''}`}
                                    >
                                        Ventas (607)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('606')}
                                        className={`segment-item ${currentTab === '606' ? 'active' : ''}`}
                                    >
                                        Compras / Gastos (606)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('608')}
                                        className={`segment-item ${currentTab === '608' ? 'active' : ''}`}
                                    >
                                        Anulaciones (608)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('it1')}
                                        className={`segment-item ${currentTab === 'it1' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#16a34a' }} />
                                        Declaración IT-1
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('ir2')}
                                        className={`segment-item ${currentTab === 'ir2' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#7c3aed' }} />
                                        Declaración IR-2
                                    </button>
                                </>
                            )}

                            {currentCategory === 'rst' && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('rs2')}
                                        className={`segment-item ${currentTab === 'rs2' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#8b5cf6' }} />
                                        RST Jurídicas (RS2)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('rs1')}
                                        className={`segment-item ${currentTab === 'rs1' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#6366f1' }} />
                                        RST Físicas (RS1)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('rs3')}
                                        className={`segment-item ${currentTab === 'rs3' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#d97706' }} />
                                        RST Compras (RS3)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('rs4')}
                                        className={`segment-item ${currentTab === 'rs4' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#65a30d' }} />
                                        RST Agropecuario (RS4)
                                    </button>
                                </>
                            )}

                            {currentCategory === 'sectorial' && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('dss')}
                                        className={`segment-item ${currentTab === 'dss' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#ea580c' }} />
                                        Seguros (DSS-07)
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setCurrentTab('daf')}
                                        className={`segment-item ${currentTab === 'daf' ? 'active' : ''}`}
                                        style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}
                                    >
                                        <span style={{ display: 'inline-block', width: '7px', height: '7px', borderRadius: '50%', background: '#0d9488' }} />
                                        Activos Financieros (DAF)
                                    </button>
                                </>
                            )}
                        </div>

                        <div style={{ display: 'flex', gap: '10px', flexWrap: 'wrap' }}>
                            {isFormatTab && (
                                <button
                                    type="button"
                                    className="btn"
                                    id="btn-prevalidate"
                                    onClick={handlePrevalidate}
                                    disabled={isPrevalidating}
                                    style={{ background: '#0284c7', color: '#fff', border: 'none', display: 'flex', alignItems: 'center', gap: '8px' }}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M9 11l3 3L22 4" />
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                    </svg>
                                    {isPrevalidating ? 'Pre-validando...' : 'Pre-validar (DGII)'}
                                </button>
                            )}

                            <button
                                type="button"
                                className="btn"
                                id="btn-export-excel"
                                onClick={handleExportExcel}
                                style={{ background: '#107c41', color: '#fff', border: 'none', display: 'flex', alignItems: 'center', gap: '8px' }}
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="8" y1="13" x2="16" y2="13" />
                                    <line x1="8" y1="17" x2="16" y2="17" />
                                    <line x1="10" y1="9" x2="8" y2="9" />
                                </svg>
                                {getExcelBtnLabel()}
                            </button>

                            {isFormatTab && (
                                <button
                                    type="button"
                                    className="btn btn-secondary"
                                    id="btn-export-txt"
                                    onClick={handleExportTxt}
                                    style={{ display: 'flex', alignItems: 'center', gap: '8px' }}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    Descargar Formato TXT
                                </button>
                            )}
                        </div>
                    </div>

                    {/* 1:1 Main Table Outer (`reports.js` line 161) */}
                    <div className="table-outer">
                        <div className="table-wrapper">
                            {currentTab === '607' && (
                                <table className="data-table">
                                    <thead>
                                        <tr id="report-headers">
                                            <th>Comprobante (eNCF)</th>
                                            <th>Cliente</th>
                                            <th>RNC/Cédula</th>
                                            <th>Tipo Ingreso</th>
                                            <th>Fecha Emisión</th>
                                            <th className="text-right">Monto Facturado</th>
                                            <th className="text-right">ITBIS Facturado</th>
                                            <th className="text-right">Efectivo</th>
                                            <th className="text-right">Transferencia</th>
                                            <th className="text-right">Crédito</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-tbody">
                                        <tr>
                                            <td colSpan={10} style={{ padding: '0 16px', border: 'none' }}>
                                                <div className="dgii-info-banner" style={{ borderRadius: '8px', padding: '16px 20px', margin: '12px 0', display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ flexShrink: 0, marginTop: '2px' }}>
                                                        <circle cx="12" cy="12" r="10" />
                                                        <line x1="12" y1="16" x2="12" y2="12" />
                                                        <line x1="12" y1="8" x2="12.01" y2="8" />
                                                    </svg>
                                                    <div style={{ fontSize: '13px', lineHeight: 1.5 }}>
                                                        <strong>Formato 607 - Ventas de Bienes y Servicios (Norma 07-2018)</strong><br />
                                                        Los emisores electrónicos (e-CF) reportan sus ventas en tiempo real a la DGII. Esta plantilla prellenada en Excel refleja los 23 campos oficiales requeridos por la DGII, incluyendo el desglose de formas de pago.
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        {rows607.length === 0 ? (
                                            <tr>
                                                <td colSpan={10} className="text-center text-muted py-24">
                                                    No hay facturas emitidas registradas en este período
                                                </td>
                                            </tr>
                                        ) : (
                                            rows607.map((r, i) => (
                                                <tr key={r.id || i}>
                                                    <td>
                                                        <strong style={{ fontFamily: "'JetBrains Mono', monospace" }}>{r.ncf || 'S/N'}</strong>
                                                    </td>
                                                    <td>{r.cliente_nombre || r.client_name || 'Cliente General'}</td>
                                                    <td style={{ fontFamily: "'JetBrains Mono', monospace" }}>
                                                        {r.rnc_cliente || <span className="text-muted">—</span>}
                                                    </td>
                                                    <td>
                                                        <span className="badge badge-draft" title="Ingreso por operaciones">
                                                            {r.tipo_ingreso || '01'}
                                                        </span>
                                                    </td>
                                                    <td>{formatDgiiDate(r.fecha_comprobante)}</td>
                                                    <td className="text-right font-semibold">{formatCurrency(r.monto_facturado)}</td>
                                                    <td className="text-right">{formatCurrency(r.itbis_facturado)}</td>
                                                    <td className="text-right">{formatCurrency(r.efectivo)}</td>
                                                    <td className="text-right">{formatCurrency(r.bancos)}</td>
                                                    <td className="text-right" style={{ color: 'var(--color-danger-icon)' }}>
                                                        {formatCurrency(r.credito)}
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            )}

                            {currentTab === '606' && (
                                <table className="data-table">
                                    <thead>
                                        <tr id="report-headers">
                                            <th>Comprobante (eNCF)</th>
                                            <th>Proveedor</th>
                                            <th>RNC/Cédula</th>
                                            <th>Fecha Emisión</th>
                                            <th className="text-right">Total Facturado</th>
                                            <th className="text-right">ITBIS Facturado</th>
                                            <th style={{ minWidth: '220px' }}>Tipo de Gasto (606)</th>
                                            <th style={{ minWidth: '180px' }}>Forma de Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-tbody">
                                        {rows606.length === 0 ? (
                                            <tr>
                                                <td colSpan={8} className="text-center text-muted py-24">
                                                    No hay compras ni gastos registrados en este período
                                                </td>
                                            </tr>
                                        ) : (
                                            rows606.map((r, idx) => (
                                                <tr key={r.id || idx}>
                                                    <td>
                                                        <strong style={{ fontFamily: "'JetBrains Mono', monospace" }}>{r.ncf || 'S/N'}</strong>
                                                    </td>
                                                    <td>{r.proveedor_nombre || r.supplier_name || 'Proveedor'}</td>
                                                    <td style={{ fontFamily: "'JetBrains Mono', monospace" }}>{r.rnc_proveedor || '—'}</td>
                                                    <td>{formatDgiiDate(r.fecha_comprobante)}</td>
                                                    <td className="text-right font-semibold">
                                                        {formatCurrency(r.total_facturado ?? r.total_monto_facturado)}
                                                    </td>
                                                    <td className="text-right">{formatCurrency(r.itbis_facturado)}</td>
                                                    <td>
                                                        <select
                                                            className="form-control table-select"
                                                            style={{ fontSize: '12px', padding: '4px 8px', height: '30px' }}
                                                            value={r.tipo_bien_servicio || r.tipo_bienes || '02'}
                                                            onChange={(e) => update606Field(idx, 'tipo_bien_servicio', e.target.value)}
                                                        >
                                                            {EXPENSE_TYPES_606.map((et) => (
                                                                <option key={et.code} value={et.code}>{et.label}</option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select
                                                            className="form-control table-select"
                                                            style={{ fontSize: '12px', padding: '4px 8px', height: '30px' }}
                                                            value={r.forma_pago || '02'}
                                                            onChange={(e) => update606Field(idx, 'forma_pago', e.target.value)}
                                                        >
                                                            {PAYMENT_METHODS_606.map((pm) => (
                                                                <option key={pm.code} value={pm.code}>{pm.label}</option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            )}

                            {currentTab === '608' && (
                                <table className="data-table">
                                    <thead>
                                        <tr id="report-headers">
                                            <th>Comprobante Fiscal (NCF)</th>
                                            <th>Cliente / Razón Social</th>
                                            <th>Fecha Comprobante</th>
                                            <th>Fecha Anulación</th>
                                            <th style={{ minWidth: '240px' }}>Tipo de Anulación (DGII)</th>
                                            <th className="text-right">Monto Facturado</th>
                                            <th>Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-tbody">
                                        <tr>
                                            <td colSpan={7} style={{ padding: '0 16px', border: 'none' }}>
                                                <div style={{ background: 'linear-gradient(135deg,#fef2f2,#fee2e2)', border: '1px solid #fca5a5', borderRadius: '8px', padding: '16px 20px', margin: '12px 0', display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" strokeWidth="2" style={{ flexShrink: 0, marginTop: '2px' }}>
                                                        <circle cx="12" cy="12" r="10" />
                                                        <line x1="12" y1="8" x2="12" y2="12" />
                                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                                    </svg>
                                                    <div style={{ fontSize: '13px', color: '#991b1b', lineHeight: 1.5 }}>
                                                        <strong>Formato 608 - Comprobantes Fiscales Anulados</strong><br />
                                                        Reporta los comprobantes fiscales y e-NCF anulados durante este período indicando el motivo oficial de anulación (códigos 01 al 09 de la DGII).
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        {rows608.length === 0 ? (
                                            <tr>
                                                <td colSpan={7} className="text-center text-muted py-24">
                                                    No hay comprobantes anulados en este período
                                                </td>
                                            </tr>
                                        ) : (
                                            rows608.map((r, idx) => (
                                                <tr key={r.id || idx}>
                                                    <td>
                                                        <strong style={{ fontFamily: "'JetBrains Mono', monospace", color: '#dc2626' }}>{r.ncf || 'S/N'}</strong>
                                                    </td>
                                                    <td>{r.cliente_nombre || 'Cliente General'}</td>
                                                    <td>{formatDgiiDate(r.fecha_comprobante)}</td>
                                                    <td>{formatDgiiDate(r.fecha_anulacion || r.fecha_comprobante)}</td>
                                                    <td>
                                                        <select
                                                            className="form-control table-select"
                                                            style={{ fontSize: '12px', padding: '4px 8px', height: '30px' }}
                                                            value={r.tipo_anulacion || '05'}
                                                            onChange={(e) => update608Field(idx, 'tipo_anulacion', e.target.value)}
                                                        >
                                                            {ANULATION_TYPES_608.map((at) => (
                                                                <option key={at.code} value={at.code}>{at.label}</option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                    <td className="text-right" style={{ textDecoration: 'line-through', color: 'var(--color-text-muted)' }}>
                                                        {formatCurrency(r.monto || 0)}
                                                    </td>
                                                    <td>
                                                        <span className="badge badge-overdue">Anulada</span>
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            )}

                            {currentTab === 'it1' && (
                                <table className="data-table">
                                    <thead>
                                        <tr id="report-headers">
                                            <th style={{ width: '130px' }}>Casilla Oficial</th>
                                            <th>Concepto / Descripción del Formulario</th>
                                            <th className="text-right" style={{ width: '180px' }}>Monto Calculado</th>
                                            <th style={{ width: '280px' }}>Fórmula / Base Imponible</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-tbody">
                                        <tr>
                                            <td colSpan={4} style={{ padding: '0 16px', border: 'none' }}>
                                                <div style={{ background: 'linear-gradient(135deg,#ecfdf5,#d1fae5)', border: '1px solid #6ee7b7', borderRadius: '8px', padding: '16px 20px', margin: '12px 0', display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" strokeWidth="2" style={{ flexShrink: 0, marginTop: '2px' }}>
                                                        <path d="M9 11l3 3L22 4" />
                                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                                    </svg>
                                                    <div style={{ fontSize: '13px', color: '#065f46', lineHeight: 1.5 }}>
                                                        <strong>Declaración Jurada y Pago de ITBIS (Formulario IT-1 y Anexo A Oficial)</strong><br />
                                                        Consolidación automática a partir de los reportes 607 (Ventas) y 606 (Compras) del período. Al descargar el Excel oficial, las hojas <strong>Anexo A</strong> e <strong>IT-1</strong> se entregan prellenadas con las fórmulas originales de la DGII intactas.
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style={{ background: 'var(--bg-hover)' }}>
                                            <td><strong style={{ color: 'var(--color-primary)' }}>II.</strong></td>
                                            <td><strong>INGRESOS POR OPERACIONES (VIENE DE ANEXO A)</strong></td>
                                            <td className="text-right" />
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Sección II del Formulario IT-1</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 1</span></td>
                                            <td><strong>TOTAL DE OPERACIONES DEL PERÍODO</strong></td>
                                            <td className="text-right font-semibold">{formatCurrency(it1.casilla_1_total_operaciones)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Viene de Casilla 11 del Anexo A</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 4</span></td>
                                            <td>Ingresos por ventas locales de bienes o servicios exentos (Art. 343 y Art. 344 CT)</td>
                                            <td className="text-right">{formatCurrency(it1.casilla_4_exentas)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Operaciones no gravadas con ITBIS</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 9</span></td>
                                            <td>Total Operaciones No Gravadas (Sumar casillas 2 a 8)</td>
                                            <td className="text-right">{formatCurrency(it1.casilla_4_exentas)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Exentas / Exportaciones</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 10</span></td>
                                            <td><strong>TOTAL OPERACIONES GRAVADAS (Restar Casilla 1 - Casilla 9)</strong></td>
                                            <td className="text-right font-semibold">{formatCurrency(it1.casilla_10_ingresos_gravados)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Base imponible sujeta a ITBIS</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 11</span></td>
                                            <td>Operaciones Gravadas al 18%</td>
                                            <td className="text-right font-semibold">{formatCurrency(it1.casilla_11_gravadas_18)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Tasa general vigente (18%)</span></td>
                                        </tr>
                                        <tr style={{ background: 'var(--bg-hover)' }}>
                                            <td><strong style={{ color: 'var(--color-primary)' }}>III.</strong></td>
                                            <td><strong>LIQUIDACIÓN DEL IMPUESTO (ITBIS)</strong></td>
                                            <td className="text-right" />
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Sección III del Formulario IT-1</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 16</span></td>
                                            <td>ITBIS Cobrado en Operaciones al 18% (18% de la Casilla 11)</td>
                                            <td className="text-right font-semibold" style={{ color: 'var(--color-primary)' }}>{formatCurrency(it1.casilla_16_itbis_cobrado)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Casilla 11 × 0.18</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 21</span></td>
                                            <td><strong>TOTAL ITBIS COBRADO (Sumar Casillas 16 a 20)</strong></td>
                                            <td className="text-right font-semibold" style={{ color: 'var(--color-primary)' }}>{formatCurrency(it1.casilla_21_total_itbis_cobrado)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Débito Fiscal del Período</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 22</span></td>
                                            <td>ITBIS Pagado en Compras Locales de Bienes (Formato 606)</td>
                                            <td className="text-right">{formatCurrency(it1.casilla_22_itbis_bienes)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Anexo A Renglón IX (Bienes)</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 23</span></td>
                                            <td>ITBIS Pagado por Servicios Deducibles (Formato 606)</td>
                                            <td className="text-right">{formatCurrency(it1.casilla_23_itbis_servicios)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Anexo A Renglón IX (Servicios)</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 25</span></td>
                                            <td><strong>TOTAL ITBIS DEDUCIBLE (Sumar Casillas 22 + 23 + 24)</strong></td>
                                            <td className="text-right font-semibold" style={{ color: '#059669' }}>{formatCurrency(it1.casilla_25_total_itbis_deducible)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Crédito Fiscal Adelantado (606)</span></td>
                                        </tr>
                                        <tr style={{ background: 'rgba(22, 163, 74, 0.06)' }}>
                                            <td><span className="badge badge-paid">Casilla 26</span></td>
                                            <td><strong>IMPUESTO A PAGAR (Si Casilla 21 &gt; Casilla 25)</strong></td>
                                            <td className="text-right font-semibold" style={{ fontSize: '15px', color: '#16a34a' }}>{formatCurrency(it1.casilla_26_impuesto_a_pagar)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Casilla 21 − Casilla 25</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 27</span></td>
                                            <td>SALDO A FAVOR (Si Casilla 25 &gt; Casilla 21)</td>
                                            <td className="text-right font-semibold">{formatCurrency(it1.casilla_27_saldo_a_favor)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Compensable en próximo período</span></td>
                                        </tr>
                                        <tr style={{ background: 'rgba(22, 163, 74, 0.1)' }}>
                                            <td><span className="badge badge-paid">Casilla 38</span></td>
                                            <td><strong>TOTAL GENERAL A PAGAR A LA DGII</strong></td>
                                            <td className="text-right font-semibold" style={{ fontSize: '16px', color: '#16a34a' }}>{formatCurrency(it1.casilla_38_total_a_pagar)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Monto final de liquidación</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            )}

                            {currentTab === 'ir2' && (
                                <table className="data-table">
                                    <thead>
                                        <tr id="report-headers">
                                            <th style={{ width: '130px' }}>Casilla Oficial</th>
                                            <th>Concepto / Descripción del Formulario</th>
                                            <th className="text-right" style={{ width: '180px' }}>Monto Anual</th>
                                            <th style={{ width: '280px' }}>Fórmula / Base Imponible</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-tbody">
                                        <tr>
                                            <td colSpan={4} style={{ padding: '0 16px', border: 'none' }}>
                                                <div style={{ background: 'linear-gradient(135deg,#f5f3ff,#ede9fe)', border: '1px solid #c4b5fd', borderRadius: '8px', padding: '16px 20px', margin: '12px 0', display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" strokeWidth="2" style={{ flexShrink: 0, marginTop: '2px' }}>
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                                    </svg>
                                                    <div style={{ fontSize: '13px', color: '#5b21b6', lineHeight: 1.5 }}>
                                                        <strong>Declaración Jurada Anual del Impuesto Sobre la Renta de Sociedades (Formulario IR-2 Versión 2026)</strong><br />
                                                        Consolidación automática del ejercicio fiscal completo (<strong>01/01/{selectedYear} al 31/12/{selectedYear}</strong>). Incluye prellenado de <strong>Anexo B-1</strong> (Estado de Resultados), <strong>Anexo A-1</strong> (Balance General), <strong>Anexo J</strong> y <strong>Hoja IR-2</strong> (Tasa 27%).
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style={{ background: 'var(--bg-hover)' }}>
                                            <td><strong style={{ color: '#7c3aed' }}>I.</strong></td>
                                            <td><strong>DETERMINACIÓN DE LA RENTA NETA IMPONIBLE</strong></td>
                                            <td className="text-right" />
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Sección I del Formulario IR-2</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla A</span></td>
                                            <td><strong>INGRESOS BRUTOS DEL EJERCICIO (Viene de Anexo B-1)</strong></td>
                                            <td className="text-right font-semibold">{formatCurrency(ir2.casilla_A_total_ingresos)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Total Ingresos de Operaciones Netos</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 1</span></td>
                                            <td><strong>BENEFICIO (O PÉRDIDA) NETA ANTES DEL IMPUESTO</strong></td>
                                            <td className="text-right font-semibold">{formatCurrency(ir2.casilla_1_beneficio_neto)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Ingresos (B-1) − Costos y Gastos (B-1)</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 7</span></td>
                                            <td><strong>RENTA NETA IMPONIBLE ANTES DE LA PÉRDIDA</strong></td>
                                            <td className="text-right font-semibold">{formatCurrency(ir2.casilla_7_renta_neta)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Resultado Fiscal Ajustado</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 11</span></td>
                                            <td><strong>RENTA NETA IMPONIBLE DEL EJERCICIO</strong></td>
                                            <td className="text-right font-semibold">{formatCurrency(ir2.casilla_11_renta_imponible)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Base Imponible sujeta al 27% de ISR</span></td>
                                        </tr>
                                        <tr style={{ background: 'var(--bg-hover)' }}>
                                            <td><strong style={{ color: '#7c3aed' }}>II.</strong></td>
                                            <td><strong>LIQUIDACIÓN DEL IMPUESTO SOBRE LA RENTA (TASA 27%)</strong></td>
                                            <td className="text-right" />
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Sección II del Formulario IR-2</span></td>
                                        </tr>
                                        <tr style={{ background: 'rgba(124, 58, 237, 0.06)' }}>
                                            <td><span className="badge badge-draft">Casilla 12</span></td>
                                            <td><strong>IMPUESTO LIQUIDADO (27% sobre Casilla 11)</strong></td>
                                            <td className="text-right font-semibold" style={{ color: '#7c3aed' }}>{formatCurrency(ir2.casilla_12_impuesto_liquidado)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Art. 297 Código Tributario (Ley 253-12)</span></td>
                                        </tr>
                                        <tr>
                                            <td><span className="badge badge-draft">Casilla 14</span></td>
                                            <td>Menos: Retenciones por Entidades del Estado (e-CF 45 / B15)</td>
                                            <td className="text-right">{formatCurrency(ir2.casilla_14_retenciones_estado)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Créditos deducibles por retenciones</span></td>
                                        </tr>
                                        <tr style={{ background: 'rgba(124, 58, 237, 0.1)' }}>
                                            <td><span className="badge badge-paid">Casilla 31</span></td>
                                            <td><strong>TOTAL GENERAL A PAGAR A LA DGII (ISR)</strong></td>
                                            <td className="text-right font-semibold" style={{ fontSize: '16px', color: '#7c3aed' }}>{formatCurrency(ir2.casilla_31_total_a_pagar)}</td>
                                            <td><span className="text-muted" style={{ fontSize: '12px' }}>Liquidación definitiva anual</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            )}

                            {['rs1', 'rs2', 'rs3', 'rs4', 'dss', 'daf'].includes(currentTab) && (
                                <table className="data-table">
                                    <thead>
                                        <tr id="report-headers">
                                            <th style={{ width: '130px' }}>Casilla Oficial</th>
                                            <th>Descripción / Concepto Oficial</th>
                                            <th className="text-right" style={{ width: '200px' }}>Monto Declarado (DOP)</th>
                                            <th style={{ width: '280px' }}>Fórmula DGII / Origen</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-tbody">
                                        {currentTab === 'rs2' && (
                                            <>
                                                <tr><td><span className="badge badge-draft">Casilla 1</span></td><td>Ingresos por Ventas de Bienes</td><td className="text-right">{formatCurrency(rs2.casilla_1_ventas)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T19 (Facturación 607 Ventas)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 2</span></td><td>Ingresos por Prestación de Servicios</td><td className="text-right">{formatCurrency(rs2.casilla_2_servicios)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T20 (Facturación 607 Servicios)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 5</span></td><td><strong>TOTAL INGRESOS BRUTOS DEL EJERCICIO</strong></td><td className="text-right font-semibold">{formatCurrency(rs2.casilla_5_total_ingresos)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Fórmula Nativa DGII: =SUM(T19:T24)</span></td></tr>
                                                <tr style={{ background: 'rgba(139, 92, 246, 0.08)' }}><td><span className="badge badge-draft">Casilla 8</span></td><td><strong>Impuesto Liquidado (Tasa Efectiva TET 7%)</strong></td><td className="text-right font-semibold" style={{ color: '#8b5cf6' }}>{formatCurrency(rs2.casilla_8_impuesto_liquidado)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Fórmula Nativa DGII: =L39*T25</span></td></tr>
                                                <tr style={{ background: 'rgba(139, 92, 246, 0.12)' }}><td><span className="badge badge-paid">Casilla 14</span></td><td><strong>TOTAL A PAGAR AL FISCO (DGII)</strong></td><td className="text-right font-semibold" style={{ color: '#8b5cf6' }}>{formatCurrency(rs2.casilla_14_total_a_pagar)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T50</span></td></tr>
                                            </>
                                        )}
                                        {currentTab === 'rs1' && (
                                            <>
                                                <tr><td><span className="badge badge-draft">Casilla 2</span></td><td>Ingresos por Prestación de Servicios</td><td className="text-right">{formatCurrency(rs1.casilla_2_servicios)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T20 (Facturación 607)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 5</span></td><td><strong>TOTAL INGRESOS BRUTOS ANUALES</strong></td><td className="text-right font-semibold">{formatCurrency(rs1.casilla_5_total_ingresos)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Fórmula Nativa DGII: =SUM(T19:T25)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 8</span></td><td><strong>Renta Neta Estimada (Base Gravable 60%)</strong></td><td className="text-right font-semibold">{formatCurrency(rs1.casilla_8_renta_estimada)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>=T26*0.60 (Exención 40% de Gastos)</span></td></tr>
                                                <tr style={{ background: 'rgba(99, 102, 241, 0.1)' }}><td><span className="badge badge-paid">Casilla 16</span></td><td><strong>TOTAL A PAGAR AL FISCO (DGII)</strong></td><td className="text-right font-semibold" style={{ color: '#6366f1' }}>{formatCurrency(rs1.casilla_16_total_a_pagar)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Escala Progresiva Personas Físicas</span></td></tr>
                                            </>
                                        )}
                                        {currentTab === 'rs3' && (
                                            <>
                                                <tr><td><span className="badge badge-draft">Casilla 1</span></td><td>Compras Locales e Importadas Registradas</td><td className="text-right">{formatCurrency(rs3.casilla_1_compras)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T21 (Compras Formato 606)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 5</span></td><td><strong>Ventas Estimadas según Margen Comercial</strong></td><td className="text-right font-semibold">{formatCurrency(rs3.casilla_5_ventas_estimadas)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Fórmula Nativa DGII: =T27*(1+T30)</span></td></tr>
                                                <tr style={{ background: 'rgba(217, 119, 6, 0.1)' }}><td><span className="badge badge-paid">Casilla 16</span></td><td><strong>TOTAL A PAGAR AL FISCO (ISR + ITBIS)</strong></td><td className="text-right font-semibold" style={{ color: '#d97706' }}>{formatCurrency(rs3.casilla_16_total_a_pagar)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T58</span></td></tr>
                                            </>
                                        )}
                                        {currentTab === 'rs4' && (
                                            <>
                                                <tr><td><span className="badge badge-draft">Casilla 1</span></td><td>Ingresos Agropecuarios del Ejercicio</td><td className="text-right">{formatCurrency(rs4.casilla_1_ingresos_agropecuarios)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda T21</span></td></tr>
                                                <tr style={{ background: 'rgba(101, 163, 13, 0.1)' }}><td><span className="badge badge-paid">Casilla 15</span></td><td><strong>TOTAL A PAGAR AL FISCO (DGII)</strong></td><td className="text-right font-semibold" style={{ color: '#65a30d' }}>{formatCurrency(rs4.casilla_15_total_a_pagar)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Tasa Efectiva TET ~6.1%</span></td></tr>
                                            </>
                                        )}
                                        {currentTab === 'dss' && (
                                            <>
                                                <tr><td><span className="badge badge-draft">Casilla 12</span></td><td><strong>TOTAL OPERACIONES DEL PERÍODO</strong></td><td className="text-right font-semibold">{formatCurrency(dss.casilla_12_total_operaciones)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Fórmula Nativa DGII: =SUM(AB21:AE31)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 13</span></td><td>Operaciones Exentas</td><td className="text-right">{formatCurrency(dss.casilla_13_operaciones_exentas)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda AB35</span></td></tr>
                                                <tr style={{ background: 'rgba(234, 88, 12, 0.1)' }}><td><span className="badge badge-paid">Casilla 24</span></td><td><strong>TOTAL A PAGAR AL FISCO (DGII)</strong></td><td className="text-right font-semibold" style={{ color: '#ea580c' }}>{formatCurrency(dss.casilla_24_total_a_pagar)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Ley 146-02 (Tasa 16%)</span></td></tr>
                                            </>
                                        )}
                                        {currentTab === 'daf' && (
                                            <>
                                                <tr><td><span className="badge badge-draft">Casilla 1</span></td><td><strong>Total Activos Financieros Productivos Netos (Norma 09-2011)</strong></td><td className="text-right font-semibold">{formatCurrency(daf.casilla_1_activos_financieros)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Celda AC13 (Caja/Bancos y CxC)</span></td></tr>
                                                <tr><td><span className="badge badge-draft">Casilla 2</span></td><td>Exención Legal (Párrafo 2 Art. 12 Ley 139-2011)</td><td className="text-right">{formatCurrency(daf.casilla_2_exencion)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Exención RD$ 700,000,000.00</span></td></tr>
                                                <tr style={{ background: 'rgba(13, 148, 136, 0.1)' }}><td><span className="badge badge-paid">Casilla 17</span></td><td><strong>TOTAL A PAGAR AL FISCO (DGII)</strong></td><td className="text-right font-semibold" style={{ color: '#0d9488' }}>{formatCurrency(daf.casilla_17_total_a_pagar)}</td><td><span className="text-muted" style={{ fontSize: '12px' }}>Tasa 0.48% sobre excedente</span></td></tr>
                                            </>
                                        )}
                                    </tbody>
                                </table>
                            )}
                        </div>

                        {/* 1:1 Summary Footer Bar (`reports.js` line 170) */}
                        <div
                            id="report-summary-box"
                            style={{
                                padding: '16px 24px',
                                background: 'var(--bg-hover)',
                                borderTop: '1px solid var(--color-border)',
                                fontSize: '13px',
                                color: 'var(--color-text-secondary)',
                                display: 'flex',
                                justifyContent: 'space-between',
                                flexWrap: 'wrap',
                                gap: '12px',
                            }}
                        >
                            {currentTab === '607' && (
                                <>
                                    <span>Total Comprobantes Emitidos: <strong>{rows607.length}</strong></span>
                                    <span>
                                        Facturado Neto:{' '}
                                        <strong style={{ color: 'var(--color-primary)', fontSize: '16px' }}>
                                            {formatCurrency(rows607.reduce((s, r) => s + Number(r.monto_facturado || 0), 0))}
                                        </strong>
                                    </span>
                                </>
                            )}
                            {currentTab === '606' && (
                                <>
                                    <span>Total Comprobantes Recibidos: <strong>{rows606.length}</strong></span>
                                    <span>
                                        Gasto Neto:{' '}
                                        <strong style={{ color: '#059669', fontSize: '16px' }}>
                                            {formatCurrency(rows606.reduce((s, r) => s + Number(r.total_facturado ?? r.total_monto_facturado ?? 0), 0))}
                                        </strong>
                                    </span>
                                </>
                            )}
                            {currentTab === '608' && (
                                <>
                                    <span>Total Comprobantes Anulados: <strong>{rows608.length}</strong></span>
                                    <span>
                                        Suma Total:{' '}
                                        <strong style={{ color: '#dc2626', fontSize: '16px' }}>
                                            {formatCurrency(rows608.reduce((s, r) => s + Number(r.monto || 0), 0))}
                                        </strong>
                                    </span>
                                </>
                            )}
                            {currentTab === 'it1' && (
                                <>
                                    <span>
                                        Período Fiscal: <strong>{dIt1.period_formatted}</strong> &bull; Fecha Límite de Pago: <strong style={{ color: '#dc2626' }}>{dIt1.deadline}</strong> &bull; Contribuyente: <strong>{dIt1.company_name}</strong> (RNC: {dIt1.tax_id})
                                    </span>
                                    <span>
                                        Total Impuesto a Pagar:{' '}
                                        <strong style={{ color: '#16a34a', fontSize: '16px' }}>
                                            {formatCurrency(it1.casilla_38_total_a_pagar)}
                                        </strong>
                                    </span>
                                </>
                            )}
                            {currentTab === 'ir2' && (
                                <>
                                    <span>
                                        Ejercicio Fiscal: <strong>{dIr2.year}</strong> ({dIr2.period_formatted}) &bull; Fecha Límite: <strong style={{ color: '#dc2626' }}>{dIr2.deadline}</strong> &bull; Contribuyente: <strong>{dIr2.company_name}</strong> (RNC: {dIr2.tax_id})
                                    </span>
                                    <span>
                                        Total ISR Anual a Pagar (27%):{' '}
                                        <strong style={{ color: '#7c3aed', fontSize: '16px' }}>
                                            {formatCurrency(ir2.casilla_31_total_a_pagar)}
                                        </strong>
                                    </span>
                                </>
                            )}
                        </div>
                    </div>

                    {/* 1:1 Reference Box (`reports.js` line 174) */}
                    <div id="report-reference-box" style={{ marginTop: '24px' }}>
                        {currentTab === '608' && (
                            <div className="table-outer" style={{ padding: '20px 24px' }}>
                                <h4 style={{ margin: '0 0 12px 0', fontSize: '13px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.04em', color: 'var(--color-text-secondary)' }}>
                                    Catálogo Oficial DGII — Tipos de Anulación (Formato 608)
                                </h4>
                                <div className="grid-3" style={{ gap: '10px', fontSize: '12px', color: 'var(--color-text-secondary)' }}>
                                    {ANULATION_TYPES_608.map((at) => (
                                        <div key={at.code}>
                                            <strong style={{ color: 'var(--color-primary)' }}>{at.code}:</strong> {at.label.replace(/^[0-9]{2}\s*-\s*/, '')}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {currentTab === 'it1' && (
                            <div className="grid-2" style={{ gap: '20px' }}>
                                <div className="table-outer" style={{ padding: '20px 24px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                                        <h4 style={{ margin: 0, fontSize: '14px', fontWeight: 700 }}>Anexo A: Comprobantes Emitidos (Renglón II)</h4>
                                        <span className="badge badge-draft">Formato 607</span>
                                    </div>
                                    <table className="data-table">
                                        <tbody>
                                            <tr><td>1. Crédito Fiscal (B01 / E31):</td><td className="text-right"><strong>{(axA.ncf_counts && axA.ncf_counts['01_31']) || 0} doc(s)</strong></td><td className="text-right font-semibold">{formatCurrency(axA.ncf_amounts && axA.ncf_amounts['01_31'])}</td></tr>
                                            <tr><td>2. Consumo Final (B02 / E32):</td><td className="text-right"><strong>{(axA.ncf_counts && axA.ncf_counts['02_32']) || 0} doc(s)</strong></td><td className="text-right font-semibold">{formatCurrency(axA.ncf_amounts && axA.ncf_amounts['02_32'])}</td></tr>
                                            <tr><td>4. Notas de Crédito (B04 / E34):</td><td className="text-right"><strong style={{ color: '#dc2626' }}>{(axA.ncf_counts && axA.ncf_counts['04_34']) || 0} doc(s)</strong></td><td className="text-right font-semibold" style={{ color: '#dc2626' }}>-{formatCurrency(axA.ncf_amounts && axA.ncf_amounts['04_34'])}</td></tr>
                                            <tr><td>6. Regímenes Especiales (B14 / E44):</td><td className="text-right"><strong>{(axA.ncf_counts && axA.ncf_counts['14_44']) || 0} doc(s)</strong></td><td className="text-right font-semibold">{formatCurrency(axA.ncf_amounts && axA.ncf_amounts['14_44'])}</td></tr>
                                            <tr><td>7. Gubernamentales (B15 / E45):</td><td className="text-right"><strong>{(axA.ncf_counts && axA.ncf_counts['15_45']) || 0} doc(s)</strong></td><td className="text-right font-semibold">{formatCurrency(axA.ncf_amounts && axA.ncf_amounts['15_45'])}</td></tr>
                                            <tr style={{ background: 'var(--bg-hover)' }}><td colSpan={2}><strong>11. Total Operaciones Anexo A:</strong></td><td className="text-right font-semibold" style={{ color: 'var(--color-primary)' }}>{formatCurrency(axA.total_operaciones)}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div className="table-outer" style={{ padding: '20px 24px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                                        <h4 style={{ margin: 0, fontSize: '14px', fontWeight: 700 }}>Anexo A: Formas de Pago Declaradas (Renglón III)</h4>
                                        <span className="badge badge-draft">Monto Bruto c/ITBIS</span>
                                    </div>
                                    <table className="data-table">
                                        <tbody>
                                            <tr><td>12. Efectivo:</td><td className="text-right font-semibold">{formatCurrency(axA.formas_pago?.efectivo)}</td></tr>
                                            <tr><td>13. Cheques / Transferencias / Depósitos:</td><td className="text-right font-semibold">{formatCurrency(axA.formas_pago?.cheque_transferencia)}</td></tr>
                                            <tr><td>14. Tarjeta de Débito / Crédito:</td><td className="text-right font-semibold">{formatCurrency(axA.formas_pago?.tarjeta)}</td></tr>
                                            <tr><td>15. A Crédito:</td><td className="text-right font-semibold" style={{ color: '#d97706' }}>{formatCurrency(axA.formas_pago?.credito)}</td></tr>
                                            <tr style={{ background: 'var(--bg-hover)' }}><td><strong>56. Total ITBIS Pagado Deducible (606):</strong></td><td className="text-right font-semibold" style={{ color: '#059669' }}>{formatCurrency(axA.itbis_pagado?.total_deducible)}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}

                        {currentTab === 'ir2' && (
                            <div className="grid-2" style={{ gap: '20px' }}>
                                <div className="table-outer" style={{ padding: '20px 24px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                                        <h4 style={{ margin: 0, fontSize: '14px', fontWeight: 700 }}>Anexo B-1: Estado de Resultados Oficial</h4>
                                        <span className="badge badge-draft">Ejercicio {dIr2.year}</span>
                                    </div>
                                    <table className="data-table">
                                        <tbody>
                                            <tr><td>1. Ingresos de Operaciones Netos:</td><td className="text-right font-semibold" style={{ color: '#059669' }}>{formatCurrency(b1.total_ingresos_netos)}</td></tr>
                                            <tr><td>5. Menos: Costo de Venta (Compras 09):</td><td className="text-right">{formatCurrency(b1.costo_venta)}</td></tr>
                                            <tr><td>6. Gastos de Personal (Sueldos y TSS):</td><td className="text-right">{formatCurrency(b1.gastos_personal)}</td></tr>
                                            <tr><td>7. Gastos por Trabajos, Suministros y Servicios:</td><td className="text-right">{formatCurrency(b1.gastos_servicios)}</td></tr>
                                            <tr><td>8. Arrendamientos:</td><td className="text-right">{formatCurrency(b1.arrendamientos)}</td></tr>
                                            <tr style={{ background: 'var(--bg-hover)' }}><td><strong>13. Total Costos y Gastos del Ejercicio:</strong></td><td className="text-right font-semibold" style={{ color: '#dc2626' }}>-{formatCurrency(b1.total_costos_gastos)}</td></tr>
                                            <tr style={{ background: 'rgba(124, 58, 237, 0.08)' }}><td><strong>14. Beneficio (o Pérdida) del Ejercicio:</strong></td><td className="text-right font-semibold" style={{ color: '#7c3aed' }}>{formatCurrency(b1.beneficio_neto)}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div className="table-outer" style={{ padding: '20px 24px' }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                                        <h4 style={{ margin: 0, fontSize: '14px', fontWeight: 700 }}>Anexo A-1: Balance General Oficial</h4>
                                        <span className="badge badge-paid">✓ Cuadrado: Activo = Pasivo + Capital</span>
                                    </div>
                                    <table className="data-table">
                                        <tbody>
                                            <tr><td>1.1 Cajas y Bancos (Efectivo):</td><td className="text-right font-semibold">{formatCurrency(a1.caja_bancos)}</td></tr>
                                            <tr><td>1.2 Cuentas por Cobrar Clientes:</td><td className="text-right font-semibold">{formatCurrency(a1.cuentas_por_cobrar)}</td></tr>
                                            <tr style={{ background: 'var(--bg-hover)' }}><td><strong>6. TOTAL ACTIVOS:</strong></td><td className="text-right font-semibold" style={{ color: 'var(--color-primary)' }}>{formatCurrency(a1.total_activos)}</td></tr>
                                            <tr><td>Total Pasivos Corrientes:</td><td className="text-right font-semibold" style={{ color: '#d97706' }}>{formatCurrency(a1.total_pasivos)}</td></tr>
                                            <tr><td>10.1 Capital Suscrito y Pagado:</td><td className="text-right font-semibold">{formatCurrency(a1.capital_social)}</td></tr>
                                            <tr style={{ background: 'var(--bg-hover)' }}><td><strong>11. TOTAL PASIVOS Y PATRIMONIO:</strong></td><td className="text-right font-semibold" style={{ color: '#059669' }}>{formatCurrency(a1.total_pasivos_patrimonio)}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* 1:1 Pre-validation Modal (`reports.js` prevalidate modal) */}
                    {prevalModal && (
                        <div className="modal-overlay" onClick={() => setPrevalModal(null)}>
                            <div className="modal" style={{ maxWidth: '600px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3 style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                        <span style={{ background: '#0284c7', color: '#fff', padding: '3px 8px', borderRadius: '4px', fontSize: '12px' }}>
                                            DGII {prevalModal.format || currentTab}
                                        </span>
                                        Resultado de Pre-validación Fiscal
                                    </h3>
                                    <button type="button" className="modal-close" onClick={() => setPrevalModal(null)}>✕</button>
                                </div>
                                <div className="modal-body">
                                    {prevalModal.valid || prevalModal.isValid ? (
                                        <div style={{ background: 'rgba(16, 185, 129, 0.1)', border: '1px solid #10b981', borderRadius: '8px', padding: '16px', marginBottom: '16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                                            <div style={{ width: '32px', height: '32px', borderRadius: '50%', background: '#10b981', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontWeight: 'bold' }}>✓</div>
                                            <div>
                                                <h4 style={{ margin: '0 0 4px 0', color: '#065f46', fontSize: '15px' }}>¡Documento 100% Válido para DGII!</h4>
                                                <p style={{ margin: 0, color: '#047857', fontSize: '13px' }}>Cumple con el 100% de las normas, algoritmos de dígito verificador y estructuras de comprobantes de la DGII.</p>
                                            </div>
                                        </div>
                                    ) : (
                                        <div style={{ background: 'rgba(239, 68, 68, 0.1)', border: '1px solid #ef4444', borderRadius: '8px', padding: '16px', marginBottom: '16px', display: 'flex', alignItems: 'center', gap: '12px' }}>
                                            <div style={{ width: '32px', height: '32px', borderRadius: '50%', background: '#ef4444', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, fontWeight: 'bold' }}>✕</div>
                                            <div>
                                                <h4 style={{ margin: '0 0 4px 0', color: '#991b1b', fontSize: '15px' }}>Se encontraron {(prevalModal.errors || []).length} errores en el reporte</h4>
                                                <p style={{ margin: 0, color: '#b91c1c', fontSize: '13px' }}>La DGII rechazará este archivo. Revise el detalle a continuación para corregirlos.</p>
                                            </div>
                                        </div>
                                    )}

                                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '12px', marginBottom: '16px', padding: '12px', background: 'var(--bg-hover)', borderRadius: '6px', fontSize: '13px' }}>
                                        <div><span className="text-muted">RNC Emisor:</span><br /><strong>{prevalModal.rnc || billsCore?.emitterRnc}</strong></div>
                                        <div><span className="text-muted">Período Fiscal:</span><br /><strong>{prevalModal.period || `${selectedYear}${selectedMonth}`}</strong></div>
                                        <div><span className="text-muted">Comprobantes:</span><br /><strong>{prevalModal.record_count ?? prevalModal.totalLines ?? 0} líneas</strong></div>
                                    </div>

                                    {(prevalModal.errors || []).length > 0 && (
                                        <div style={{ marginTop: '12px' }}>
                                            <h5 style={{ margin: '0 0 8px 0', color: '#dc2626', fontSize: '13px' }}>Errores que bloquean el envío ({prevalModal.errors.length}):</h5>
                                            <ul style={{ margin: 0, paddingLeft: '20px', fontSize: '12.5px', color: '#dc2626', maxHeight: '180px', overflowY: 'auto' }}>
                                                {prevalModal.errors.map((err, i) => <li key={i} style={{ marginBottom: '4px' }}>{err}</li>)}
                                            </ul>
                                        </div>
                                    )}

                                    {(prevalModal.warnings || []).length > 0 && (
                                        <div style={{ marginTop: '12px' }}>
                                            <h5 style={{ margin: '0 0 8px 0', color: '#d97706', fontSize: '13px' }}>Advertencias informativas ({prevalModal.warnings.length}):</h5>
                                            <ul style={{ margin: 0, paddingLeft: '20px', fontSize: '12.5px', color: '#d97706', maxHeight: '140px', overflowY: 'auto' }}>
                                                {prevalModal.warnings.map((w, i) => <li key={i} style={{ marginBottom: '4px' }}>{w}</li>)}
                                            </ul>
                                        </div>
                                    )}
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary" onClick={() => setPrevalModal(null)}>Cerrar</button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
