import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Index({ logs = [], events = [], filters = {} }) {
    const [search, setSearch] = useState(filters.search || '');
    const [activeEvent, setActiveEvent] = useState(filters.event || 'all');
    const [selectedLog, setSelectedLog] = useState(null);

    const handleSearch = (e) => {
        e?.preventDefault();
        router.get('/admin/activity-logs', { search, event: activeEvent }, { preserveState: true, preserveScroll: true });
    };

    const handleEventFilter = (ev) => {
        setActiveEvent(ev);
        router.get('/admin/activity-logs', { search, event: ev }, { preserveState: true, preserveScroll: true });
    };

    const getEventBadge = (event) => {
        switch (event) {
            case 'created':
                return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
            case 'updated':
                return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20';
            case 'deleted':
                return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20';
            case 'login':
                return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20';
            default:
                return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20';
        }
    };

    return (
        <AuthenticatedLayout title="Registro de Auditoría">
            <Head title="Auditoría del Sistema - SGA" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-sga-primary dark:text-blue-400 uppercase tracking-wider">
                            <i className="fa-solid fa-shield-halved"></i> Trazabilidad & Cumplimiento
                        </div>
                        <h1 className="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                            Pista de Auditoría & Actividades
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-white/5 px-4 py-2 rounded-xl border border-slate-200 dark:border-white/5">
                        <i className="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                        <span>Inmutable & Firmado Digitalmente</span>
                    </div>
                </div>

                {/* Filter & Search Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <form onSubmit={handleSearch} className="relative w-full md:w-80">
                        <i className="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Buscar en descripción o entidad..."
                            className="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                        />
                    </form>

                    <div className="flex gap-1.5 overflow-x-auto w-full md:w-auto pb-1 scrollbar-none">
                        <button
                            onClick={() => handleEventFilter('all')}
                            className={`px-3 py-1.5 rounded-lg text-xs font-bold border transition-all ${
                                activeEvent === 'all'
                                    ? 'bg-sga-primary text-white border-blue-600 shadow-sm'
                                    : 'bg-slate-50 dark:bg-white/5 border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-400 hover:text-white'
                            }`}
                        >
                            Todos
                        </button>
                        {events.map(ev => (
                            <button
                                key={ev}
                                onClick={() => handleEventFilter(ev)}
                                className={`px-3 py-1.5 rounded-lg text-xs font-bold border capitalize transition-all ${
                                    activeEvent === ev
                                        ? 'bg-sga-primary text-white border-blue-600 shadow-sm'
                                        : 'bg-slate-50 dark:bg-white/5 border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-400 hover:text-white'
                                }`}
                            >
                                {ev}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Audit Logs Table */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 dark:bg-white/[0.02] border-b border-slate-200 dark:border-white/5 text-xs uppercase font-bold text-slate-500 tracking-wider">
                                <tr>
                                    <th className="px-6 py-4">Fecha & Hora</th>
                                    <th className="px-6 py-4">Evento</th>
                                    <th className="px-6 py-4">Descripción</th>
                                    <th className="px-6 py-4">Entidad Afectada</th>
                                    <th className="px-6 py-4">Autor / Usuario</th>
                                    <th className="px-6 py-4 text-right">Detalles</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-white/5 font-medium">
                                {logs.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-slate-400">
                                            No se registran eventos de auditoría para los criterios seleccionados.
                                        </td>
                                    </tr>
                                ) : (
                                    logs.map(log => (
                                        <tr key={log.id} className="hover:bg-slate-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                            <td className="px-6 py-4 text-xs font-mono text-slate-500">{log.created_at}</td>
                                            <td className="px-6 py-4">
                                                <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize ${getEventBadge(log.event)}`}>
                                                    {log.event}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 font-semibold text-slate-800 dark:text-white max-w-xs truncate">
                                                {log.description}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="text-xs font-mono text-blue-600 dark:text-blue-400 bg-blue-500/5 px-2 py-1 rounded">
                                                    {log.subject_type} #{log.subject_id || '-'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div>
                                                    <div className="font-bold text-xs text-slate-800 dark:text-white">{log.causer_name}</div>
                                                    <div className="text-[11px] text-slate-400 font-mono">{log.causer_email}</div>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <button
                                                    onClick={() => setSelectedLog(log)}
                                                    className="px-3 py-1 rounded-lg bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-xs font-semibold text-slate-600 dark:text-slate-300 transition-all inline-flex items-center gap-1.5"
                                                >
                                                    <i className="fa-solid fa-code"></i>
                                                    <span>Payload</span>
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Payload Modal */}
                {selectedLog && (
                    createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                        <div className="bg-white dark:bg-[#161622] border border-slate-200 dark:border-white/10 rounded-2xl max-w-xl w-full p-6 shadow-2xl">
                            <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/5">
                                <div>
                                    <h4 className="text-lg font-bold text-slate-800 dark:text-white">Detalle de Registro #{selectedLog.id}</h4>
                                    <div className="text-xs text-slate-500 mt-0.5">{selectedLog.description}</div>
                                </div>
                                <button
                                    onClick={() => setSelectedLog(null)}
                                    className="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-white flex items-center justify-center"
                                >
                                    &times;
                                </button>
                            </div>

                            <div className="mt-4 space-y-3">
                                <div className="text-xs text-slate-500 font-mono">
                                    <strong>Autor:</strong> {selectedLog.causer_name} ({selectedLog.causer_email})
                                </div>
                                <div className="text-xs text-slate-500 font-mono">
                                    <strong>Fecha:</strong> {selectedLog.created_at}
                                </div>

                                <div className="mt-4">
                                    <div className="text-xs uppercase font-bold text-slate-600 dark:text-slate-400 mb-2">
                                        Datos Modificados (JSON Properties)
                                    </div>
                                    <pre className="p-4 rounded-xl bg-slate-900 text-emerald-400 font-mono text-xs overflow-x-auto max-h-60 border border-slate-800">
                                        {selectedLog.properties ? JSON.stringify(selectedLog.properties, null, 2) : '// Sin propiedades adicionales'}
                                    </pre>
                                </div>
                            </div>

                            <div className="mt-6 flex justify-end">
                                <button
                                    onClick={() => setSelectedLog(null)}
                                    className="px-5 py-2 rounded-xl bg-sga-primary text-white font-semibold text-sm shadow-md"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>, document.body)
                )}
            </div>
        </AuthenticatedLayout>
    );
}
