import React, { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import PoweredByBills from '@/components/bills/PoweredByBills';
import {
    Search,
    CheckCircle2,
    FileSpreadsheet,
    Download,
    X,
} from 'lucide-react';

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
    { code: '01', label: '01 - Gastos de Personal' },
    { code: '02', label: '02 - Gastos por Trabajos, Suministros y Servicios' },
    { code: '03', label: '03 - Arrendamientos' },
    { code: '04', label: '04 - Gastos de Activos Fijos' },
    { code: '05', label: '05 - Gastos de Representación' },
    { code: '06', label: '06 - Otras Deducciones Admitidas' },
    { code: '07', label: '07 - Gastos Financieros' },
    { code: '08', label: '08 - Gastos Extraordinarios' },
    { code: '09', label: '09 - Compras y Gastos que forman parte del Costo de Venta' },
    { code: '10', label: '10 - Adquisiciones de Activos' },
    { code: '11', label: '11 - Gastos de Seguros' },
];

const PAYMENT_METHODS_606 = [
    { code: '01', label: '01 - Efectivo' },
    { code: '02', label: '02 - Cheques/Transferencias/Depósito' },
    { code: '03', label: '03 - Tarjeta Crédito/Débito' },
    { code: '04', label: '04 - Compra a Crédito' },
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
        return new Intl.NumberFormat('es-DO', {
            style: 'currency',
            currency: 'DOP',
            minimumFractionDigits: 2,
        }).format(Number(val || 0));
    };

    const formatDgiiDate = (dateStr) => {
        if (!dateStr || String(dateStr).length !== 8) return dateStr || '—';
        const s = String(dateStr);
        return `${s.slice(6, 8)}/${s.slice(4, 6)}/${s.slice(0, 4)}`;
    };

    const handleLoadPeriod = () => {
        const ym = `${selectedYear}-${selectedMonth}`;
        router.get(
            route('admin.finance.dgii-reports'),
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
                route('admin.finance.dgii-reports.prevalidate', { type: currentTab }),
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
        window.location.href = `${route('admin.finance.dgii-reports.export-excel', { type: currentTab })}?yearMonth=${ym}`;
    };

    const handleExportTxt = () => {
        const ym = `${selectedYear}-${selectedMonth}`;
        if (currentTab === '607') {
            window.location.href = `${route('admin.finance.dgii-reports.download-607')}?yearMonth=${ym}`;
        } else if (currentTab === '606') {
            window.location.href = `${route('admin.finance.dgii-reports.download-606')}?yearMonth=${ym}`;
        } else if (currentTab === '608') {
            window.location.href = `${route('admin.finance.dgii-reports.download-608')}?yearMonth=${ym}`;
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
    const jV = (dIr2.anexo_j && dIr2.anexo_j.ventas) || { counts: {}, amounts: {} };
    const jG = (dIr2.anexo_j && dIr2.anexo_j.gastos) || { counts: {}, amounts: {} };
    const act = dIr2.activo || {};

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

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5">
                {/* Page Header (Identical to Gridbase Bills) */}
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Reportes Fiscales DGII
                    </h1>
                    <PoweredByBills variant="badge" />
                </div>

                {/* Period Selector Bar */}
                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-2xs flex flex-wrap items-end gap-4">
                    <div className="min-w-[140px]">
                        <label className="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            Año Fiscal
                        </label>
                        <select
                            value={selectedYear}
                            onChange={(e) => setSelectedYear(e.target.value)}
                            className="w-full h-10 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            {['2026', '2025', '2024'].map((y) => (
                                <option key={y} value={y}>
                                    {y}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="min-w-[180px]">
                        <label className="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
                            Mes del Período
                        </label>
                        <select
                            value={selectedMonth}
                            onChange={(e) => setSelectedMonth(e.target.value)}
                            className="w-full h-10 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium text-slate-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            {MONTHS.map((m) => (
                                <option key={m.val} value={m.val}>
                                    {m.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <button
                        type="button"
                        onClick={handleLoadPeriod}
                        className="h-10 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold inline-flex items-center gap-2 transition shadow-2xs cursor-pointer"
                    >
                        <Search className="w-4 h-4" />
                        Cargar Período
                    </button>
                </div>

                {/* Régimen / Categoría Selector Bar */}
                <div className="flex flex-wrap items-center gap-3">
                    <span className="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Régimen / Categoría:
                    </span>
                    <div className="inline-flex flex-wrap bg-slate-100 dark:bg-slate-800/90 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/70 gap-1">
                        <button
                            type="button"
                            onClick={() => handleCategoryChange('general')}
                            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-2 transition cursor-pointer ${
                                currentCategory === 'general'
                                    ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                            }`}
                        >
                            <span className="w-2 h-2 rounded-full bg-[#16a34a]" />
                            Régimen General / Obligatorios (5)
                        </button>
                        <button
                            type="button"
                            onClick={() => handleCategoryChange('rst')}
                            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-2 transition cursor-pointer ${
                                currentCategory === 'rst'
                                    ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                            }`}
                        >
                            <span className="w-2 h-2 rounded-full bg-[#8b5cf6]" />
                            Régimen Simplificado - RST (4)
                        </button>
                        <button
                            type="button"
                            onClick={() => handleCategoryChange('sectorial')}
                            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-2 transition cursor-pointer ${
                                currentCategory === 'sectorial'
                                    ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                            }`}
                        >
                            <span className="w-2 h-2 rounded-full bg-[#0d9488]" />
                            Sectoriales Especiales (2)
                        </button>
                    </div>
                </div>

                {/* Sub-tabs & Action Buttons Row */}
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="inline-flex flex-wrap bg-slate-100 dark:bg-slate-800/90 p-1 rounded-xl border border-slate-200/80 dark:border-slate-700/70 gap-1">
                        {currentCategory === 'general' && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('607')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                                        currentTab === '607'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                                    }`}
                                >
                                    Ventas (607)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('606')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                                        currentTab === '606'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                                    }`}
                                >
                                    Compras / Gastos (606)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('608')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                                        currentTab === '608'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                                    }`}
                                >
                                    Anulaciones (608)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('it1')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'it1'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#16a34a]" />
                                    Declaración IT-1
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('ir2')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'ir2'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#7c3aed]" />
                                    Declaración IR-2
                                </button>
                            </>
                        )}

                        {currentCategory === 'rst' && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('rs2')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'rs2'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#8b5cf6]" />
                                    RST Jurídicas (RS2)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('rs1')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'rs1'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#6366f1]" />
                                    RST Físicas (RS1)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('rs3')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'rs3'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#d97706]" />
                                    RST Compras (RS3)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('rs4')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'rs4'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#65a30d]" />
                                    RST Agropecuario (RS4)
                                </button>
                            </>
                        )}

                        {currentCategory === 'sectorial' && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('dss')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'dss'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#ea580c]" />
                                    Seguros (DSS-07)
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setCurrentTab('daf')}
                                    className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer ${
                                        currentTab === 'daf'
                                            ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs'
                                            : 'text-slate-600 dark:text-slate-400'
                                    }`}
                                >
                                    <span className="w-2 h-2 rounded-full bg-[#0d9488]" />
                                    Activos Financieros (DAF)
                                </button>
                            </>
                        )}
                    </div>

                    {/* Action Buttons */}
                    <div className="flex flex-wrap items-center gap-2.5">
                        {isFormatTab && (
                            <button
                                type="button"
                                onClick={handlePrevalidate}
                                disabled={isPrevalidating}
                                className="px-3.5 py-2 rounded-lg bg-[#0284c7] hover:bg-[#0369a1] text-white text-xs font-semibold inline-flex items-center gap-2 transition shadow-2xs cursor-pointer"
                            >
                                <CheckCircle2 className="w-4 h-4" />
                                {isPrevalidating ? 'Pre-validando...' : 'Pre-validar (DGII)'}
                            </button>
                        )}

                        <button
                            type="button"
                            onClick={handleExportExcel}
                            className="px-3.5 py-2 rounded-lg bg-[#107c41] hover:bg-[#0c5e31] text-white text-xs font-semibold inline-flex items-center gap-2 transition shadow-2xs cursor-pointer"
                        >
                            <FileSpreadsheet className="w-4 h-4" />
                            {getExcelBtnLabel()}
                        </button>

                        {isFormatTab && (
                            <button
                                type="button"
                                onClick={handleExportTxt}
                                className="px-3.5 py-2 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 text-xs font-semibold inline-flex items-center gap-2 transition shadow-2xs cursor-pointer"
                            >
                                <Download className="w-4 h-4" />
                                Descargar Formato TXT
                            </button>
                        )}
                    </div>
                </div>

                {/* Contextual Info Banner */}
                {currentTab === '607' && (
                    <div className="rounded-xl border border-sky-200 dark:border-sky-900/60 bg-sky-50/80 dark:bg-sky-950/30 p-3.5 flex items-start gap-3 text-xs text-sky-900 dark:text-sky-200">
                        <span className="font-bold text-base leading-none">ℹ</span>
                        <div>
                            <strong>Formato 607 - Ventas de Bienes y Servicios (Norma 07-2018):</strong>{' '}
                            Reporta todas las facturas válidas emitidas (B01, B02, B04, B14, B15 y e-CF). Los montos se expresan en Pesos Dominicanos (DOP) antes de impuestos y se desglosa la forma de cobro (Efectivo, Transferencia, Tarjeta o Crédito).
                        </div>
                    </div>
                )}

                {currentTab === '606' && (
                    <div className="rounded-xl border border-sky-200 dark:border-sky-900/60 bg-sky-50/80 dark:bg-sky-950/30 p-3.5 flex items-start gap-3 text-xs text-sky-900 dark:text-sky-200">
                        <span className="font-bold text-base leading-none">ℹ</span>
                        <div>
                            <strong>Formato 606 - Compras de Bienes y Servicios (Norma 07-2018):</strong>{' '}
                            Reporta los gastos y compras con valor fiscal sustentados en NCF/e-CF. Puede ajustar directamente en la tabla el Tipo de Costo/Gasto (01 al 11) y la Forma de Pago antes de pre-validar o exportar.
                        </div>
                    </div>
                )}

                {currentTab === '608' && (
                    <div className="rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/80 dark:bg-rose-950/30 p-3.5 flex items-start gap-3 text-xs text-rose-900 dark:text-rose-200">
                        <span className="font-bold text-base leading-none">ℹ</span>
                        <div>
                            <strong>Formato 608 - Comprobantes Fiscales Anulados:</strong>{' '}
                            Reporta mensualmente los números de comprobantes fiscales (NCF / e-CF) que fueron anulados por deterioro, errores de impresión, duplicidad o corrección de información.
                        </div>
                    </div>
                )}

                {currentTab === 'it1' && (
                    <div className="rounded-xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/80 dark:bg-emerald-950/30 p-3.5 flex items-start gap-3 text-xs text-emerald-900 dark:text-emerald-200">
                        <span className="font-bold text-base leading-none">✓</span>
                        <div>
                            <strong>Declaración Jurada y Pago de ITBIS (Formulario IT-1 y Anexo A Oficial):</strong>{' '}
                            Consolidación automática a partir de los reportes 607 (Ventas) y 606 (Compras) del período. Al descargar el Excel oficial, las hojas <strong>Anexo A</strong> e <strong>IT-1</strong> se entregan prellenadas con las fórmulas originales de la DGII intactas.
                        </div>
                    </div>
                )}

                {currentTab === 'ir2' && (
                    <div className="rounded-xl border border-violet-200 dark:border-violet-900/60 bg-violet-50/80 dark:bg-violet-950/30 p-3.5 flex items-start gap-3 text-xs text-violet-900 dark:text-violet-200">
                        <span className="font-bold text-base leading-none">★</span>
                        <div>
                            <strong>Declaración Jurada Anual del Impuesto Sobre la Renta de Sociedades (Formulario IR-2 Versión 2026):</strong>{' '}
                            Consolidación automática del ejercicio fiscal completo (<strong>01/01/{selectedYear} al 31/12/{selectedYear}</strong>). Incluye prellenado de <strong>Anexo B-1</strong> (Estado de Resultados), <strong>Anexo A-1</strong> (Balance General), <strong>Anexo J</strong> y <strong>Hoja IR-2</strong> (Tasa 27%).
                        </div>
                    </div>
                )}

                {/* Main Data Table Card */}
                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xs overflow-hidden">
                    <div className="overflow-x-auto">
                        {/* TAB 607 */}
                        {currentTab === '607' && (
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th className="py-3 px-4">Comprobante (eNCF)</th>
                                        <th className="py-3 px-4">Cliente</th>
                                        <th className="py-3 px-4">RNC/Cédula</th>
                                        <th className="py-3 px-4">Tipo Ingreso</th>
                                        <th className="py-3 px-4">Fecha Emisión</th>
                                        <th className="py-3 px-4 text-right">Monto Facturado</th>
                                        <th className="py-3 px-4 text-right">ITBIS Facturado</th>
                                        <th className="py-3 px-4 text-right">Efectivo</th>
                                        <th className="py-3 px-4 text-right">Transferencia</th>
                                        <th className="py-3 px-4 text-right">Crédito</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    {rows607.length === 0 ? (
                                        <tr>
                                            <td colSpan={10} className="py-12 text-center text-slate-400">
                                                No se encontraron comprobantes emitidos para este período fiscal.
                                            </td>
                                        </tr>
                                    ) : (
                                        rows607.map((r, i) => (
                                            <tr key={r.id || i} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                                <td className="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                    {r.ncf || 'S/N'}
                                                </td>
                                                <td className="py-3 px-4 font-medium text-slate-900 dark:text-white">
                                                    {r.cliente_nombre || r.client_name || 'Cliente General'}
                                                </td>
                                                <td className="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                                    {r.rnc_cliente || '—'}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span className="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[11px] font-medium">
                                                        01 - Operaciones
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4 text-slate-600 dark:text-slate-300">
                                                    {formatDgiiDate(r.fecha_comprobante)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                                    {formatCurrency(r.monto_facturado)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-300">
                                                    {formatCurrency(r.itbis_facturado)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono text-slate-500">
                                                    {formatCurrency(r.efectivo)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono text-slate-500">
                                                    {formatCurrency(r.bancos)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono text-amber-600">
                                                    {formatCurrency(r.credito)}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        )}

                        {/* TAB 606 */}
                        {currentTab === '606' && (
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th className="py-3 px-4">Comprobante (eNCF)</th>
                                        <th className="py-3 px-4">Proveedor</th>
                                        <th className="py-3 px-4">RNC/Cédula</th>
                                        <th className="py-3 px-4">Fecha Emisión</th>
                                        <th className="py-3 px-4 text-right">Total Facturado</th>
                                        <th className="py-3 px-4 text-right">ITBIS Facturado</th>
                                        <th className="py-3 px-4">Tipo de Gasto (606)</th>
                                        <th className="py-3 px-4">Forma de Pago</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    {rows606.length === 0 ? (
                                        <tr>
                                            <td colSpan={8} className="py-12 text-center text-slate-400">
                                                No se encontraron registros de compras o gastos para este período fiscal.
                                            </td>
                                        </tr>
                                    ) : (
                                        rows606.map((r, idx) => (
                                            <tr key={r.id || idx} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                                <td className="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                                    {r.ncf || 'S/N'}
                                                </td>
                                                <td className="py-3 px-4 font-medium text-slate-900 dark:text-white">
                                                    {r.proveedor_nombre || r.supplier_name || 'Proveedor'}
                                                </td>
                                                <td className="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                                    {r.rnc_proveedor || '—'}
                                                </td>
                                                <td className="py-3 px-4 text-slate-600 dark:text-slate-300">
                                                    {formatDgiiDate(r.fecha_comprobante)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">
                                                    {formatCurrency(r.total_facturado ?? r.total_monto_facturado)}
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-300">
                                                    {formatCurrency(r.itbis_facturado)}
                                                </td>
                                                <td className="py-2 px-4">
                                                    <select
                                                        value={r.tipo_bien_servicio || r.tipo_bienes || '02'}
                                                        onChange={(e) =>
                                                            update606Field(idx, 'tipo_bien_servicio', e.target.value)
                                                        }
                                                        className="w-full max-w-[210px] rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs py-1 px-2"
                                                    >
                                                        {EXPENSE_TYPES_606.map((et) => (
                                                            <option key={et.code} value={et.code}>
                                                                {et.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </td>
                                                <td className="py-2 px-4">
                                                    <select
                                                        value={r.forma_pago || '02'}
                                                        onChange={(e) =>
                                                            update606Field(idx, 'forma_pago', e.target.value)
                                                        }
                                                        className="w-full max-w-[170px] rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs py-1 px-2"
                                                    >
                                                        {PAYMENT_METHODS_606.map((pm) => (
                                                            <option key={pm.code} value={pm.code}>
                                                                {pm.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        )}

                        {/* TAB 608 */}
                        {currentTab === '608' && (
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th className="py-3 px-4">Comprobante Fiscal (NCF)</th>
                                        <th className="py-3 px-4">Cliente / Razón Social</th>
                                        <th className="py-3 px-4">Fecha Comprobante</th>
                                        <th className="py-3 px-4">Fecha Anulación</th>
                                        <th className="py-3 px-4">Tipo de Anulación (DGII)</th>
                                        <th className="py-3 px-4 text-right">Monto Facturado</th>
                                        <th className="py-3 px-4">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    {rows608.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="py-12 text-center text-slate-400">
                                                No se encontraron comprobantes anulados para este período fiscal.
                                            </td>
                                        </tr>
                                    ) : (
                                        rows608.map((r, idx) => (
                                            <tr key={r.id || idx} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                                <td className="py-3 px-4 font-mono font-bold text-rose-600 dark:text-rose-400">
                                                    {r.ncf || 'S/N'}
                                                </td>
                                                <td className="py-3 px-4 font-medium text-slate-900 dark:text-white">
                                                    {r.cliente_nombre || 'General'}
                                                </td>
                                                <td className="py-3 px-4 text-slate-600 dark:text-slate-300">
                                                    {formatDgiiDate(r.fecha_comprobante)}
                                                </td>
                                                <td className="py-3 px-4 text-slate-600 dark:text-slate-300">
                                                    {formatDgiiDate(r.fecha_anulacion || r.fecha_comprobante)}
                                                </td>
                                                <td className="py-2 px-4">
                                                    <select
                                                        value={r.tipo_anulacion || '05'}
                                                        onChange={(e) =>
                                                            update608Field(idx, 'tipo_anulacion', e.target.value)
                                                        }
                                                        className="w-full max-w-[250px] rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs py-1 px-2"
                                                    >
                                                        {ANULATION_TYPES_608.map((at) => (
                                                            <option key={at.code} value={at.code}>
                                                                {at.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </td>
                                                <td className="py-3 px-4 text-right font-mono text-slate-500 line-through">
                                                    {formatCurrency(r.monto || 0)}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span className="px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-[11px] font-bold">
                                                        Anulada
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        )}

                        {/* TAB IT-1 */}
                        {currentTab === 'it1' && (
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th className="py-3 px-4 w-[130px]">Casilla Oficial</th>
                                        <th className="py-3 px-4">Concepto / Descripción del Formulario</th>
                                        <th className="py-3 px-4 text-right w-[180px]">Monto Calculado</th>
                                        <th className="py-3 px-4 w-[280px]">Fórmula / Base Imponible</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    <tr className="bg-slate-100/80 dark:bg-slate-800/80 font-bold">
                                        <td className="py-2.5 px-4 font-mono text-indigo-600 dark:text-indigo-400">II.</td>
                                        <td className="py-2.5 px-4">INGRESOS POR OPERACIONES (VIENE DE ANEXO A)</td>
                                        <td className="py-2.5 px-4 text-right" />
                                        <td className="py-2.5 px-4 text-slate-500">Sección II del Formulario IT-1</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 1</td>
                                        <td className="py-2.5 px-4 font-bold">TOTAL DE OPERACIONES DEL PERÍODO</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(it1.casilla_1_total_operaciones)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Viene de Casilla 11 del Anexo A</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 4</td>
                                        <td className="py-2.5 px-4">Ingresos por ventas locales de bienes o servicios exentos (Art. 343 y Art. 344 CT)</td>
                                        <td className="py-2.5 px-4 text-right font-mono">{formatCurrency(it1.casilla_4_exentas)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Operaciones no gravadas con ITBIS</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 9</td>
                                        <td className="py-2.5 px-4">Total Operaciones No Gravadas (Sumar casillas 2 a 8)</td>
                                        <td className="py-2.5 px-4 text-right font-mono">{formatCurrency(it1.casilla_4_exentas)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Exentas / Exportaciones</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 10</td>
                                        <td className="py-2.5 px-4 font-bold">TOTAL OPERACIONES GRAVADAS (Restar Casilla 1 - Casilla 9)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(it1.casilla_10_ingresos_gravados)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Base imponible sujeta a ITBIS</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 11</td>
                                        <td className="py-2.5 px-4">Operaciones Gravadas al 18%</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(it1.casilla_11_gravadas_18)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Tasa general vigente (18%)</td>
                                    </tr>
                                    <tr className="bg-slate-100/80 dark:bg-slate-800/80 font-bold">
                                        <td className="py-2.5 px-4 font-mono text-indigo-600 dark:text-indigo-400">III.</td>
                                        <td className="py-2.5 px-4">LIQUIDACIÓN DEL IMPUESTO (ITBIS)</td>
                                        <td className="py-2.5 px-4 text-right" />
                                        <td className="py-2.5 px-4 text-slate-500">Sección III del Formulario IT-1</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 16</td>
                                        <td className="py-2.5 px-4">ITBIS Cobrado en Operaciones al 18% (18% de la Casilla 11)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(it1.casilla_16_itbis_cobrado)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Casilla 11 × 0.18</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 21</td>
                                        <td className="py-2.5 px-4 font-bold">TOTAL ITBIS COBRADO (Sumar Casillas 16 a 20)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(it1.casilla_21_total_itbis_cobrado)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Débito Fiscal del Período</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 22</td>
                                        <td className="py-2.5 px-4">ITBIS Pagado en Compras Locales de Bienes (Formato 606)</td>
                                        <td className="py-2.5 px-4 text-right font-mono">{formatCurrency(it1.casilla_22_itbis_bienes)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Anexo A Renglón IX (Bienes)</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 23</td>
                                        <td className="py-2.5 px-4">ITBIS Pagado por Servicios Deducibles (Formato 606)</td>
                                        <td className="py-2.5 px-4 text-right font-mono">{formatCurrency(it1.casilla_23_itbis_servicios)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Anexo A Renglón IX (Servicios)</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 25</td>
                                        <td className="py-2.5 px-4 font-bold">TOTAL ITBIS DEDUCIBLE (Sumar Casillas 22 + 23 + 24)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(it1.casilla_25_total_itbis_deducible)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Crédito Fiscal Adelantado (606)</td>
                                    </tr>
                                    <tr className="bg-emerald-50/70 dark:bg-emerald-950/30">
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 26</td>
                                        <td className="py-2.5 px-4 font-bold">IMPUESTO A PAGAR (Si Casilla 21 &gt; Casilla 25)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold text-emerald-600 text-sm">{formatCurrency(it1.casilla_26_impuesto_a_pagar)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Casilla 21 − Casilla 25</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 27</td>
                                        <td className="py-2.5 px-4">SALDO A FAVOR (Si Casilla 25 &gt; Casilla 21)</td>
                                        <td className="py-2.5 px-4 text-right font-mono">{formatCurrency(it1.casilla_27_saldo_a_favor)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Compensable en próximo período</td>
                                    </tr>
                                    <tr className="bg-emerald-50/70 dark:bg-emerald-950/30">
                                        <td className="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 38</td>
                                        <td className="py-3 px-4 font-bold">TOTAL GENERAL A PAGAR A LA DGII</td>
                                        <td className="py-3 px-4 text-right font-mono font-bold text-emerald-600 text-base">{formatCurrency(it1.casilla_38_total_a_pagar)}</td>
                                        <td className="py-3 px-4 text-slate-500">Monto final de liquidación</td>
                                    </tr>
                                </tbody>
                            </table>
                        )}

                        {/* TAB IR-2 */}
                        {currentTab === 'ir2' && (
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th className="py-3 px-4 w-[130px]">Casilla Oficial</th>
                                        <th className="py-3 px-4">Concepto / Descripción del Formulario</th>
                                        <th className="py-3 px-4 text-right w-[180px]">Monto Anual</th>
                                        <th className="py-3 px-4 w-[280px]">Fórmula / Base Imponible</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    <tr className="bg-slate-100/80 dark:bg-slate-800/80 font-bold">
                                        <td className="py-2.5 px-4 font-mono text-indigo-600 dark:text-indigo-400">I.</td>
                                        <td className="py-2.5 px-4">DETERMINACIÓN DE LA RENTA NETA IMPONIBLE</td>
                                        <td className="py-2.5 px-4 text-right" />
                                        <td className="py-2.5 px-4 text-slate-500">Sección I del Formulario IR-2</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla A</td>
                                        <td className="py-2.5 px-4 font-bold">INGRESOS BRUTOS DEL EJERCICIO (Viene de Anexo B-1)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(ir2.casilla_A_total_ingresos)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Total Ingresos de Operaciones Netos</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 1</td>
                                        <td className="py-2.5 px-4 font-bold">BENEFICIO (O PÉRDIDA) NETA ANTES DEL IMPUESTO</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(ir2.casilla_1_beneficio_neto)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Ingresos (B-1) − Costos y Gastos (B-1)</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 7</td>
                                        <td className="py-2.5 px-4 font-bold">RENTA NETA IMPONIBLE ANTES DE LA PÉRDIDA</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(ir2.casilla_7_renta_neta)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Resultado Fiscal Ajustado</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 11</td>
                                        <td className="py-2.5 px-4 font-bold">RENTA NETA IMPONIBLE DEL EJERCICIO</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(ir2.casilla_11_renta_imponible)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Base Imponible sujeta al 27% de ISR</td>
                                    </tr>
                                    <tr className="bg-slate-100/80 dark:bg-slate-800/80 font-bold">
                                        <td className="py-2.5 px-4 font-mono text-indigo-600 dark:text-indigo-400">II.</td>
                                        <td className="py-2.5 px-4">LIQUIDACIÓN DEL IMPUESTO SOBRE LA RENTA (TASA 27%)</td>
                                        <td className="py-2.5 px-4 text-right" />
                                        <td className="py-2.5 px-4 text-slate-500">Sección II del Formulario IR-2</td>
                                    </tr>
                                    <tr className="bg-violet-50/70 dark:bg-violet-950/30">
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 12</td>
                                        <td className="py-2.5 px-4 font-bold">IMPUESTO LIQUIDADO (27% sobre Casilla 11)</td>
                                        <td className="py-2.5 px-4 text-right font-mono font-bold text-violet-600 text-sm">{formatCurrency(ir2.casilla_12_impuesto_liquidado)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Art. 297 Código Tributario (Ley 253-12)</td>
                                    </tr>
                                    <tr>
                                        <td className="py-2.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 14</td>
                                        <td className="py-2.5 px-4">Menos: Retenciones por Entidades del Estado (e-CF 45 / B15)</td>
                                        <td className="py-2.5 px-4 text-right font-mono">{formatCurrency(ir2.casilla_14_retenciones_estado)}</td>
                                        <td className="py-2.5 px-4 text-slate-500">Créditos deducibles por retenciones</td>
                                    </tr>
                                    <tr className="bg-violet-50/70 dark:bg-violet-950/30">
                                        <td className="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">Casilla 31</td>
                                        <td className="py-3 px-4 font-bold">TOTAL GENERAL A PAGAR A LA DGII (ISR)</td>
                                        <td className="py-3 px-4 text-right font-mono font-bold text-violet-600 text-base">{formatCurrency(ir2.casilla_31_total_a_pagar)}</td>
                                        <td className="py-3 px-4 text-slate-500">Liquidación definitiva anual</td>
                                    </tr>
                                </tbody>
                            </table>
                        )}

                        {/* TAB RS1, RS2, RS3, RS4, DSS, DAF */}
                        {['rs1', 'rs2', 'rs3', 'rs4', 'dss', 'daf'].includes(currentTab) && (
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="bg-slate-50 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        <th className="py-3 px-4 w-[130px]">Casilla Oficial</th>
                                        <th className="py-3 px-4">Descripción / Concepto Oficial</th>
                                        <th className="py-3 px-4 text-right w-[200px]">Monto Declarado (DOP)</th>
                                        <th className="py-3 px-4 w-[280px]">Fórmula DGII / Origen</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    {currentTab === 'rs2' && (
                                        <>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 1</td><td className="py-2.5 px-4">Ingresos por Ventas de Bienes</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(rs2.casilla_1_ventas)}</td><td className="py-2.5 px-4 text-slate-500">Celda T19 (Facturación 607 Ventas)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 2</td><td className="py-2.5 px-4">Ingresos por Prestación de Servicios</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(rs2.casilla_2_servicios)}</td><td className="py-2.5 px-4 text-slate-500">Celda T20 (Facturación 607 Servicios)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 5</td><td className="py-2.5 px-4 font-bold">TOTAL INGRESOS BRUTOS DEL EJERCICIO</td><td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(rs2.casilla_5_total_ingresos)}</td><td className="py-2.5 px-4 text-slate-500">Fórmula Nativa DGII: =SUM(T19:T24)</td></tr>
                                            <tr className="bg-violet-50/70 dark:bg-violet-950/30"><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 8</td><td className="py-2.5 px-4 font-bold">Impuesto Liquidado (Tasa Efectiva TET 7%)</td><td className="py-2.5 px-4 text-right font-mono font-bold text-violet-600">{formatCurrency(rs2.casilla_8_impuesto_liquidado)}</td><td className="py-2.5 px-4 text-slate-500">Fórmula Nativa DGII: =L39*T25</td></tr>
                                            <tr className="bg-violet-50/70 dark:bg-violet-950/30"><td className="py-3 px-4 font-mono font-bold text-indigo-600">Casilla 14</td><td className="py-3 px-4 font-bold">TOTAL A PAGAR AL FISCO (DGII)</td><td className="py-3 px-4 text-right font-mono font-bold text-violet-600 text-sm">{formatCurrency(rs2.casilla_14_total_a_pagar)}</td><td className="py-3 px-4 text-slate-500">Celda T50</td></tr>
                                        </>
                                    )}
                                    {currentTab === 'rs1' && (
                                        <>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 2</td><td className="py-2.5 px-4">Ingresos por Prestación de Servicios</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(rs1.casilla_2_servicios)}</td><td className="py-2.5 px-4 text-slate-500">Celda T20 (Facturación 607)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 5</td><td className="py-2.5 px-4 font-bold">TOTAL INGRESOS BRUTOS ANUALES</td><td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(rs1.casilla_5_total_ingresos)}</td><td className="py-2.5 px-4 text-slate-500">Fórmula Nativa DGII: =SUM(T19:T25)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 8</td><td className="py-2.5 px-4 font-bold">Renta Neta Estimada (Base Gravable 60%)</td><td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(rs1.casilla_8_renta_estimada)}</td><td className="py-2.5 px-4 text-slate-500">=T26*0.60 (Exención 40% de Gastos)</td></tr>
                                            <tr className="bg-indigo-50/70 dark:bg-indigo-950/30"><td className="py-3 px-4 font-mono font-bold text-indigo-600">Casilla 16</td><td className="py-3 px-4 font-bold">TOTAL A PAGAR AL FISCO (DGII)</td><td className="py-3 px-4 text-right font-mono font-bold text-indigo-600 text-sm">{formatCurrency(rs1.casilla_16_total_a_pagar)}</td><td className="py-3 px-4 text-slate-500">Escala Progresiva Personas Físicas</td></tr>
                                        </>
                                    )}
                                    {currentTab === 'rs3' && (
                                        <>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 1</td><td className="py-2.5 px-4">Compras Locales e Importadas Registradas</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(rs3.casilla_1_compras)}</td><td className="py-2.5 px-4 text-slate-500">Celda T21 (Compras Formato 606)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 5</td><td className="py-2.5 px-4 font-bold">Ventas Estimadas según Margen Comercial</td><td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(rs3.casilla_5_ventas_estimadas)}</td><td className="py-2.5 px-4 text-slate-500">Fórmula Nativa DGII: =T27*(1+T30)</td></tr>
                                            <tr className="bg-amber-50/70 dark:bg-amber-950/30"><td className="py-3 px-4 font-mono font-bold text-indigo-600">Casilla 16</td><td className="py-3 px-4 font-bold">TOTAL A PAGAR AL FISCO (ISR + ITBIS)</td><td className="py-3 px-4 text-right font-mono font-bold text-amber-600 text-sm">{formatCurrency(rs3.casilla_16_total_a_pagar)}</td><td className="py-3 px-4 text-slate-500">Celda T58</td></tr>
                                        </>
                                    )}
                                    {currentTab === 'rs4' && (
                                        <>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 1</td><td className="py-2.5 px-4">Ingresos Agropecuarios del Ejercicio</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(rs4.casilla_1_ingresos_agropecuarios)}</td><td className="py-2.5 px-4 text-slate-500">Celda T21</td></tr>
                                            <tr className="bg-lime-50/70 dark:bg-lime-950/30"><td className="py-3 px-4 font-mono font-bold text-indigo-600">Casilla 15</td><td className="py-3 px-4 font-bold">TOTAL A PAGAR AL FISCO (DGII)</td><td className="py-3 px-4 text-right font-mono font-bold text-lime-600 text-sm">{formatCurrency(rs4.casilla_15_total_a_pagar)}</td><td className="py-3 px-4 text-slate-500">Tasa Efectiva TET ~6.1%</td></tr>
                                        </>
                                    )}
                                    {currentTab === 'dss' && (
                                        <>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 12</td><td className="py-2.5 px-4 font-bold">TOTAL OPERACIONES DEL PERÍODO</td><td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(dss.casilla_12_total_operaciones)}</td><td className="py-2.5 px-4 text-slate-500">Fórmula Nativa DGII: =SUM(AB21:AE31)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 13</td><td className="py-2.5 px-4">Operaciones Exentas</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(dss.casilla_13_operaciones_exentas)}</td><td className="py-2.5 px-4 text-slate-500">Celda AB35</td></tr>
                                            <tr className="bg-orange-50/70 dark:bg-orange-950/30"><td className="py-3 px-4 font-mono font-bold text-indigo-600">Casilla 24</td><td className="py-3 px-4 font-bold">TOTAL A PAGAR AL FISCO (DGII)</td><td className="py-3 px-4 text-right font-mono font-bold text-orange-600 text-sm">{formatCurrency(dss.casilla_24_total_a_pagar)}</td><td className="py-3 px-4 text-slate-500">Ley 146-02 (Tasa 16%)</td></tr>
                                        </>
                                    )}
                                    {currentTab === 'daf' && (
                                        <>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 1</td><td className="py-2.5 px-4 font-bold">Total Activos Financieros Productivos Netos (Norma 09-2011)</td><td className="py-2.5 px-4 text-right font-mono font-bold">{formatCurrency(daf.casilla_1_activos_financieros)}</td><td className="py-2.5 px-4 text-slate-500">Celda AC13 (Caja/Bancos y CxC)</td></tr>
                                            <tr><td className="py-2.5 px-4 font-mono font-bold text-indigo-600">Casilla 2</td><td className="py-2.5 px-4">Exención Legal (Párrafo 2 Art. 12 Ley 139-2011)</td><td className="py-2.5 px-4 text-right font-mono">{formatCurrency(daf.casilla_2_exencion)}</td><td className="py-2.5 px-4 text-slate-500">Exención RD$ 700,000,000.00</td></tr>
                                            <tr className="bg-teal-50/70 dark:bg-teal-950/30"><td className="py-3 px-4 font-mono font-bold text-indigo-600">Casilla 17</td><td className="py-3 px-4 font-bold">TOTAL A PAGAR AL FISCO (DGII)</td><td className="py-3 px-4 text-right font-mono font-bold text-teal-600 text-sm">{formatCurrency(daf.casilla_17_total_a_pagar)}</td><td className="py-3 px-4 text-slate-500">Tasa 0.48% sobre excedente</td></tr>
                                        </>
                                    )}
                                </tbody>
                            </table>
                        )}
                    </div>

                    {/* Summary Footer Bar (Identical to Gridbase Bills) */}
                    <div className="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600 dark:text-slate-300">
                        {currentTab === '607' && (
                            <>
                                <div>
                                    Total Comprobantes Emitidos: <strong>{rows607.length}</strong>
                                </div>
                                <div>
                                    Facturado Neto:{' '}
                                    <strong className="text-indigo-600 dark:text-indigo-400 text-sm ml-1">
                                        {formatCurrency(
                                            rows607.reduce((s, r) => s + Number(r.monto_facturado || 0), 0)
                                        )}
                                    </strong>
                                </div>
                            </>
                        )}

                        {currentTab === '606' && (
                            <>
                                <div>
                                    Total Comprobantes Recibidos: <strong>{rows606.length}</strong>
                                </div>
                                <div>
                                    Gasto Neto:{' '}
                                    <strong className="text-indigo-600 dark:text-indigo-400 text-sm ml-1">
                                        {formatCurrency(
                                            rows606.reduce(
                                                (s, r) =>
                                                    s + Number(r.total_facturado ?? r.total_monto_facturado ?? 0),
                                                0
                                            )
                                        )}
                                    </strong>
                                </div>
                            </>
                        )}

                        {currentTab === '608' && (
                            <>
                                <div>
                                    Total Comprobantes Anulados: <strong>{rows608.length}</strong>
                                </div>
                                <div>
                                    Monto Total Anulado:{' '}
                                    <strong className="text-rose-600 dark:text-rose-400 text-sm ml-1">
                                        {formatCurrency(
                                            rows608.reduce((s, r) => s + Number(r.monto || 0), 0)
                                        )}
                                    </strong>
                                </div>
                            </>
                        )}

                        {currentTab === 'it1' && (
                            <>
                                <div>
                                    Período Fiscal: <strong>{dIt1.period_formatted}</strong> &bull; Fecha Límite de Pago:{' '}
                                    <strong className="text-rose-600">{dIt1.deadline}</strong> &bull; Contribuyente:{' '}
                                    <strong>{dIt1.company_name}</strong> (RNC: {dIt1.tax_id})
                                </div>
                                <div>
                                    Total Impuesto a Pagar:{' '}
                                    <strong className="text-emerald-600 text-base ml-1.5">
                                        {formatCurrency(it1.casilla_38_total_a_pagar)}
                                    </strong>
                                </div>
                            </>
                        )}

                        {currentTab === 'ir2' && (
                            <>
                                <div>
                                    Ejercicio Fiscal: <strong>{dIr2.year}</strong> ({dIr2.period_formatted}) &bull; Fecha Límite:{' '}
                                    <strong className="text-rose-600">{dIr2.deadline}</strong> &bull; Contribuyente:{' '}
                                    <strong>{dIr2.company_name}</strong> (RNC: {dIr2.tax_id})
                                </div>
                                <div>
                                    Total ISR Anual a Pagar (27%):{' '}
                                    <strong className="text-violet-600 text-base ml-1.5">
                                        {formatCurrency(ir2.casilla_31_total_a_pagar)}
                                    </strong>
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* Reference Boxes Below Table (Identical to Gridbase Bills) */}
                {currentTab === '608' && (
                    <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-2xs">
                        <h4 className="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-3">
                            Catálogo Oficial DGII — Tipos de Anulación (Formato 608)
                        </h4>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 text-xs text-slate-600 dark:text-slate-400">
                            {ANULATION_TYPES_608.map((at) => (
                                <div key={at.code}>
                                    <strong className="text-indigo-600 dark:text-indigo-400">
                                        {at.code}:
                                    </strong>{' '}
                                    {at.label.replace(/^[0-9]{2}\s*-\s*/, '')}
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {currentTab === 'it1' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-2xs">
                            <div className="flex items-center justify-between mb-3">
                                <h4 className="text-xs font-bold text-slate-800 dark:text-white">
                                    Anexo A: Comprobantes Emitidos (Renglón II)
                                </h4>
                                <span className="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-semibold">
                                    Formato 607
                                </span>
                            </div>
                            <table className="w-full text-xs">
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    <tr>
                                        <td className="py-1.5">1. Crédito Fiscal (B01 / E31):</td>
                                        <td className="py-1.5 text-right font-mono font-bold">
                                            {(axA.ncf_counts && axA.ncf_counts['01_31']) || 0} doc(s)
                                        </td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.ncf_amounts && axA.ncf_amounts['01_31'])}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">2. Consumo Final (B02 / E32):</td>
                                        <td className="py-1.5 text-right font-mono font-bold">
                                            {(axA.ncf_counts && axA.ncf_counts['02_32']) || 0} doc(s)
                                        </td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.ncf_amounts && axA.ncf_amounts['02_32'])}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">4. Notas de Crédito (B04 / E34):</td>
                                        <td className="py-1.5 text-right font-mono font-bold text-rose-600">
                                            {(axA.ncf_counts && axA.ncf_counts['04_34']) || 0} doc(s)
                                        </td>
                                        <td className="py-1.5 text-right font-mono font-semibold text-rose-600">
                                            -{formatCurrency(axA.ncf_amounts && axA.ncf_amounts['04_34'])}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">6. Regímenes Especiales (B14 / E44):</td>
                                        <td className="py-1.5 text-right font-mono font-bold">
                                            {(axA.ncf_counts && axA.ncf_counts['14_44']) || 0} doc(s)
                                        </td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.ncf_amounts && axA.ncf_amounts['14_44'])}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">7. Gubernamentales (B15 / E45):</td>
                                        <td className="py-1.5 text-right font-mono font-bold">
                                            {(axA.ncf_counts && axA.ncf_counts['15_45']) || 0} doc(s)
                                        </td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.ncf_amounts && axA.ncf_amounts['15_45'])}
                                        </td>
                                    </tr>
                                    <tr className="bg-slate-50 dark:bg-slate-800/60 font-bold">
                                        <td className="py-2" colSpan={2}>
                                            11. Total Operaciones Anexo A:
                                        </td>
                                        <td className="py-2 text-right font-mono text-indigo-600">
                                            {formatCurrency(axA.total_operaciones)}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-2xs">
                            <div className="flex items-center justify-between mb-3">
                                <h4 className="text-xs font-bold text-slate-800 dark:text-white">
                                    Anexo A: Formas de Pago Declaradas (Renglón III)
                                </h4>
                                <span className="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-semibold">
                                    Monto Bruto c/ITBIS
                                </span>
                            </div>
                            <table className="w-full text-xs">
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    <tr>
                                        <td className="py-1.5">12. Efectivo:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.formas_pago?.efectivo)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">13. Cheques / Transferencias / Depósitos:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.formas_pago?.cheque_transferencia)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">14. Tarjeta de Débito / Crédito:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(axA.formas_pago?.tarjeta)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">15. A Crédito:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold text-amber-600">
                                            {formatCurrency(axA.formas_pago?.credito)}
                                        </td>
                                    </tr>
                                    <tr className="bg-slate-50 dark:bg-slate-800/60 font-bold">
                                        <td className="py-2">56. Total ITBIS Pagado Deducible (606):</td>
                                        <td className="py-2 text-right font-mono text-emerald-600">
                                            {formatCurrency(axA.itbis_pagado?.total_deducible)}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {currentTab === 'ir2' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-2xs">
                            <div className="flex items-center justify-between mb-3">
                                <h4 className="text-xs font-bold text-slate-800 dark:text-white">
                                    Anexo B-1: Estado de Resultados Oficial
                                </h4>
                                <span className="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-semibold">
                                    Ejercicio {dIr2.year}
                                </span>
                            </div>
                            <table className="w-full text-xs">
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    <tr>
                                        <td className="py-1.5">1. Ingresos de Operaciones Netos:</td>
                                        <td className="py-1.5 text-right font-mono font-bold text-emerald-600">
                                            {formatCurrency(b1.total_ingresos_netos)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">5. Menos: Costo de Venta (Compras 09):</td>
                                        <td className="py-1.5 text-right font-mono">{formatCurrency(b1.costo_venta)}</td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">6. Gastos de Personal (Sueldos y TSS):</td>
                                        <td className="py-1.5 text-right font-mono">{formatCurrency(b1.gastos_personal)}</td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">7. Gastos por Trabajos, Suministros y Servicios:</td>
                                        <td className="py-1.5 text-right font-mono">{formatCurrency(b1.gastos_servicios)}</td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">8. Arrendamientos:</td>
                                        <td className="py-1.5 text-right font-mono">{formatCurrency(b1.arrendamientos)}</td>
                                    </tr>
                                    <tr className="bg-slate-50 dark:bg-slate-800/60 font-bold">
                                        <td className="py-2">13. Total Costos y Gastos del Ejercicio:</td>
                                        <td className="py-2 text-right font-mono text-rose-600">
                                            -{formatCurrency(b1.total_costos_gastos)}
                                        </td>
                                    </tr>
                                    <tr className="bg-violet-50/60 dark:bg-violet-950/30 font-bold">
                                        <td className="py-2">14. Beneficio (o Pérdida) del Ejercicio:</td>
                                        <td className="py-2 text-right font-mono text-violet-600">
                                            {formatCurrency(b1.beneficio_neto)}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-2xs">
                            <div className="flex items-center justify-between mb-3">
                                <h4 className="text-xs font-bold text-slate-800 dark:text-white">
                                    Anexo A-1: Balance General Oficial
                                </h4>
                                <span className="px-2 py-0.5 rounded bg-emerald-500 text-white text-[10px] font-bold">
                                    ✓ Cuadrado: Activo = Pasivo + Capital
                                </span>
                            </div>
                            <table className="w-full text-xs">
                                <tbody className="divide-y divide-slate-200/70 dark:divide-slate-800">
                                    <tr>
                                        <td className="py-1.5">1.1 Cajas y Bancos (Efectivo):</td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(a1.caja_bancos)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">1.2 Cuentas por Cobrar Clientes:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(a1.cuentas_por_cobrar)}
                                        </td>
                                    </tr>
                                    <tr className="bg-slate-50 dark:bg-slate-800/60 font-bold">
                                        <td className="py-1.5">6. TOTAL ACTIVOS:</td>
                                        <td className="py-1.5 text-right font-mono text-indigo-600">
                                            {formatCurrency(a1.total_activos)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">Total Pasivos Corrientes:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold text-amber-600">
                                            {formatCurrency(a1.total_pasivos)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td className="py-1.5">10.1 Capital Suscrito y Pagado:</td>
                                        <td className="py-1.5 text-right font-mono font-semibold">
                                            {formatCurrency(a1.capital_social)}
                                        </td>
                                    </tr>
                                    <tr className="bg-slate-50 dark:bg-slate-800/60 font-bold">
                                        <td className="py-2">11. TOTAL PASIVOS Y PATRIMONIO:</td>
                                        <td className="py-2 text-right font-mono text-emerald-600">
                                            {formatCurrency(a1.total_pasivos_patrimonio)}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                <PoweredByBills variant="footer" />
            </div>

            {/* Pre-validation Modal (Identical to Gridbase Bills) */}
            {prevalModal && (
                <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                    <div className="bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden">
                        <div className="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div className="flex items-center gap-2.5">
                                <span className="bg-[#0284c7] text-white px-2 py-1 rounded text-[11px] font-bold">
                                    DGII {prevalModal.format || currentTab}
                                </span>
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Resultado de Pre-validación Fiscal
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setPrevalModal(null)}
                                className="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="p-5 overflow-y-auto flex-1 space-y-4">
                            {prevalModal.valid || prevalModal.isValid ? (
                                <div className="rounded-xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50 dark:bg-emerald-950/30 p-4 flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold shrink-0">
                                        ✓
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-bold text-emerald-950 dark:text-emerald-200">
                                            ¡Documento 100% Válido para DGII!
                                        </h4>
                                        <p className="text-xs text-emerald-800 dark:text-emerald-300/80">
                                            Cumple con el 100% de las normas, algoritmos de dígito verificador y estructuras de comprobantes de la DGII.
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/30 p-4 flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold shrink-0">
                                        ✕
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-bold text-rose-950 dark:text-rose-200">
                                            Se encontraron {(prevalModal.errors || []).length} errores en el reporte
                                        </h4>
                                        <p className="text-xs text-rose-800 dark:text-rose-300/80">
                                            La DGII rechazará este archivo. Revise el detalle a continuación para corregirlos.
                                        </p>
                                    </div>
                                </div>
                            )}

                            <div className="grid grid-cols-3 gap-3 bg-slate-50 dark:bg-slate-800/70 p-3 rounded-lg text-xs">
                                <div>
                                    <span className="text-slate-500 block">RNC Contribuyente:</span>
                                    <strong className="text-slate-900 dark:text-white">
                                        {prevalModal.rnc || billsCore?.emitterRnc}
                                    </strong>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">Período Fiscal:</span>
                                    <strong className="text-slate-900 dark:text-white">
                                        {prevalModal.period || `${selectedYear}${selectedMonth}`}
                                    </strong>
                                </div>
                                <div>
                                    <span className="text-slate-500 block">Comprobantes:</span>
                                    <strong className="text-slate-900 dark:text-white">
                                        {prevalModal.record_count ?? prevalModal.totalLines ?? 0} líneas
                                    </strong>
                                </div>
                            </div>

                            {(prevalModal.errors || []).length > 0 && (
                                <div>
                                    <h5 className="text-xs font-bold text-rose-600 mb-2">
                                        Errores que bloquean el envío ({prevalModal.errors.length}):
                                    </h5>
                                    <ul className="list-disc pl-5 text-xs text-rose-600 dark:text-rose-400 space-y-1 max-h-44 overflow-y-auto">
                                        {prevalModal.errors.map((err, i) => (
                                            <li key={i}>{err}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            {(prevalModal.warnings || []).length > 0 && (
                                <div>
                                    <h5 className="text-xs font-bold text-amber-600 mb-2">
                                        Advertencias informativas ({prevalModal.warnings.length}):
                                    </h5>
                                    <ul className="list-disc pl-5 text-xs text-amber-600 dark:text-amber-400 space-y-1 max-h-36 overflow-y-auto">
                                        {prevalModal.warnings.map((w, i) => (
                                            <li key={i}>{w}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>

                        <div className="px-5 py-3 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                            <button
                                type="button"
                                onClick={() => setPrevalModal(null)}
                                className="px-4 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 cursor-pointer"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
