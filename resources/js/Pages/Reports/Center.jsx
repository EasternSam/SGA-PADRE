import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Center({
    reportType = 'payments',
    filters = {},
    courses = [],
    modules = [],
    schedules = [],
    reportData = [],
    summary = {},
}) {
    const [localFilters, setLocalFilters] = useState({
        report_type: reportType,
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        course_id: filters.course_id || '',
        module_id: filters.module_id || '',
        schedule_id: filters.schedule_id || '',
        status: filters.status || 'all',
    });

    const reportTabs = [
        { key: 'payments', label: 'Cobros & Recaudación', icon: 'fa-solid fa-file-invoice-dollar' },
        { key: 'attendance', label: 'Control de Asistencia', icon: 'fa-solid fa-calendar-check' },
        { key: 'grades', label: 'Calificaciones & Notas', icon: 'fa-solid fa-chart-line' },
        { key: 'students', label: 'Padrón Estudiantil', icon: 'fa-solid fa-user-graduate' },
        { key: 'debtors', label: 'Morosos & Cobranzas', icon: 'fa-solid fa-triangle-exclamation' },
    ];

    const handleTabChange = (key) => {
        const next = { ...localFilters, report_type: key };
        setLocalFilters(next);
        router.get('/admin/reports', next, { preserveState: true, preserveScroll: true });
    };

    const handleFilterChange = (key, value) => {
        const next = { ...localFilters, [key]: value };
        setLocalFilters(next);
    };

    const applyFilters = (e) => {
        e?.preventDefault();
        router.get('/admin/reports', localFilters, { preserveState: true, preserveScroll: true });
    };

    const handleExportPdf = () => {
        const params = new URLSearchParams(localFilters).toString();
        window.open(`/admin/reports/export-pdf?${params}`, '_blank');
    };

    const handleExportCsv = () => {
        const params = new URLSearchParams(localFilters).toString();
        window.location.href = `/admin/reports/export-csv?${params}`;
    };

    return (
        <AuthenticatedLayout title="Centro de Reportes">
            <Head title="Centro de Reportes - SGA" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-semibold text-sga-primary dark:text-blue-400 uppercase tracking-wider">
                            <i className="fa-solid fa-chart-pie"></i> Inteligencia & Analítica
                        </div>
                        <h1 className="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                            Centro de Reportes Institucionales
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={handleExportPdf}
                            className="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold text-sm shadow-lg shadow-rose-600/25 transition-all flex items-center gap-2"
                        >
                            <i className="fa-solid fa-file-pdf"></i>
                            <span>Exportar PDF Oficial</span>
                        </button>
                        <button
                            type="button"
                            onClick={handleExportCsv}
                            className="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2"
                        >
                            <i className="fa-solid fa-file-excel"></i>
                            <span>Exportar CSV</span>
                        </button>
                    </div>
                </div>

                {/* Report Tabs */}
                <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                    {reportTabs.map(t => (
                        <button
                            key={t.key}
                            onClick={() => handleTabChange(t.key)}
                            className={`flex items-center gap-2.5 px-5 py-3 rounded-2xl font-bold text-sm whitespace-nowrap transition-all border ${
                                reportType === t.key
                                    ? 'bg-sga-primary border-blue-600 text-white shadow-md'
                                    : 'bg-white dark:bg-[#161622] border-slate-200 dark:border-white/5 text-slate-600 dark:text-slate-400 hover:text-white hover:bg-slate-50 dark:hover:bg-white/5'
                            }`}
                        >
                            <i className={t.icon}></i>
                            <span>{t.label}</span>
                        </button>
                    ))}
                </div>

                {/* Filter Control Bar */}
                <form onSubmit={applyFilters} className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-slate-200 dark:border-white/5 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">
                            Fecha Inicial
                        </label>
                        <input
                            type="date"
                            value={localFilters.date_from}
                            onChange={e => handleFilterChange('date_from', e.target.value)}
                            className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">
                            Fecha Final
                        </label>
                        <input
                            type="date"
                            value={localFilters.date_to}
                            onChange={e => handleFilterChange('date_to', e.target.value)}
                            className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">
                            Estado
                        </label>
                        <select
                            value={localFilters.status}
                            onChange={e => handleFilterChange('status', e.target.value)}
                            className="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                        >
                            <option value="all">Todos los Estados</option>
                            <option value="Pagado">Pagado / Completado</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Vencido">Vencido</option>
                        </select>
                    </div>

                    <div>
                        <button
                            type="submit"
                            className="w-full py-2.5 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2"
                        >
                            <i className="fa-solid fa-filter"></i>
                            <span>Filtrar Reporte</span>
                        </button>
                    </div>
                </form>

                {/* Summary Stat Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm">
                        <div className="text-xs uppercase font-bold text-slate-400">Total de Registros</div>
                        <div className="text-3xl font-black text-slate-800 dark:text-white mt-1">
                            {summary.total_records || 0}
                        </div>
                        <div className="text-xs text-slate-500 mt-2">Muestra procesada en base de datos</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm">
                        <div className="text-xs uppercase font-bold text-emerald-600 dark:text-emerald-400">
                            {summary.primary_label || 'Métrica Principal'}
                        </div>
                        <div className="text-3xl font-black text-slate-800 dark:text-white mt-1">
                            {summary.primary_metric || '0'}
                        </div>
                        <div className="text-xs text-slate-500 mt-2">Indicador clave consolidado</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-sm">
                        <div className="text-xs uppercase font-bold text-blue-600 dark:text-blue-400">
                            {summary.secondary_label || 'Métrica Secundaria'}
                        </div>
                        <div className="text-3xl font-black text-slate-800 dark:text-white mt-1">
                            {summary.secondary_metric || '0'}
                        </div>
                        <div className="text-xs text-slate-500 mt-2">Seguimiento de cumplimiento</div>
                    </div>
                </div>

                {/* Data Grid */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 dark:bg-white/[0.02] border-b border-slate-200 dark:border-white/5 text-xs uppercase font-bold text-slate-500 tracking-wider">
                                {reportType === 'payments' && (
                                    <tr>
                                        <th className="px-6 py-4">Fecha</th>
                                        <th className="px-6 py-4">Matrícula</th>
                                        <th className="px-6 py-4">Estudiante</th>
                                        <th className="px-6 py-4">Concepto</th>
                                        <th className="px-6 py-4">Método</th>
                                        <th className="px-6 py-4">NCF</th>
                                        <th className="px-6 py-4 text-right">Monto</th>
                                        <th className="px-6 py-4 text-center">Estado</th>
                                    </tr>
                                )}
                                {reportType === 'attendance' && (
                                    <tr>
                                        <th className="px-6 py-4">Fecha</th>
                                        <th className="px-6 py-4">Matrícula</th>
                                        <th className="px-6 py-4">Estudiante</th>
                                        <th className="px-6 py-4">Sección</th>
                                        <th className="px-6 py-4 text-center">Asistencia</th>
                                        <th className="px-6 py-4">Observación</th>
                                    </tr>
                                )}
                                {reportType === 'grades' && (
                                    <tr>
                                        <th className="px-6 py-4">Matrícula</th>
                                        <th className="px-6 py-4">Estudiante</th>
                                        <th className="px-6 py-4">Asignatura</th>
                                        <th className="px-6 py-4">Período</th>
                                        <th className="px-6 py-4 text-right">Calificación</th>
                                        <th className="px-6 py-4 text-center">Condición</th>
                                    </tr>
                                )}
                                {reportType === 'students' && (
                                    <tr>
                                        <th className="px-6 py-4">Matrícula</th>
                                        <th className="px-6 py-4">Estudiante</th>
                                        <th className="px-6 py-4">Email</th>
                                        <th className="px-6 py-4">Teléfono</th>
                                        <th className="px-6 py-4">Carrera</th>
                                        <th className="px-6 py-4 text-center">Estado</th>
                                    </tr>
                                )}
                                {reportType === 'debtors' && (
                                    <tr>
                                        <th className="px-6 py-4">Matrícula</th>
                                        <th className="px-6 py-4">Estudiante</th>
                                        <th className="px-6 py-4">Carrera</th>
                                        <th className="px-6 py-4">Concepto Vencido</th>
                                        <th className="px-6 py-4">Contacto</th>
                                        <th className="px-6 py-4">Vencimiento</th>
                                        <th className="px-6 py-4 text-right">Balance Pendiente</th>
                                    </tr>
                                )}
                            </thead>

                            <tbody className="divide-y divide-slate-100 dark:divide-white/5 font-medium">
                                {reportData.length === 0 ? (
                                    <tr>
                                        <td colSpan="8" className="px-6 py-12 text-center text-slate-400">
                                            <i className="fa-solid fa-folder-open text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                            <div>No se encontraron registros para los filtros seleccionados.</div>
                                        </td>
                                    </tr>
                                ) : (
                                    reportData.map((row, idx) => (
                                        <tr key={idx} className="hover:bg-slate-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                            {reportType === 'payments' && (
                                                <>
                                                    <td className="px-6 py-4 text-xs text-slate-500 font-mono">{row.date}</td>
                                                    <td className="px-6 py-4 font-mono text-xs text-blue-600 dark:text-blue-400">{row.student_code}</td>
                                                    <td className="px-6 py-4 font-bold text-slate-800 dark:text-white">{row.student_name}</td>
                                                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{row.concept}</td>
                                                    <td className="px-6 py-4 text-xs text-slate-500">{row.gateway}</td>
                                                    <td className="px-6 py-4 text-xs font-mono text-slate-500">{row.ncf}</td>
                                                    <td className="px-6 py-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                        RD$ {row.amount.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold border ${
                                                            row.status === 'Pagado'
                                                                ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'
                                                                : 'bg-amber-500/10 text-amber-600 border-amber-500/20'
                                                        }`}>
                                                            {row.status}
                                                        </span>
                                                    </td>
                                                </>
                                            )}
                                            {reportType === 'attendance' && (
                                                <>
                                                    <td className="px-6 py-4 text-xs text-slate-500 font-mono">{row.date}</td>
                                                    <td className="px-6 py-4 font-mono text-xs text-blue-600 dark:text-blue-400">{row.student_code}</td>
                                                    <td className="px-6 py-4 font-bold text-slate-800 dark:text-white">{row.student_name}</td>
                                                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{row.section}</td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold capitalize ${
                                                            row.status === 'present' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600'
                                                        }`}>
                                                            {row.status}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-xs text-slate-400">{row.notes}</td>
                                                </>
                                            )}
                                            {reportType === 'grades' && (
                                                <>
                                                    <td className="px-6 py-4 font-mono text-xs text-blue-600 dark:text-blue-400">{row.student_code}</td>
                                                    <td className="px-6 py-4 font-bold text-slate-800 dark:text-white">{row.student_name}</td>
                                                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{row.subject}</td>
                                                    <td className="px-6 py-4 text-xs text-slate-500">{row.period}</td>
                                                    <td className="px-6 py-4 text-right font-mono font-bold text-slate-800 dark:text-white">{row.score} pts</td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold ${
                                                            row.score >= 70 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600'
                                                        }`}>
                                                            {row.status}
                                                        </span>
                                                    </td>
                                                </>
                                            )}
                                            {reportType === 'students' && (
                                                <>
                                                    <td className="px-6 py-4 font-mono text-xs text-blue-600 dark:text-blue-400">{row.student_code}</td>
                                                    <td className="px-6 py-4 font-bold text-slate-800 dark:text-white">{row.student_name}</td>
                                                    <td className="px-6 py-4 text-xs text-slate-500">{row.email}</td>
                                                    <td className="px-6 py-4 text-xs text-slate-500">{row.phone}</td>
                                                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{row.career}</td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                                            {row.status}
                                                        </span>
                                                    </td>
                                                </>
                                            )}
                                            {reportType === 'debtors' && (
                                                <>
                                                    <td className="px-6 py-4 font-mono text-xs text-blue-600 dark:text-blue-400">{row.student_code}</td>
                                                    <td className="px-6 py-4 font-bold text-slate-800 dark:text-white">{row.student_name}</td>
                                                    <td className="px-6 py-4 text-xs text-slate-500">{row.career}</td>
                                                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{row.concept}</td>
                                                    <td className="px-6 py-4 text-xs text-slate-500">{row.phone}</td>
                                                    <td className="px-6 py-4 text-xs text-rose-500 font-semibold">{row.due_date}</td>
                                                    <td className="px-6 py-4 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                                                        RD$ {row.amount.toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                    </td>
                                                </>
                                            )}
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
