import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function DgiiReportsIndex({
    yearMonth,
    summary606,
    summary607,
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

    return (
        <AuthenticatedLayout title="Reportes Fiscales DGII">
            <Head title="Reportes Fiscales DGII (606 & 607) - SGA" />

            <div className="space-y-6 sm:space-y-8">
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
                            <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Reportes Fiscales DGII
                            </h1>
                            
                        </div>

                        {/* Selector de Período Fiscal */}
                        <div className="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 p-2 rounded-2xl border border-gray-200/80 dark:border-gray-700/60">
                            <label htmlFor="period-input" className="text-xs font-bold text-gray-700 dark:text-gray-300 pl-2">
                                Período:
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
                        <svg className="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <h4 className="text-xs font-bold text-amber-900 dark:text-amber-200">Facturación Electrónica Deshabilitada</h4>
                            <p className="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                La emisión de comprobantes fiscales electrónicos (e-CF) no está activa en la configuración global. Los reportes se generarán con los datos estándar locales.
                            </p>
                        </div>
                    </div>
                )}

                {/* ── TARJETAS DE FORMATOS FISCALES ── */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* FORMATO 606 */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div className="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 rounded-xl bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 font-black text-sm">
                                        606
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-gray-900 dark:text-white">Formato 606</h3>
                                    </div>
                                </div>
                                <span className="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-800/40">
                                    Mensual
                                </span>
                            </div>

                            <div className="p-6 space-y-4">
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
                                            RD$ {(summary606?.totalAmount || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                        </p>
                                    </div>
                                    <div className="col-span-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex justify-between">
                                        <span className="text-gray-400">ITBIS Declarado:</span>
                                        <strong className="text-gray-900 dark:text-white">
                                            RD$ {(summary606?.totalItbis || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="p-6 pt-0">
                            <a
                                href={`/admin/finance/dgii-reports/download-606?yearMonth=${period}`}
                                className="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-98"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Descargar 606_{period}.txt
                            </a>
                        </div>
                    </div>

                    {/* FORMATO 607 */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div className="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                        607
                                    </div>
                                    <div>
                                        <h3 className="text-base font-bold text-gray-900 dark:text-white">Formato 607</h3>
                                    </div>
                                </div>
                                <span className="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                    Mensual
                                </span>
                            </div>

                            <div className="p-6 space-y-4">
                                <div className="grid grid-cols-2 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-xs">
                                    <div>
                                        <span className="text-gray-400">Transacciones con NCF:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            {summary607?.count || 0}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-400">Total Ingresos:</span>
                                        <p className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                            RD$ {(summary607?.totalAmount || 0).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                        </p>
                                    </div>
                                    <div className="col-span-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 flex justify-between">
                                        <span className="text-gray-400">Tipo de Documento:</span>
                                        <strong className="text-gray-900 dark:text-white">
                                            B01 / B02 / e-CF
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="p-6 pt-0">
                            <a
                                href={`/admin/finance/dgii-reports/download-607?yearMonth=${period}`}
                                className="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-98"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Descargar 607_{period}.txt
                            </a>
                        </div>
                    </div>
                </div>

                {/* ── NOTA DE CONFORMIDAD NORMATIVA DGII ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6">
                    <h3 className="text-sm font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <svg className="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Instrucciones de Carga en la Oficina Virtual (OFV)
                    </h3>
                    <ul className="text-xs text-gray-500 dark:text-gray-400 space-y-1.5 list-disc pl-5">
                        <li>Los archivos descargados están codificados en formato texto plano UTF-8 delimitado por plecas (<code className="text-brand-primary font-mono">|</code>).</li>
                        <li>Valida previamente las estructuras en la herramienta oficial de validación de la DGII antes de enviar la declaración mensual.</li>
                        <li>La fecha límite para remitir los formatos 606 y 607 es el día 15 de cada mes respecto a las operaciones del mes anterior.</li>
                    </ul>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
