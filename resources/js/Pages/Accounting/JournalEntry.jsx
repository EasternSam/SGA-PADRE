import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function JournalEntry({ journals, accounts, defaultDate }) {
    const { data, setData, post, processing, errors } = useForm({
        journal_id: journals?.[0]?.id || '',
        date: defaultDate || new Date().toISOString().split('T')[0],
        description: '',
        lines: [
            { account_id: '', description: '', debit: '', credit: '' },
            { account_id: '', description: '', debit: '', credit: '' },
        ],
    });

    const [formError, setFormError] = useState('');

    const handleAddLine = () => {
        setData('lines', [
            ...data.lines,
            { account_id: '', description: '', debit: '', credit: '' },
        ]);
    };

    const handleRemoveLine = (index) => {
        if (data.lines.length <= 2) {
            setFormError('Un asiento contable requiere al menos 2 movimientos (partida doble).');
            return;
        }
        setFormError('');
        const updated = data.lines.filter((_, i) => i !== index);
        setData('lines', updated);
    };

    const handleLineChange = (index, field, value) => {
        setFormError('');
        const updated = [...data.lines];
        updated[index] = { ...updated[index], [field]: value };

        // Si ingresa débito, limpia crédito en la misma línea
        if (field === 'debit' && parseFloat(value) > 0) {
            updated[index].credit = '';
        }
        // Si ingresa crédito, limpia débito en la misma línea
        if (field === 'credit' && parseFloat(value) > 0) {
            updated[index].debit = '';
        }

        setData('lines', updated);
    };

    // Cálculos de totales
    let totalDebit = 0;
    let totalCredit = 0;
    data.lines.forEach((l) => {
        totalDebit += parseFloat(l.debit) || 0;
        totalCredit += parseFloat(l.credit) || 0;
    });

    totalDebit = Math.round(totalDebit * 100) / 100;
    totalCredit = Math.round(totalCredit * 100) / 100;
    const difference = Math.abs(Math.round((totalDebit - totalCredit) * 100) / 100);
    const isBalanced = totalDebit > 0 && totalDebit === totalCredit;

    const handleSubmit = (e) => {
        e.preventDefault();
        setFormError('');

        if (!isBalanced) {
            setFormError('El asiento está descuadrado. La suma de Débitos debe ser exactamente igual a los Créditos.');
            return;
        }

        post('/admin/finance/manual-entry', {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout title="Asiento Contable Manual">
            <Head title="Asiento Contable Manual - SGA" />

            <div className="space-y-6">
                {/* ── ENCABEZADO ── */}
                <header className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-6 transition-colors">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div className="flex items-center gap-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                                <Link href="/admin/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Inicio</Link>
                                <span>/</span>
                                <Link href="/admin/finance/dashboard" className="hover:text-brand-primary dark:hover:text-blue-400">Finanzas</Link>
                                <span>/</span>
                                <span className="text-gray-600 dark:text-gray-300">Asiento Manual</span>
                            </div>
                            <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Asiento Contable Manual
                            </h1>
                            
                        </div>

                        <div className="flex items-center gap-3">
                            <Link
                                href="/admin/finance/ledger"
                                className="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl font-semibold text-xs shadow-xs transition"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Ver Libro Mayor
                            </Link>
                        </div>
                    </div>
                </header>

                {formError && (
                    <div className="p-4 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60 rounded-2xl flex items-center gap-3 text-xs text-red-700 dark:text-red-300">
                        <svg className="w-5 h-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{formError}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* ── ENCABEZADO DEL ASIENTO ── */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-6 space-y-4">
                        <h2 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            Datos del Encabezado
                        </h2>

                        <div className="grid grid-cols-1 sm:grid-cols-12 gap-4">
                            <div className="sm:col-span-4">
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Diario Contable <span className="text-red-500">*</span>
                                </label>
                                <select
                                    required
                                    value={data.journal_id}
                                    onChange={(e) => setData('journal_id', e.target.value)}
                                    className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                >
                                    {journals?.map((j) => (
                                        <option key={j.id} value={j.id}>
                                            {j.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.journal_id && <p className="text-red-500 text-[11px] mt-1">{errors.journal_id}</p>}
                            </div>

                            <div className="sm:col-span-4">
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha del Asiento <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    required
                                    value={data.date}
                                    onChange={(e) => setData('date', e.target.value)}
                                    className="w-full px-3 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                />
                                {errors.date && <p className="text-red-500 text-[11px] mt-1">{errors.date}</p>}
                            </div>

                            <div className="sm:col-span-12">
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Concepto General / Glosa <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    required
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Ej: Depreciación de mobiliario y cómputo mes actual, ajuste bancario..."
                                    className="w-full px-3.5 py-2 text-xs sm:text-sm bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:bg-white dark:focus:bg-[#161622] focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100"
                                />
                                {errors.description && <p className="text-red-500 text-[11px] mt-1">{errors.description}</p>}
                            </div>
                        </div>
                    </div>

                    {/* ── MOVIMIENTOS CONTABLES (DEBE / HABER) ── */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden">
                        <div className="p-4 sm:p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <h2 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                Movimientos Contables (Debe / Haber)
                            </h2>
                            <button
                                type="button"
                                onClick={handleAddLine}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 rounded-xl text-xs font-bold transition"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                Añadir Línea
                            </button>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-xs text-left">
                                <thead className="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-gray-50/70 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800">
                                    <tr>
                                        <th className="px-6 py-3.5 w-5/12">Cuenta Contable</th>
                                        <th className="px-4 py-3.5 w-4/12">Descripción de la Línea</th>
                                        <th className="px-4 py-3.5 w-2/12 text-right">Débito (Debe)</th>
                                        <th className="px-4 py-3.5 w-2/12 text-right">Crédito (Haber)</th>
                                        <th className="px-4 py-3.5 w-12 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                    {data.lines.map((line, idx) => (
                                        <tr key={idx} className="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                            {/* Cuenta */}
                                            <td className="px-6 py-3 align-top">
                                                <select
                                                    required
                                                    value={line.account_id}
                                                    onChange={(e) => handleLineChange(idx, 'account_id', e.target.value)}
                                                    className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                                                >
                                                    <option value="">Seleccione una cuenta contable...</option>
                                                    {accounts?.map((acc) => (
                                                        <option key={acc.id} value={acc.id}>
                                                            {acc.code} - {acc.name}
                                                        </option>
                                                    ))}
                                                </select>
                                                {errors[`lines.${idx}.account_id`] && (
                                                    <p className="text-red-500 text-[10px] mt-1">{errors[`lines.${idx}.account_id`]}</p>
                                                )}
                                            </td>

                                            {/* Explicación */}
                                            <td className="px-4 py-3 align-top">
                                                <input
                                                    type="text"
                                                    required
                                                    value={line.description}
                                                    onChange={(e) => handleLineChange(idx, 'description', e.target.value)}
                                                    placeholder="Detalle del movimiento..."
                                                    className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                                                />
                                                {errors[`lines.${idx}.description`] && (
                                                    <p className="text-red-500 text-[10px] mt-1">{errors[`lines.${idx}.description`]}</p>
                                                )}
                                            </td>

                                            {/* Débito */}
                                            <td className="px-4 py-3 align-top">
                                                <div className="relative">
                                                    <span className="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-gray-400 font-bold">$</span>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        value={line.debit}
                                                        onChange={(e) => handleLineChange(idx, 'debit', e.target.value)}
                                                        placeholder="0.00"
                                                        className="w-full pl-6 pr-3 py-2 text-xs bg-white dark:bg-[#161622] border border-gray-200 dark:border-gray-700/60 rounded-xl text-right font-bold text-blue-700 dark:text-blue-400 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary"
                                                    />
                                                </div>
                                            </td>

                                            {/* Crédito */}
                                            <td className="px-4 py-3 align-top">
                                                <div className="relative">
                                                    <span className="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-gray-400 font-bold">$</span>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        value={line.credit}
                                                        onChange={(e) => handleLineChange(idx, 'credit', e.target.value)}
                                                        placeholder="0.00"
                                                        className="w-full pl-6 pr-3 py-2 text-xs bg-white dark:bg-[#161622] border border-gray-200 dark:border-gray-700/60 rounded-xl text-right font-bold text-amber-700 dark:text-amber-400 focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary"
                                                    />
                                                </div>
                                            </td>

                                            {/* Borrar */}
                                            <td className="px-4 py-3 text-center align-middle">
                                                {data.lines.length > 2 && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRemoveLine(idx)}
                                                        className="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition"
                                                        title="Eliminar línea"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* ── BARRA DE TOTALES Y CUADRE ── */}
                        <div className="p-4 sm:p-6 bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                {isBalanced ? (
                                    <div className="flex items-center gap-2 px-3.5 py-1.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs font-bold">
                                        <svg className="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Asiento Cuadrado (Partida Doble)
                                    </div>
                                ) : (
                                    <div className="flex items-center gap-2 px-3.5 py-1.5 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40 rounded-xl text-xs font-bold">
                                        <svg className="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Descuadre: RD$ {difference.toFixed(2)}
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center gap-6">
                                <div className="text-right">
                                    <span className="text-[11px] text-gray-400 block">Total Débitos</span>
                                    <strong className="text-sm sm:text-base font-black text-blue-700 dark:text-blue-400">
                                        RD$ {totalDebit.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </strong>
                                </div>
                                <div className="text-right">
                                    <span className="text-[11px] text-gray-400 block">Total Créditos</span>
                                    <strong className="text-sm sm:text-base font-black text-amber-700 dark:text-amber-400">
                                        RD$ {totalCredit.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                    </strong>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing || !isBalanced}
                                    className="px-6 py-2.5 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95 disabled:opacity-50"
                                >
                                    {processing ? 'Contabilizando...' : 'Contabilizar Asiento'}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
