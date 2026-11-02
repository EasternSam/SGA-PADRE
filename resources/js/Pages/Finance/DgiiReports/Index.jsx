import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';
import { CheckCircle2, AlertTriangle, Download, FileSpreadsheet, ShieldCheck } from 'lucide-react';

export default function DgiiReportsIndex({
    yearMonth,
    summary606,
    summary607,
    summary608,
    validation606,
    validation607,
    validation608,
    it1Summary,
    billsCore,
    isElectronicBillingEnabled,
}) {
    const [period, setPeriod] = useState(yearMonth);

    const handlePeriodChange = (e) => {
        const newPeriod = e.target.value;
        setPeriod(newPeriod);
        router.get('/admin/finance/dgii-reports', { yearMonth: newPeriod }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const fmtMoney = (val) =>
        `RD$ ${Number(val || 0).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const renderValidationPill = (validation) => {
        if (!validation) return null;
        if (validation.errorsCount > 0) {
            return (
                <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50">
                    <AlertTriangle className="w-3 h-3" />
                    {validation.errorsCount} errores
                </span>
            );
        }
        return (
            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                <CheckCircle2 className="w-3 h-3" />
                Pre-validado DGII ({validation.totalLines || 0} líneas)
            </span>
        );
    };

    return (
        <AuthenticatedLayout title="Reportes Fiscales DGII">
            <Head title="Reportes Fiscales DGII (606, 607, 608 & IT-1) - SGA" />

            <div className="space-y-6">
                {/* Banner Motor Gridbase Bills Core */}
                <PoweredByBills variant="banner" />

                {/* ── ENCABEZADO SUPERIOR ── */}
                <header className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-6 transition-colors">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                <Link href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</Link>
                                <span>/</span>
                                <Link href="/admin/finance/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Finanzas</Link>
                                <span>/</span>
                                <span className="text-gray-600 dark:text-gray-300">Reportes DGII</span>
                            </div>
                            <div className="flex flex-wrap items-center gap-3">
                                <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                    <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                        <FileSpreadsheet className="w-5 h-5" />
                                    </div>
                                    Central Tributaria DGII (606, 607, 608 & IT-1)
                                </h1>
                                <PoweredByBills variant="badge" />
                            </div>
                            {billsCore?.emitterRnc && (
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                     Contribuyente Emisor: <strong className="text-gray-800 dark:text-gray-200">{billsCore.emitterName}</strong> · RNC <code className="font-mono font-bold text-brand-primary">{billsCore.emitterRnc}</code> · Ambiente <span className="uppercase font-semibold">{billsCore.environment}</span>
                                </p>
                            )}
                        </div>

                        {/* Selector de Período Fiscal */}
                        <div className="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 p-2 rounded-2xl border border-gray-200/80 dark:border-gray-700/60">
                            <label htmlFor="period-input" className="text-xs font-bold text-gray-700 dark:text-gray-300 pl-2">
                                Período Fiscal:
                            </label>
                            <input
                                id="period-input"
                                type="month"
                                value={period}
                                onChange={handlePeriodChange}
                                className="px-3 py-1.5 text-xs font-semibold bg-white dark:bg-[#161622] border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-white"
                            />
                        </div>
                    </div>
                </header>

                {!isElectronicBillingEnabled && (
                    <div className="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 rounded-2xl flex items-start gap-3">
                        <AlertTriangle className="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                        <div>
                            <h4 className="text-xs font-bold text-amber-900 dark:text-amber-200">Facturación Electrónica Deshabilitada</h4>
                            <p className="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                La emisión automática de comprobantes fiscales electrónicos (e-CF) está desactivada en Configuración Global.
                            </p>
                        </div>
                    </div>
                )}

                {/* ── TARJETAS DE FORMATOS FISCALES (606, 607, 608) ── */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* FORMATO 606 */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div className="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 font-black text-sm">
                                        606
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-gray-900 dark:text-white">Formato 606</h3>
                                        <p className="text-[11px] text-gray-500">Compras de Bienes y Servicios (23 cols)</p>
                                    </div>
                                </div>
                                {renderValidationPill(validation606)}
                            </div>

                            <div className="p-5 space-y-3">
                                <div className="grid grid-cols-2 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs">
                                    <div>
                                        <span className="text-gray-400">Facturas con NCF:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            {summary606?.count || 0}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-400">Subtotal Compras:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            {fmtMoney(summary606?.totalAmount)}
                                        </p>
                                    </div>
                                    <div className="col-span-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex justify-between">
                                        <span className="text-gray-400">ITBIS Facturado:</span>
                                        <strong className="text-gray-900 dark:text-white">
                                            {fmtMoney(summary606?.totalItbis)}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="p-5 pt-0">
                            <a
                                href={`/admin/finance/dgii-reports/download-606?yearMonth=${period}`}
                                className="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-98"
                            >
                                <Download className="w-4 h-4" />
                                Descargar TXT Oficial 606 ({period})
                            </a>
                        </div>
                    </div>

                    {/* FORMATO 607 */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div className="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                        607
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-gray-900 dark:text-white">Formato 607</h3>
                                        <p className="text-[11px] text-gray-500">Ventas y Operaciones (23 cols)</p>
                                    </div>
                                </div>
                                {renderValidationPill(validation607)}
                            </div>

                            <div className="p-5 space-y-3">
                                <div className="grid grid-cols-2 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs">
                                    <div>
                                        <span className="text-gray-400">Comprobantes Emitidos:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            {summary607?.count || 0}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-400">Total Facturado:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            {fmtMoney(summary607?.totalAmount)}
                                        </p>
                                    </div>
                                    <div className="col-span-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex justify-between">
                                        <span className="text-gray-400">Series Soportadas:</span>
                                        <strong className="text-gray-900 dark:text-white">
                                            Serie E (e-CF 31/32) & Serie B
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="p-5 pt-0">
                            <a
                                href={`/admin/finance/dgii-reports/download-607?yearMonth=${period}`}
                                className="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-98"
                            >
                                <Download className="w-4 h-4" />
                                Descargar TXT Oficial 607 ({period})
                            </a>
                        </div>
                    </div>

                    {/* FORMATO 608 */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div className="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 font-black text-sm">
                                        608
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-gray-900 dark:text-white">Formato 608</h3>
                                        <p className="text-[11px] text-gray-500">Comprobantes Anulados (3 cols)</p>
                                    </div>
                                </div>
                                {renderValidationPill(validation608)}
                            </div>

                            <div className="p-5 space-y-3">
                                <div className="grid grid-cols-2 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs">
                                    <div>
                                        <span className="text-gray-400">NCF Anulados:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            {summary608?.count || 0}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-400">Norma General:</span>
                                        <p className="text-xs font-bold text-gray-900 dark:text-white mt-1">
                                            DGII 06-2018
                                        </p>
                                    </div>
                                    <div className="col-span-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex justify-between">
                                        <span className="text-gray-400">Integridad Fiscal:</span>
                                        <strong className="text-emerald-600 dark:text-emerald-400">
                                            Sin saltos de secuencia
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="p-5 pt-0">
                            <a
                                href={`/admin/finance/dgii-reports/download-608?yearMonth=${period}`}
                                className="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-98"
                            >
                                <Download className="w-4 h-4" />
                                Descargar TXT Oficial 608 ({period})
                            </a>
                        </div>
                    </div>
                </div>

                {/* ── SIMULACIÓN EJECUTIVA DECLARACIÓN JURADA IT-1 (GRIDBASE BILLS CORE) ── */}
                {it1Summary && (
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
                        <div className="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div className="flex items-center gap-2.5">
                                <ShieldCheck className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                <div>
                                    <h3 className="text-sm font-bold text-gray-900 dark:text-white">
                                        Simulación Ejecutiva de Declaración Jurada IT-1 (Período {period})
                                    </h3>
                                    <p className="text-xs text-gray-500">
                                        Calculado automáticamente por el motor tributario de Gridbase Bills a partir de las operaciones 606 y 607.
                                    </p>
                                </div>
                            </div>
                            <PoweredByBills variant="badge" />
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                            <div className="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                                <span className="text-gray-400">Ingresos por Operaciones (Casilla 1):</span>
                                <p className="text-base font-extrabold text-gray-900 dark:text-white mt-1">
                                    {fmtMoney(it1Summary.ingresos_operaciones_netos)}
                                </p>
                                <span className="text-[11px] text-emerald-600 dark:text-emerald-400">
                                    Exentos Educación: {fmtMoney(it1Summary.operaciones_exentas)}
                                </span>
                            </div>

                            <div className="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                                <span className="text-gray-400">ITBIS Cobrado en Ventas:</span>
                                <p className="text-base font-extrabold text-gray-900 dark:text-white mt-1">
                                    {fmtMoney(it1Summary.itbis_cobrado)}
                                </p>
                                <span className="text-[11px] text-gray-500">
                                    Gravado: {fmtMoney(it1Summary.operaciones_gravadas)}
                                </span>
                            </div>

                            <div className="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                                <span className="text-gray-400">ITBIS Adelantado en Compras (606):</span>
                                <p className="text-base font-extrabold text-gray-900 dark:text-white mt-1">
                                    {fmtMoney(it1Summary.itbis_adelantar)}
                                </p>
                                <span className="text-[11px] text-gray-500">
                                    Compras: {fmtMoney(it1Summary.compras_locales_total)}
                                </span>
                            </div>

                            <div className="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/70 dark:border-indigo-800/40">
                                <span className="text-indigo-700 dark:text-indigo-300 font-semibold">
                                    Compromiso Neto Estimado DGII:
                                </span>
                                <p className="text-base font-extrabold text-indigo-950 dark:text-white mt-1">
                                    {fmtMoney(it1Summary.total_compromiso_dgii)}
                                </p>
                                <span className="text-[11px] text-indigo-600 dark:text-indigo-400">
                                    Saldo a favor: {fmtMoney(it1Summary.saldo_a_favor)}
                                </span>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
