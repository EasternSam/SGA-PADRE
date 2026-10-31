import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Ledger({ entries, journals, filters }) {
    const [journalId, setJournalId] = useState(filters?.journal_id || '');
    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');
    const [status, setStatus] = useState(filters?.status || 'posted');

    const handleFilter = (newJournal, newFrom, newTo, newStatus) => {
        router.get('/admin/finance/ledger', {
            journal_id: newJournal ?? journalId,
            date_from: newFrom ?? dateFrom,
            date_to: newTo ?? dateTo,
            status: newStatus ?? status,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout title="Libro Mayor">
            <Head title="Libro Mayor General - SGA" />

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
                                <span className="text-gray-600 dark:text-gray-300">Libro Mayor</span>
                            </div>
                            <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-brand-primary dark:text-blue-400">
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                Libro Mayor General
                            </h1>
                            
                        </div>

                        <div className="flex items-center gap-3">
                            <Link
                                href="/admin/finance/manual-entry"
                                className="inline-flex items-center gap-2 px-4 py-2 bg-brand-primary hover:bg-brand-dark text-white rounded-xl font-bold text-xs shadow-sm transition active:scale-95"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                Nuevo Asiento
                            </Link>
                        </div>
                    </div>
                </header>

                {/* ── BARRA DE FILTROS ── */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 p-4 sm:p-5">
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Filtrar por Diario
                            </label>
                            <select
                                value={journalId}
                                onChange={(e) => {
                                    setJournalId(e.target.value);
                                    handleFilter(e.target.value, dateFrom, dateTo, status);
                                }}
                                className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                            >
                                <option value="">Todos los Diarios</option>
                                {journals?.map((j) => (
                                    <option key={j.id} value={j.id}>
                                        {j.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Fecha Desde
                            </label>
                            <input
                                type="date"
                                value={dateFrom}
                                onChange={(e) => {
                                    setDateFrom(e.target.value);
                                    handleFilter(journalId, e.target.value, dateTo, status);
                                }}
                                className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Fecha Hasta
                            </label>
                            <input
                                type="date"
                                value={dateTo}
                                onChange={(e) => {
                                    setDateTo(e.target.value);
                                    handleFilter(journalId, dateFrom, e.target.value, status);
                                }}
                                className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl text-gray-900 dark:text-gray-100"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Estado del Asiento
                            </label>
                            <select
                                value={status}
                                onChange={(e) => {
                                    setStatus(e.target.value);
                                    handleFilter(journalId, dateFrom, dateTo, e.target.value);
                                }}
                                className="w-full px-3 py-2 text-xs bg-gray-50/70 dark:bg-[#1a1a2e] border border-gray-200 dark:border-gray-700/60 rounded-xl focus:ring-2 focus:ring-brand-primary/20 focus:border-brand-primary text-gray-900 dark:text-gray-100 font-medium"
                            >
                                <option value="all">Ver Todos</option>
                                <option value="posted">Contabilizados (Firmes)</option>
                                <option value="draft">En Borrador</option>
                                <option value="void">Anulados</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* ── LISTADO DE ASIENTOS CONTABLES ── */}
                <div className="space-y-4">
                    {entries?.data && entries.data.length > 0 ? (
                        entries.data.map((entry) => (
                            <div
                                key={entry.id}
                                className="bg-white dark:bg-[#161622] rounded-2xl shadow-xs border border-gray-100 dark:border-gray-800 overflow-hidden"
                            >
                                {/* Encabezado del Asiento */}
                                <div className="p-4 sm:p-5 bg-gray-50/70 dark:bg-gray-900/30 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div className="flex flex-wrap items-center gap-2.5">
                                        <h3 className="text-sm font-bold text-gray-900 dark:text-white font-mono">
                                            Asiento #{entry.number}
                                        </h3>

                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-200/80 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                            {entry.journal_name}
                                        </span>

                                        {entry.status === 'posted' ? (
                                            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-50 dark:bg-green-950/60 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/40">
                                                <span className="w-1.5 h-1.5 rounded-full bg-green-500"></span> Contabilizado
                                            </span>
                                        ) : entry.status === 'draft' ? (
                                            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                                <span className="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Borrador
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                                                <span className="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Anulado
                                            </span>
                                        )}

                                        <span className="text-xs text-gray-400 dark:text-gray-500">
                                            • {entry.date_formatted}
                                        </span>

                                        {entry.reference_type && (
                                            <span className="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                                                [Ref: {entry.reference_type} #{entry.reference_id}]
                                            </span>
                                        )}
                                    </div>

                                    <div className="text-xs font-medium text-gray-600 dark:text-gray-300 max-w-md truncate">
                                        {entry.description || <span className="italic text-gray-400">Sin concepto</span>}
                                    </div>
                                </div>

                                {/* Tabla de Líneas del Asiento */}
                                <div className="overflow-x-auto">
                                    <table className="w-full text-xs text-left">
                                        <thead className="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider bg-white dark:bg-[#161622] border-b border-gray-100 dark:border-gray-800">
                                            <tr>
                                                <th className="px-6 py-2.5 w-4/12">Cuenta Contable</th>
                                                <th className="px-4 py-2.5 w-4/12">Explicación</th>
                                                <th className="px-4 py-2.5 w-2/12 text-right">Débito (Debe)</th>
                                                <th className="px-4 py-2.5 w-2/12 text-right">Crédito (Haber)</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-50 dark:divide-gray-800/60 text-gray-700 dark:text-gray-300">
                                            {entry.lines.map((line) => (
                                                <tr key={line.id} className="hover:bg-gray-50/30 dark:hover:bg-gray-800/10 transition-colors">
                                                    <td className="px-6 py-2.5 font-medium">
                                                        <span className="font-mono text-gray-500 dark:text-gray-400">{line.account_code}</span> - {line.account_name}
                                                    </td>
                                                    <td className="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                        {line.description || '-'}
                                                    </td>
                                                    <td className="px-4 py-2.5 text-right font-mono font-semibold text-blue-700 dark:text-blue-400">
                                                        {line.debit > 0 ? `RD$ ${line.debit.toLocaleString('es-DO', { minimumFractionDigits: 2 })}` : '-'}
                                                    </td>
                                                    <td className="px-4 py-2.5 text-right font-mono font-semibold text-amber-700 dark:text-amber-400">
                                                        {line.credit > 0 ? `RD$ ${line.credit.toLocaleString('es-DO', { minimumFractionDigits: 2 })}` : '-'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                        <tfoot className="bg-gray-50/60 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-800 text-xs font-bold">
                                            <tr>
                                                <td colSpan="2" className="px-6 py-2.5 text-right text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">
                                                    Total Asiento
                                                </td>
                                                <td className="px-4 py-2.5 text-right font-mono font-black text-blue-700 dark:text-blue-400">
                                                    RD$ {entry.total_debit.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </td>
                                                <td className="px-4 py-2.5 text-right font-mono font-black text-amber-700 dark:text-amber-400">
                                                    RD$ {entry.total_credit.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 p-12 text-center text-gray-400">
                            <svg className="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h3 className="font-semibold text-gray-700 dark:text-gray-300 text-sm">No se encontraron asientos contables</h3>
                            <p className="text-xs text-gray-400 mt-1">Ajusta los filtros o registra un nuevo asiento manual.</p>
                        </div>
                    )}

                    {/* ── PAGINACIÓN ── */}
                    {entries?.links && entries.links.length > 3 && (
                        <div className="p-4 bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 flex items-center justify-between gap-4">
                            <span className="text-xs text-gray-500 dark:text-gray-400">
                                Página {entries.current_page} de {entries.last_page}
                            </span>
                            <div className="flex items-center gap-1">
                                {entries.links.map((link, idx) => {
                                    if (!link.url) {
                                        return (
                                            <span
                                                key={idx}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                className="px-3 py-1.5 text-xs text-gray-300 dark:text-gray-600 rounded-lg cursor-not-allowed"
                                            />
                                        );
                                    }
                                    return (
                                        <Link
                                            key={idx}
                                            href={link.url}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                            className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition ${
                                                link.active
                                                    ? 'bg-brand-primary text-white shadow-xs'
                                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                                            }`}
                                            preserveScroll
                                        />
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
