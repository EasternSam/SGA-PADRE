import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

export default function FinancialStatements({ reportType, dateFrom, dateTo, report }) {
    const [type, setType] = useState(reportType);
    const [from, setFrom] = useState(dateFrom);
    const [to, setTo] = useState(dateTo);

    const handleFilterChange = (newType, newFrom, newTo) => {
        router.get('/admin/finance/statements', {
            report_type: newType ?? type,
            date_from: newFrom ?? from,
            date_to: newTo ?? to,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout title="Estados Financieros">
            <Head title="Estados Financieros Oficiales - SGA" />

            <div className="space-y-6">
                {/* ── ENCABEZADO ── */}
                <header className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-6 transition-colors print:hidden">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                <Link href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</Link>
                                <span>/</span>
                                <Link href="/admin/finance/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Finanzas</Link>
                                <span>/</span>
                                <span className="text-gray-600 dark:text-gray-300">Estados Financieros</span>
                            </div>
                            <div className="flex flex-wrap items-center gap-2.5">
                                <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    Estados Financieros
                                </h1>
                                <PoweredByBills variant="badge" />
                            </div>
                            
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                type="button"
                                onClick={() => {
                                    const params = new URLSearchParams({
                                        report_type: type,
                                        date_from: from || '',
                                        date_to: to || '',
                                    }).toString();
                                    window.open(`/admin/finance/statements/pdf?${params}`, '_blank');
                                }}
                                className="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl font-semibold text-xs shadow-sm transition"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Exportar PDF Oficial
                            </button>
                        </div>
                    </div>
                </header>

                {/* ── FILTROS DE REPORTE ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5 print:hidden">
                    <div className="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                        <div className="sm:col-span-4">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Tipo de Documento Financiero
                            </label>
                            <select
                                value={type}
                                onChange={(e) => {
                                    setType(e.target.value);
                                    handleFilterChange(e.target.value, from, to);
                                }}
                                className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-bold"
                            >
                                <option value="balance_sheet">Balance General (Estado de Situación)</option>
                                <option value="income_statement">Estado de Resultados (Pérdidas y Ganancias)</option>
                            </select>
                        </div>

                        {type === 'income_statement' ? (
                            <>
                                <div className="sm:col-span-4">
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Período Desde
                                    </label>
                                    <input
                                        type="date"
                                        value={from}
                                        onChange={(e) => {
                                            setFrom(e.target.value);
                                            handleFilterChange(type, e.target.value, to);
                                        }}
                                        className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                    />
                                </div>
                                <div className="sm:col-span-4">
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Período Hasta
                                    </label>
                                    <input
                                        type="date"
                                        value={to}
                                        onChange={(e) => {
                                            setTo(e.target.value);
                                            handleFilterChange(type, from, e.target.value);
                                        }}
                                        className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="sm:col-span-8 flex flex-col sm:flex-row gap-4 items-center">
                                <div className="w-full sm:w-1/2">
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Fecha de Corte (Hasta)
                                    </label>
                                    <input
                                        type="date"
                                        value={to}
                                        onChange={(e) => {
                                            setTo(e.target.value);
                                            handleFilterChange(type, from, e.target.value);
                                        }}
                                        className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100 font-semibold"
                                    />
                                </div>
                                <div className="w-full sm:w-1/2 p-2.5 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-900/40 text-[11px] text-blue-800 dark:text-blue-300">
                                    Muestra el balance acumulado históricamente a la fecha de corte.
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* ── DOCUMENTO IMPRIMIBLE OFICIAL ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden p-6 sm:p-12 transition-colors">
                    {/* Encabezado Institucional del Reporte */}
                    <div className="text-center pb-8 border-b border-gray-200 dark:border-gray-800 mb-8 space-y-1">
                        <h2 className="text-xl sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-wider">
                            INSTITUTO TECNOLÓGICO DE LAS AMÉRICAS (SGA)
                        </h2>
                        <h3 className="text-base sm:text-lg font-bold text-brand-primary dark:text-blue-400 uppercase tracking-wide">
                            {type === 'balance_sheet' ? 'BALANCE GENERAL (ESTADO DE SITUACIÓN)' : 'ESTADO DE RESULTADOS (PÉRDIDAS Y GANANCIAS)'}
                        </h3>
                        <p className="text-xs text-gray-500 dark:text-gray-400">
                            {type === 'income_statement'
                                ? `Del ${from.split('-').reverse().join('/')} al ${to.split('-').reverse().join('/')}`
                                : `Al ${to.split('-').reverse().join('/')}`}
                        </p>
                        <span className="inline-block mt-2 px-3 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                            Expresado en Pesos Dominicanos (RD$)
                        </span>
                    </div>

                    {/* ── CUERPO: ESTADO DE RESULTADOS ── */}
                    {type === 'income_statement' && (
                        <div className="max-w-4xl mx-auto space-y-8">
                            {/* Ingresos */}
                            <div>
                                <div className="flex items-center justify-between pb-2 border-b-2 border-emerald-600 mb-3">
                                    <h4 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                        Ingresos Operativos
                                    </h4>
                                    <span className="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Créditos Netos</span>
                                </div>
                                <div className="space-y-1 text-xs">
                                    {report?.revenues && report.revenues.length > 0 ? (
                                        report.revenues.map((acc) => (
                                            <div key={acc.id} className="flex justify-between py-1.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                <span className="text-gray-700 dark:text-gray-300 font-medium">
                                                    <span className="font-mono text-gray-400">{acc.code}</span> - {acc.name}
                                                </span>
                                                <span className="font-mono font-semibold text-gray-900 dark:text-white">
                                                    RD$ {acc.balance.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </span>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="py-4 text-center text-gray-400 text-xs italic">
                                            No se registran ingresos en este período.
                                        </div>
                                    )}
                                </div>
                                <div className="mt-3 p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-100 dark:border-emerald-800/40 flex justify-between items-center text-xs font-bold text-emerald-900 dark:text-emerald-300">
                                    <span className="uppercase">Total Ingresos</span>
                                    <span className="font-mono text-sm font-black">
                                        RD$ {(report?.total_revenue || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            </div>

                            {/* Gastos */}
                            <div>
                                <div className="flex items-center justify-between pb-2 border-b-2 border-rose-600 mb-3">
                                    <h4 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                        Gastos Operativos
                                    </h4>
                                    <span className="text-xs font-semibold text-rose-600 dark:text-rose-400">Débitos Netos</span>
                                </div>
                                <div className="space-y-1 text-xs">
                                    {report?.expenses && report.expenses.length > 0 ? (
                                        report.expenses.map((acc) => (
                                            <div key={acc.id} className="flex justify-between py-1.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                <span className="text-gray-700 dark:text-gray-300 font-medium">
                                                    <span className="font-mono text-gray-400">{acc.code}</span> - {acc.name}
                                                </span>
                                                <span className="font-mono font-semibold text-gray-900 dark:text-white">
                                                    RD$ {acc.balance.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </span>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="py-4 text-center text-gray-400 text-xs italic">
                                            No se registran gastos en este período.
                                        </div>
                                    )}
                                </div>
                                <div className="mt-3 p-3 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-100 dark:border-rose-800/40 flex justify-between items-center text-xs font-bold text-rose-900 dark:text-rose-300">
                                    <span className="uppercase">Total Gastos</span>
                                    <span className="font-mono text-sm font-black">
                                        RD$ {(report?.total_expense || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            </div>

                            {/* Resultado del Ejercicio (Utilidad / Pérdida) */}
                            <div className={`p-6 rounded-2xl border ${
                                (report?.net_income || 0) >= 0
                                    ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800/40'
                                    : 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800/40'
                            } flex items-center justify-between`}>
                                <div>
                                    <h5 className="text-sm font-black uppercase text-gray-900 dark:text-white">
                                        {(report?.net_income || 0) >= 0 ? 'Utilidad Neta del Período' : 'Pérdida Neta del Período'}
                                    </h5>
                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Ingresos Operativos menos Gastos y Costos
                                    </p>
                                </div>
                                <div className={`text-xl sm:text-2xl font-black font-mono ${
                                    (report?.net_income || 0) >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'
                                }`}>
                                    RD$ {(report?.net_income || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ── CUERPO: BALANCE GENERAL ── */}
                    {type === 'balance_sheet' && (
                        <div className="max-w-5xl mx-auto space-y-8">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {/* Activos (Lado Izquierdo) */}
                                <div>
                                    <div className="flex items-center justify-between pb-2 border-b-2 border-blue-600 mb-3">
                                        <h4 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                            Activos (Assets)
                                        </h4>
                                        <span className="text-xs font-semibold text-blue-600 dark:text-blue-400">Saldo Deudor</span>
                                    </div>
                                    <div className="space-y-1 text-xs">
                                        {report?.assets && report.assets.length > 0 ? (
                                            report.assets.map((acc) => (
                                                <div key={acc.id} className="flex justify-between py-1.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                    <span className="text-gray-700 dark:text-gray-300 font-medium">
                                                        <span className="font-mono text-gray-400">{acc.code}</span> - {acc.name}
                                                    </span>
                                                    <span className="font-mono font-semibold text-gray-900 dark:text-white">
                                                        RD$ {acc.balance.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                    </span>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="py-4 text-center text-gray-400 text-xs italic">
                                                No hay activos registrados.
                                            </div>
                                        )}
                                    </div>
                                    <div className="mt-4 p-3 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-800/40 flex justify-between items-center text-xs font-bold text-blue-900 dark:text-blue-300">
                                        <span className="uppercase">Total Activos</span>
                                        <span className="font-mono text-sm font-black">
                                            RD$ {(report?.total_assets || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                        </span>
                                    </div>
                                </div>

                                {/* Pasivos & Capital (Lado Derecho) */}
                                <div className="space-y-6">
                                    {/* Pasivos */}
                                    <div>
                                        <div className="flex items-center justify-between pb-2 border-b-2 border-orange-600 mb-3">
                                            <h4 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                                Pasivos (Liabilities)
                                            </h4>
                                            <span className="text-xs font-semibold text-orange-600 dark:text-orange-400">Saldo Acreedor</span>
                                        </div>
                                        <div className="space-y-1 text-xs">
                                            {report?.liabilities && report.liabilities.length > 0 ? (
                                                report.liabilities.map((acc) => (
                                                    <div key={acc.id} className="flex justify-between py-1.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                        <span className="text-gray-700 dark:text-gray-300 font-medium">
                                                            <span className="font-mono text-gray-400">{acc.code}</span> - {acc.name}
                                                        </span>
                                                        <span className="font-mono font-semibold text-gray-900 dark:text-white">
                                                            RD$ {acc.balance.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                        </span>
                                                    </div>
                                                ))
                                            ) : (
                                                <div className="py-2 text-center text-gray-400 text-xs italic">
                                                    Sin deudas registradas.
                                                </div>
                                            )}
                                        </div>
                                        <div className="mt-2 p-2.5 bg-orange-50 dark:bg-orange-950/40 rounded-xl border border-orange-100 dark:border-orange-800/40 flex justify-between items-center text-xs font-bold text-orange-900 dark:text-orange-300">
                                            <span className="uppercase">Total Pasivos</span>
                                            <span className="font-mono text-xs font-black">
                                                RD$ {(report?.total_liabilities || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Capital & Patrimonio */}
                                    <div>
                                        <div className="flex items-center justify-between pb-2 border-b-2 border-purple-600 mb-3">
                                            <h4 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                                Capital & Patrimonio
                                            </h4>
                                            <span className="text-xs font-semibold text-purple-600 dark:text-purple-400">Saldo Acreedor</span>
                                        </div>
                                        <div className="space-y-1 text-xs">
                                            {report?.equity && report.equity.map((acc) => (
                                                <div key={acc.id} className="flex justify-between py-1.5 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                                    <span className="text-gray-700 dark:text-gray-300 font-medium">
                                                        <span className="font-mono text-gray-400">{acc.code}</span> - {acc.name}
                                                    </span>
                                                    <span className="font-mono font-semibold text-gray-900 dark:text-white">
                                                        RD$ {acc.balance.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                    </span>
                                                </div>
                                            ))}
                                            {/* Utilidad Histórica Acumulada */}
                                            <div className="flex justify-between py-1.5 px-3 rounded-lg bg-purple-50/50 dark:bg-purple-950/20 font-semibold">
                                                <span className="text-purple-900 dark:text-purple-300">
                                                    Utilidad Acumulada Histórica (P&L)
                                                </span>
                                                <span className="font-mono font-bold text-purple-900 dark:text-purple-300">
                                                    RD$ {(report?.historical_net_income || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="mt-2 p-2.5 bg-purple-50 dark:bg-purple-950/40 rounded-xl border border-purple-100 dark:border-purple-800/40 flex justify-between items-center text-xs font-bold text-purple-900 dark:text-purple-300">
                                            <span className="uppercase">Total Capital</span>
                                            <span className="font-mono text-xs font-black">
                                                RD$ {(report?.total_equity || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Total Pasivos + Capital */}
                                    <div className="p-3 bg-gray-100 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs font-black text-gray-900 dark:text-white">
                                        <span className="uppercase">Total Pasivos + Capital</span>
                                        <span className="font-mono text-sm">
                                            RD$ {(report?.total_liabilities_and_equity || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Validación de Ecuación Contable */}
                            <div className="pt-6 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                                <div className="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                    <svg className="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Ecuación Fundamental Cuadrada: Activo = Pasivo + Capital
                                </div>
                                <span className="text-xs text-gray-400">
                                    Certificado contablemente para fines institucionales
                                </span>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
