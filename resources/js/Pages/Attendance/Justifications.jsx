import { Paperclip, Check, X } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function JustificationsIndex({
    justifications,
    filters = {},
    stats = {},
    reasons = {},
    statuses = {},
}) {
    const [modalOpen, setModalOpen] = useState(false);
    const [studentSearch, setStudentSearch] = useState('');
    const [searchedStudents, setSearchedStudents] = useState([]);
    const [selectedStudent, setSelectedStudent] = useState(null);
    const [searchingStudent, setSearchingStudent] = useState(false);

    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [searchQuery, setSearchQuery] = useState(filters.search || '');

    const form = useForm({
        student_id: '',
        date_from: new Date().toISOString().split('T')[0],
        date_to: new Date().toISOString().split('T')[0],
        reason: 'medical',
        description: '',
        document: null,
    });

    const handleFilterChange = (newStatus, newSearch) => {
        setStatusFilter(newStatus);
        router.get('/admin/attendance/justifications', {
            status: newStatus,
            search: newSearch,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Live student search for justification creation
    const handleStudentSearch = async (val) => {
        setStudentSearch(val);
        if (val.length < 2) {
            setSearchedStudents([]);
            return;
        }

        setSearchingStudent(true);
        try {
            const res = await fetch(`/admin/requests/search-students?q=${encodeURIComponent(val)}`);
            const data = await res.json();
            setSearchedStudents(data);
        } catch (err) {
            console.error(err);
        } finally {
            setSearchingStudent(false);
        }
    };

    const handleSelectStudent = (st) => {
        setSelectedStudent(st);
        form.setData('student_id', st.id);
        setSearchedStudents([]);
        setStudentSearch(`${st.name} (${st.code || st.cedula})`);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        form.post('/admin/attendance/justifications', {
            onSuccess: () => {
                setModalOpen(false);
                form.reset();
                setSelectedStudent(null);
                setStudentSearch('');
            },
        });
    };

    const handleApprove = (justification) => {
        if (confirm(`¿Aprobar justificación para ${justification.student?.full_name}? Se actualizarán automáticamente las ausencias a 'Excusadas'.`)) {
            router.post(`/admin/attendance/justifications/${justification.id}/approve`, {}, {
                preserveScroll: true,
            });
        }
    };

    const handleReject = (justification) => {
        if (confirm(`¿Rechazar justificación de inasistencia?`)) {
            router.post(`/admin/attendance/justifications/${justification.id}/reject`, {}, {
                preserveScroll: true,
            });
        }
    };

    const handleDelete = (justification) => {
        if (confirm(`¿Eliminar este registro de justificación?`)) {
            router.delete(`/admin/attendance/justifications/${justification.id}`, {
                preserveScroll: true,
            });
        }
    };

    const statusPills = [
        { key: 'all', label: 'Todas', count: stats.total || 0 },
        { key: 'pending', label: 'Pendientes', count: stats.pending || 0 },
        { key: 'approved', label: 'Aprobadas', count: stats.approved || 0 },
        { key: 'rejected', label: 'Rechazadas', count: stats.rejected || 0 },
    ];

    return (
        <AuthenticatedLayout>
            <Head title="Excusas & Justificaciones de Asistencia - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            Gestión de Excusas & Justificaciones
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-3">
                        <Link
                            href="/admin/attendance/register"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                        >
                            Pase de Lista
                        </Link>
                        <button
                            type="button"
                            onClick={() => setModalOpen(true)}
                            className="inline-flex items-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            + Registrar Justificación
                        </button>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Recibidas</span>
                            <span className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Excusas registradas</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Por Revisar</span>
                            <span className="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {stats.pending || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Pendientes de validar</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Aprobadas</span>
                            <span className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {stats.approved || 0}
                        </div>
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1">Ausencias justificadas</div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Rechazadas</span>
                            <span className="p-2 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                            {stats.rejected || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">Sin soporte válido</div>
                    </div>
                </div>

                {/* Filter and Search Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div className="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        {statusPills.map(pill => (
                            <button
                                key={pill.key}
                                type="button"
                                onClick={() => handleFilterChange(pill.key, searchQuery)}
                                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition ${
                                    statusFilter === pill.key
                                        ? 'bg-brand-primary text-white shadow-sm'
                                        : 'bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
                                }`}
                            >
                                {pill.label} ({pill.count})
                            </button>
                        ))}
                    </div>

                    <div className="w-full md:w-80 relative">
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={e => setSearchQuery(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && handleFilterChange(statusFilter, searchQuery)}
                            placeholder="Buscar por estudiante o matrícula..."
                            className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs pl-8 pr-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                        />
                        <svg className="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {/* Table */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    {justifications.data.length === 0 ? (
                        <div className="p-12 text-center text-gray-500">
                            <p className="text-sm">No hay justificaciones registradas bajo los filtros seleccionados.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-gray-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-gray-800/30">
                                        <th className="py-3 px-4">Estudiante</th>
                                        <th className="py-3 px-4">Periodo de Ausencia</th>
                                        <th className="py-3 px-4">Motivo</th>
                                        <th className="py-3 px-4">Comprobante</th>
                                        <th className="py-3 px-4">Estado</th>
                                        <th className="py-3 px-4 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    {justifications.data.map(j => {
                                        const from = new Date(j.date_from).toLocaleDateString('es-DO');
                                        const to = new Date(j.date_to).toLocaleDateString('es-DO');
                                        const reasonLabel = reasons[j.reason] || j.reason;

                                        return (
                                            <tr key={j.id} className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition">
                                                <td className="py-3 px-4">
                                                    <div className="font-bold text-gray-900 dark:text-white">
                                                        {j.student?.full_name || 'Estudiante'}
                                                    </div>
                                                    <div className="text-[11px] text-gray-400 font-mono mt-0.5">
                                                        {j.student?.student_code || j.student?.cedula || '—'}
                                                    </div>
                                                </td>

                                                <td className="py-3 px-4">
                                                    <div className="font-medium text-gray-900 dark:text-white">
                                                        {from} al {to}
                                                    </div>
                                                    {j.description && (
                                                        <div className="text-[11px] text-gray-500 mt-0.5 max-w-xs truncate">
                                                            {j.description}
                                                        </div>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4">
                                                    <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                        {reasonLabel}
                                                    </span>
                                                </td>

                                                <td className="py-3 px-4">
                                                    {j.document_path ? (
                                                        <a
                                                            href={`/storage/${j.document_path}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-xs font-semibold text-brand-primary dark:text-blue-400 hover:underline flex items-center gap-1"
                                                        >
                                                            <Paperclip className="w-3.5 h-3.5 mr-1 inline" /> Ver Adjunto
                                                        </a>
                                                    ) : (
                                                        <span className="text-gray-400 italic text-[11px]">Sin adjunto</span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4">
                                                    {j.status === 'approved' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Aprobada
                                                        </span>
                                                    ) : j.status === 'rejected' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                            Rechazada
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                            Pendiente
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4 text-right">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {j.status === 'pending' && (
                                                            <>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleApprove(j)}
                                                                    className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300 transition"
                                                                >
                                                                    <Check className="w-3.5 h-3.5 mr-1 inline" /> Aprobar
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleReject(j)}
                                                                    className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/30 dark:text-red-400 transition"
                                                                >
                                                                    <X className="w-3.5 h-3.5 mr-1 inline" /> Rechazar
                                                                </button>
                                                            </>
                                                        )}
                                                        <button
                                                            type="button"
                                                            onClick={() => handleDelete(j)}
                                                            className="p-1 text-gray-400 hover:text-red-600"
                                                            title="Eliminar"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal: Registrar Justificación */}
            {modalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-lg rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                Registrar Justificación de Inasistencia
                            </h3>
                            <button
                                type="button"
                                onClick={() => setModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4 mt-4">
                            {/* Student Search */}
                            <div className="relative">
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Estudiante *
                                </label>
                                <input
                                    type="text"
                                    value={studentSearch}
                                    onChange={e => handleStudentSearch(e.target.value)}
                                    placeholder="Buscar por nombre, código o cédula..."
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                />
                                {searchedStudents.length > 0 && (
                                    <div className="absolute z-10 w-full mt-1 bg-white dark:bg-[#1c1c2b] border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        {searchedStudents.map(st => (
                                            <div
                                                key={st.id}
                                                onClick={() => handleSelectStudent(st)}
                                                className="p-2.5 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer text-xs border-b last:border-b-0 border-gray-100 dark:border-gray-700/60"
                                            >
                                                <div className="font-bold text-gray-900 dark:text-white">{st.name}</div>
                                                <div className="text-[11px] text-gray-400 font-mono">{st.code} • {st.email}</div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                                {form.errors.student_id && (
                                    <p className="text-xs text-red-500 mt-1">{form.errors.student_id}</p>
                                )}
                            </div>

                            {/* Dates */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Fecha Desde *
                                    </label>
                                    <input
                                        type="date"
                                        value={form.data.date_from}
                                        onChange={e => form.setData('date_from', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                    {form.errors.date_from && (
                                        <p className="text-xs text-red-500 mt-1">{form.errors.date_from}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Fecha Hasta *
                                    </label>
                                    <input
                                        type="date"
                                        value={form.data.date_to}
                                        onChange={e => form.setData('date_to', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                    {form.errors.date_to && (
                                        <p className="text-xs text-red-500 mt-1">{form.errors.date_to}</p>
                                    )}
                                </div>
                            </div>

                            {/* Reason */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Motivo *
                                </label>
                                <select
                                    value={form.data.reason}
                                    onChange={e => form.setData('reason', e.target.value)}
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    {Object.entries(reasons).map(([k, label]) => (
                                        <option key={k} value={k}>{label}</option>
                                    ))}
                                </select>
                            </div>

                            {/* Description */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Descripción / Diagnóstico
                                </label>
                                <textarea
                                    rows="2"
                                    value={form.data.description}
                                    onChange={e => form.setData('description', e.target.value)}
                                    placeholder="Detalles sobre el reposo médico o justificación..."
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                ></textarea>
                            </div>

                            {/* Document File */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Adjuntar Certificado o Comprobante (PDF/Imagen)
                                </label>
                                <input
                                    type="file"
                                    onChange={e => form.setData('document', e.target.files[0])}
                                    className="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-brand-primary hover:file:bg-blue-100"
                                />
                            </div>

                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {form.processing ? 'Guardando...' : 'Registrar Justificación'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
