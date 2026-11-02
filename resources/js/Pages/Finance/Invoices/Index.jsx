import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';
import {
    FileText,
    Search,
    Download,
    CheckCircle2,
    Clock,
    XCircle,
    RotateCcw,
    ShieldCheck,
    ExternalLink,
    FileCode2,
    PlusCircle,
    AlertTriangle,
    Printer,
    X,
    Sparkles,
} from 'lucide-react';

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

const fmtMoney = (n) =>
    'RD$ ' +
    Number(n || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

export default function FinanceInvoicesIndex({
    invoices,
    stats = {},
    filters = {},
    dgiiConfig = {},
}) {
    const [search, setSearch] = useState(filters.search || '');
    const [activeTab, setActiveTab] = useState(filters.tab || 'all');
    const [ncfTypeFilter, setNcfTypeFilter] = useState(filters.ncf_type || '');

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

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        applyFilters(activeTab, search, ncfTypeFilter);
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

    const submitEmitNow = (e) => {
        e.preventDefault();
        if (!emitTarget) return;
        setEmitProcessing(true);
        router.post(
            `/admin/finance/invoices/${emitTarget.id}/emit-ecf`,
            { ecf_type: emitEcfType },
            {
                preserveScroll: true,
                onFinish: () => {
                    setEmitProcessing(false);
                    setEmitTarget(null);
                },
            }
        );
    };

    const tabs = [
        { id: 'all', label: 'Todas las Facturas' },
        { id: 'paid', label: `Pagadas (${stats.totalPaidCount || 0})` },
        { id: 'pending', label: `Pendientes (${stats.totalPendingCount || 0})` },
        { id: 'credit_notes', label: `Notas de Crédito E34 (${stats.creditNotesCount || 0})` },
        { id: 'voided', label: `Anuladas 608 (${stats.voidedCount || 0})` },
        { id: 'dgii_audit', label: `Auditoría & XML DGII (${stats.ecfIssuedCount || 0})` },
    ];

    const envBadge = (() => {
        const env = dgiiConfig.environment || 'testing';
        if (env === 'production') {
            return { label: '🟢 PRODUCCIÓN DGII', bg: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30' };
        }
        if (env === 'certification') {
            return { label: '🟠 CERTIFICACIÓN DGII', bg: 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/30' };
        }
        return { label: '🔴 PRE-CERTIFICACIÓN / SIMULACIÓN', bg: 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/30' };
    })();

    const rows = invoices?.data || [];

    return (
        <AuthenticatedLayout>
            <Head title="Facturas y Comprobantes e-CF — Finanzas" />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div className="flex flex-wrap items-center gap-2.5 mb-2">
                            <PoweredByBills variant="badge" />
                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold border ${envBadge.bg}`}>
                                {envBadge.label}
                            </span>
                            {dgiiConfig.emitter_rnc && (
                                <span className="text-xs font-mono text-gray-500 dark:text-gray-400">
                                    RNC Emisor: {dgiiConfig.emitter_rnc}
                                </span>
                            )}
                        </div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                            Facturas, Comprobantes e-CF y Notas de Crédito
                        </h1>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Gestión centralizada de facturación fiscal, emisión de Notas de Crédito (E34/B04), anulaciones Formato 608 y descarga de XML firmado.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <Link
                            href="/admin/finance/dgii-reports"
                            className="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                        >
                            <FileText className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            Reportes 607 / 608
                        </Link>
                        <Link
                            href="/admin/pos"
                            className="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition"
                        >
                            <PlusCircle className="w-4 h-4" />
                            Nueva Factura (Caja POS)
                        </Link>
                    </div>
                </div>

                {/* KPI Strip — 1:1 Gridbase Bills */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Total Facturado
                            </span>
                            <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                        </div>
                        <div className="text-xl font-bold font-mono text-gray-900 dark:text-white mt-2">
                            {fmtMoney(stats.totalInvoiced)}
                        </div>
                        <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            {stats.totalPaidCount || 0} facturas cobradas
                        </p>
                    </div>

                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Por Cobrar
                            </span>
                            <Clock className="w-4 h-4 text-amber-500" />
                        </div>
                        <div className="text-xl font-bold font-mono text-amber-600 dark:text-amber-400 mt-2">
                            {fmtMoney(stats.totalPendingAmount)}
                        </div>
                        <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            {stats.totalPendingCount || 0} cargos pendientes
                        </p>
                    </div>

                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                e-CF / NCF Emitidos
                            </span>
                            <ShieldCheck className="w-4 h-4 text-indigo-500" />
                        </div>
                        <div className="text-xl font-bold font-mono text-indigo-600 dark:text-indigo-400 mt-2">
                            {stats.ecfIssuedCount || 0}
                        </div>
                        <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            Con código de seguridad DGII
                        </p>
                    </div>

                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Notas de Crédito (E34)
                            </span>
                            <RotateCcw className="w-4 h-4 text-purple-500" />
                        </div>
                        <div className="text-xl font-bold font-mono text-purple-600 dark:text-purple-400 mt-2">
                            {fmtMoney(stats.creditNotesAmount)}
                        </div>
                        <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            {stats.creditNotesCount || 0} documentos E34 / B04
                        </p>
                    </div>

                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-xs">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Anuladas (608)
                            </span>
                            <XCircle className="w-4 h-4 text-rose-500" />
                        </div>
                        <div className="text-xl font-bold font-mono text-rose-600 dark:text-rose-400 mt-2">
                            {stats.voidedCount || 0}
                        </div>
                        <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            Reportables en Formato 608
                        </p>
                    </div>
                </div>

                {/* Segmented Tabs + Search Bar */}
                <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 space-y-4 shadow-xs">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
                        <div className="flex flex-wrap items-center gap-1.5">
                            {tabs.map((t) => {
                                const active = activeTab === t.id;
                                return (
                                    <button
                                        key={t.id}
                                        type="button"
                                        onClick={() => applyFilters(t.id, search, ncfTypeFilter)}
                                        className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                                            active
                                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-xs'
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                        }`}
                                    >
                                        {t.label}
                                    </button>
                                );
                            })}
                        </div>

                        <form onSubmit={handleSearchSubmit} className="flex flex-wrap items-center gap-2">
                            <select
                                value={ncfTypeFilter}
                                onChange={(e) => applyFilters(activeTab, search, e.target.value)}
                                className="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs text-gray-800 dark:text-gray-200 px-2.5 py-2"
                            >
                                <option value="">Todos los Tipos NCF</option>
                                <option value="31">E31 / B01 · Crédito Fiscal</option>
                                <option value="32">E32 / B02 · Consumo</option>
                                <option value="34">E34 / B04 · Nota de Crédito</option>
                                <option value="44">E44 / B14 · Regímenes Especiales</option>
                                <option value="45">E45 / B15 · Gubernamental</option>
                            </select>

                            <div className="relative">
                                <Search className="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar por NCF, RNC, estudiante..."
                                    className="pl-8 pr-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs text-gray-900 dark:text-white w-64 focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                            <button
                                type="submit"
                                className="px-3 py-1.5 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-xs font-semibold hover:bg-gray-800 transition"
                            >
                                Filtrar
                            </button>
                        </form>
                    </div>

                    {/* Table */}
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-gray-800 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    <th className="py-3 px-3">Documento / Fecha</th>
                                    <th className="py-3 px-3">Cliente / Estudiante</th>
                                    <th className="py-3 px-3">Comprobante (e-NCF)</th>
                                    <th className="py-3 px-3">Concepto</th>
                                    <th className="py-3 px-3 text-right">Monto</th>
                                    <th className="py-3 px-3 text-center">Estado / DGII</th>
                                    <th className="py-3 px-3 text-right">Acciones Fiscales</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="py-12 text-center text-gray-400 dark:text-gray-500">
                                            No se encontraron facturas o comprobantes fiscales con los filtros seleccionados.
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((inv) => {
                                        const isPaid = ['paid', 'Completado', 'Pagado'].includes(inv.status);
                                        return (
                                            <tr
                                                key={inv.id}
                                                className="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition"
                                            >
                                                <td className="py-3 px-3 align-top">
                                                    <div className="font-mono font-bold text-gray-900 dark:text-white">
                                                        {inv.invoice_number}
                                                    </div>
                                                    <div className="text-[11px] text-gray-500 dark:text-gray-400">
                                                        {inv.date_formatted}
                                                    </div>
                                                </td>

                                                <td className="py-3 px-3 align-top">
                                                    <div className="font-semibold text-gray-900 dark:text-white">
                                                        {inv.client_name}
                                                    </div>
                                                    <div className="text-[11px] font-mono text-gray-500 dark:text-gray-400">
                                                        RNC/Céd: {inv.client_rnc}
                                                    </div>
                                                </td>

                                                <td className="py-3 px-3 align-top">
                                                    {inv.ncf ? (
                                                        <div>
                                                            <div className="inline-flex items-center gap-1.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-800">
                                                                {inv.ncf}
                                                            </div>
                                                            <div className="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                                {NCF_TYPE_LABELS[inv.ncf_type] || `Tipo ${inv.ncf_type}`}
                                                                {inv.security_code && (
                                                                    <span className="ml-1.5 font-mono text-emerald-600 dark:text-emerald-400">
                                                                        · Cód: {inv.security_code}
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    ) : (
                                                        <span className="text-[11px] text-gray-400 italic">
                                                            Sin NCF asignado
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-3 align-top max-w-xs">
                                                    <div className="text-gray-800 dark:text-gray-200 font-medium line-clamp-2">
                                                        {inv.concept}
                                                    </div>
                                                    {inv.notes && (
                                                        <div className="text-[10px] text-gray-400 dark:text-gray-500 line-clamp-1 mt-0.5">
                                                            {inv.notes}
                                                        </div>
                                                    )}
                                                </td>

                                                <td className="py-3 px-3 align-top text-right font-mono font-bold text-gray-900 dark:text-white">
                                                    <span className={inv.is_credit_note ? 'text-purple-600 dark:text-purple-400' : ''}>
                                                        {inv.is_credit_note ? '- ' : ''}
                                                        {fmtMoney(inv.amount)}
                                                    </span>
                                                </td>

                                                <td className="py-3 px-3 align-top text-center">
                                                    {inv.is_voided ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                                            <XCircle className="w-3 h-3" /> Anulada (608)
                                                        </span>
                                                    ) : inv.is_credit_note ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                                            <RotateCcw className="w-3 h-3" /> Nota Crédito E34
                                                        </span>
                                                    ) : isPaid ? (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                            <CheckCircle2 className="w-3 h-3" /> Pagada
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                                            <Clock className="w-3 h-3" /> Pendiente
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-3 align-top text-right">
                                                    <div className="inline-flex flex-wrap items-center justify-end gap-1">
                                                        {/* Recibo PDF */}
                                                        <a
                                                            href={`/admin/pos/receipt/${inv.id}/pdf`}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            title="Imprimir Recibo / Factura PDF"
                                                            className="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                                        >
                                                            <Printer className="w-3.5 h-3.5" />
                                                        </a>

                                                        {/* Descargar XML e-CF */}
                                                        <a
                                                            href={`/admin/finance/invoices/${inv.id}/xml`}
                                                            title="Descargar XML Estándar DGII e-CF"
                                                            className="p-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 transition"
                                                        >
                                                            <FileCode2 className="w-3.5 h-3.5" />
                                                        </a>

                                                        {/* Consulta QR DGII */}
                                                        {inv.qr_url && (
                                                            <a
                                                                href={inv.qr_url}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                title="Validar Timbre en Portal DGII"
                                                                className="p-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/50 transition"
                                                            >
                                                                <ExternalLink className="w-3.5 h-3.5" />
                                                            </a>
                                                        )}

                                                        {/* Emitir e-CF si no tiene NCF */}
                                                        {!inv.ncf && !inv.is_voided && isPaid && (
                                                            <button
                                                                type="button"
                                                                onClick={() => {
                                                                    setEmitTarget(inv);
                                                                    setEmitEcfType('32');
                                                                }}
                                                                title="Emitir Comprobante e-CF Ahora"
                                                                className="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-bold hover:bg-indigo-100 transition"
                                                            >
                                                                <Sparkles className="w-3 h-3" /> Emitir e-CF
                                                            </button>
                                                        )}

                                                        {/* Emitir Nota de Crédito E34 */}
                                                        {inv.ncf && !inv.is_credit_note && !inv.is_voided && (
                                                            <button
                                                                type="button"
                                                                onClick={() => openCreditNoteModal(inv)}
                                                                title="Emitir Nota de Crédito (E34 / B04)"
                                                                className="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-[10px] font-bold hover:bg-purple-100 transition"
                                                            >
                                                                <RotateCcw className="w-3 h-3" /> E34
                                                            </button>
                                                        )}

                                                        {/* Anular para 608 */}
                                                        {!inv.is_voided && !inv.is_credit_note && (
                                                            <button
                                                                type="button"
                                                                onClick={() => openVoidModal(inv)}
                                                                title="Anular Comprobante (Formato 608)"
                                                                className="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px] font-bold hover:bg-rose-100 transition"
                                                            >
                                                                <XCircle className="w-3 h-3" /> 608
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

                    {/* Pagination */}
                    {invoices?.links && invoices.links.length > 3 && (
                        <div className="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <span className="text-xs text-gray-500 dark:text-gray-400">
                                Mostrando {invoices.from || 0} a {invoices.to || 0} de {invoices.total || 0} documentos
                            </span>
                            <div className="flex flex-wrap gap-1">
                                {invoices.links.map((link, idx) => (
                                    <button
                                        key={idx}
                                        type="button"
                                        disabled={!link.url}
                                        onClick={() => link.url && router.visit(link.url, { preserveScroll: true })}
                                        className={`px-2.5 py-1 rounded text-xs font-medium ${
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : link.url
                                                ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200'
                                                : 'text-gray-400 cursor-not-allowed'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal: Emitir Nota de Crédito (E34 / B04) */}
            {creditNoteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <RotateCcw className="w-5 h-5 text-purple-600" />
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                    Emitir Nota de Crédito (E34)
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setCreditNoteTarget(null)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>

                        <div className="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 text-xs space-y-1">
                            <div className="font-semibold text-purple-900 dark:text-purple-200">
                                Comprobante Modificado: <span className="font-mono">{creditNoteTarget.ncf}</span>
                            </div>
                            <div className="text-purple-700 dark:text-purple-300">
                                Cliente: {creditNoteTarget.client_name} · Monto Original: {fmtMoney(creditNoteTarget.amount)}
                            </div>
                        </div>

                        <form onSubmit={submitCreditNote} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Código de Modificación DGII
                                </label>
                                <select
                                    value={cnModCode}
                                    onChange={(e) => setCnModCode(e.target.value)}
                                    className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white"
                                >
                                    <option value="1">1 - Anula el NCF Modificado (Total)</option>
                                    <option value="2">2 - Corrige Texto del Comprobante Fiscal</option>
                                    <option value="3">3 - Corrige Montos del NCF Modificado (Parcial/Total)</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Monto a Acreditar (RD$)
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    max={creditNoteTarget.amount}
                                    value={cnAmount}
                                    onChange={(e) => setCnAmount(e.target.value)}
                                    required
                                    className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-mono px-3 py-2 text-gray-900 dark:text-white"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Motivo / Justificación Fiscal
                                </label>
                                <textarea
                                    rows={3}
                                    value={cnReason}
                                    onChange={(e) => setCnReason(e.target.value)}
                                    required
                                    placeholder="Ej: Devolución o descuento aplicado sobre matrícula..."
                                    className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setCreditNoteTarget(null)}
                                    className="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={cnProcessing}
                                    className="px-4 py-2 rounded-lg text-xs font-semibold bg-purple-600 hover:bg-purple-700 text-white disabled:opacity-50"
                                >
                                    {cnProcessing ? 'Emitiendo E34...' : 'Emitir Nota de Crédito E34'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal: Anular Comprobante (Formato 608) */}
            {voidTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <AlertTriangle className="w-5 h-5 text-rose-600" />
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                    Anular Comprobante (Formato 608)
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setVoidTarget(null)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>

                        <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs space-y-1">
                            <div className="font-semibold text-rose-900 dark:text-rose-200">
                                Documento: <span className="font-mono">{voidTarget.ncf || voidTarget.invoice_number}</span>
                            </div>
                            <div className="text-rose-700 dark:text-rose-300">
                                Se registrará en el reporte mensual de NCF Anulados (Formato 608 DGII).
                            </div>
                        </div>

                        <form onSubmit={submitVoid} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Tipo de Anulación (Tabla DGII 608)
                                </label>
                                <select
                                    value={voidType608}
                                    onChange={(e) => setVoidType608(e.target.value)}
                                    className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white"
                                >
                                    {VOID_TYPES_608.map((vt) => (
                                        <option key={vt.code} value={vt.code}>
                                            {vt.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Comentario / Detalle de Auditoría
                                </label>
                                <textarea
                                    rows={3}
                                    value={voidReason}
                                    onChange={(e) => setVoidReason(e.target.value)}
                                    required
                                    placeholder="Explique la razón de la anulación del comprobante..."
                                    className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white"
                                />
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setVoidTarget(null)}
                                    className="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={voidProcessing}
                                    className="px-4 py-2 rounded-lg text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white disabled:opacity-50"
                                >
                                    {voidProcessing ? 'Anulando...' : 'Confirmar Anulación 608'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal: Emitir e-CF Manualmente */}
            {emitTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <Sparkles className="w-5 h-5 text-indigo-600" />
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                    Emitir Comprobante Fiscal (e-CF)
                                </h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setEmitTarget(null)}
                                className="text-gray-400 hover:text-gray-600"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>

                        <form onSubmit={submitEmitNow} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Tipo de Comprobante Fiscal a Asignar
                                </label>
                                <select
                                    value={emitEcfType}
                                    onChange={(e) => setEmitEcfType(e.target.value)}
                                    className="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white"
                                >
                                    <option value="32">E32 · Factura de Consumo Electrónica</option>
                                    <option value="31">E31 · Factura de Crédito Fiscal Electrónica</option>
                                    <option value="44">E44 · Regímenes Especiales Electrónico</option>
                                    <option value="45">E45 · Gubernamental Electrónico</option>
                                </select>
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                <button
                                    type="button"
                                    onClick={() => setEmitTarget(null)}
                                    className="px-3.5 py-2 rounded-lg text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={emitProcessing}
                                    className="px-4 py-2 rounded-lg text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50"
                                >
                                    {emitProcessing ? 'Generando e-CF...' : 'Generar Comprobante'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
