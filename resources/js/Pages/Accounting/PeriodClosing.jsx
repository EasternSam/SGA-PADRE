import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PeriodClosing({ lockDate }) {
    const { data, setData, post, processing, errors } = useForm({
        lock_date: lockDate || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/finance/period-closing', {
            preserveScroll: true,
        });
    };

    const handleClear = () => {
        setData('lock_date', '');
        post('/admin/finance/period-closing', {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout title="Cierre de Período">
            <Head title="Cierre de Período Contable - SGA" />

            <div className="space-y-6 max-w-4xl mx-auto">
                {/* ── ENCABEZADO ── */}
                <header className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-6 transition-colors">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                            <Link href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</Link>
                            <span>/</span>
                            <Link href="/admin/finance/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Finanzas</Link>
                            <span>/</span>
                            <span className="text-gray-600 dark:text-gray-300">Cierre de Período</span>
                        </div>
                        <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <div className="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            Cierre de Período Contable
                        </h1>
                        
                    </div>
                </header>

                {/* ── ESTADO ACTUAL ── */}
                <div className={`p-4 rounded-2xl border ${
                    lockDate
                        ? 'bg-rose-50/70 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/50'
                        : 'bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/50'
                } flex items-center justify-between`}>
                    <div className="flex items-center gap-3">
                        <div className={`p-2 rounded-xl ${
                            lockDate
                                ? 'bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300'
                                : 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300'
                        }`}>
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {lockDate ? (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                ) : (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                )}
                            </svg>
                        </div>
                        <div>
                            <h3 className="text-xs font-bold text-gray-900 dark:text-white">
                                {lockDate ? `Período Bloqueado hasta el: ${lockDate}` : 'Período Contable Abierto'}
                            </h3>
                            <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                {lockDate
                                    ? 'No se permiten registros, ediciones ni eliminaciones con fecha igual o anterior.'
                                    : 'Todas las transacciones históricas pueden recibir movimientos.'}
                            </p>
                        </div>
                    </div>
                </div>

                {/* ── FORMULARIO DE BLOQUEO ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 sm:p-8 space-y-6">
                    <div>
                        <h2 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            Configuración de Bloqueo
                        </h2>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Fecha de Cierre Contable
                            </label>
                            <input
                                type="date"
                                value={data.lock_date}
                                onChange={(e) => setData('lock_date', e.target.value)}
                                className="w-full sm:max-w-xs px-3.5 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-semibold"
                            />
                            {errors.lock_date && <p className="text-red-500 text-[11px] mt-1">{errors.lock_date}</p>}
                        </div>

                        <div className="p-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl border border-gray-100 dark:border-gray-800 text-xs text-gray-600 dark:text-gray-300 space-y-2">
                            <h4 className="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <svg className="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Implicaciones del Cierre de Período:
                            </h4>
                            <ul className="list-disc pl-5 space-y-1 text-[11px] text-gray-500 dark:text-gray-400">
                                <li>El motor contable rechazará automáticamente cualquier asiento de diario, factura de gasto o cobro con fecha anterior a la configurada.</li>
                                <li>Para realizar ajustes en períodos cerrados, un Administrador debe temporalmente modificar esta fecha o liberarla.</li>
                            </ul>
                        </div>

                        <div className="flex items-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-5 py-2.5 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95 disabled:opacity-50"
                            >
                                {processing ? 'Guardando...' : 'Guardar Configuración'}
                            </button>

                            {lockDate && (
                                <button
                                    type="button"
                                    onClick={handleClear}
                                    className="px-4 py-2.5 bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-xs transition"
                                >
                                    Liberar Bloqueo (Abrir Período)
                                </button>
                            )}
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
