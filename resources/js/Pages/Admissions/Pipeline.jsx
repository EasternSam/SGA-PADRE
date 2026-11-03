import { X, FileText, Check, CheckCircle } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AdmissionsPipeline({
    admissions,
    courses = [],
    admissionsOpen = false,
    filters = {},
    stats = {},
}) {
    const [reviewModalOpen, setReviewModalOpen] = useState(false);
    const [selectedAdmission, setSelectedAdmission] = useState(null);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [togglingMode, setTogglingMode] = useState(false);

    const handleToggleAdmissionsMode = () => {
        setTogglingMode(true);
        router.post('/admin/admissions/toggle-mode', {
            open: !admissionsOpen,
        }, {
            preserveScroll: true,
            onFinish: () => setTogglingMode(false),
        });
    };

    // Document review local state
    const [tempDocStatus, setTempDocStatus] = useState({});
    const [reviewNotes, setReviewNotes] = useState('');
    const [selectedCourseId, setSelectedCourseId] = useState('');

    const openReviewModal = (admission) => {
        setSelectedAdmission(admission);
        setReviewNotes(admission.notes || '');
        setSelectedCourseId(admission.course_id ? String(admission.course_id) : '');

        const initialDocStatus = { ...(admission.document_status || {}) };
        const docs = admission.documents || {};
        Object.keys(docs).forEach(key => {
            if (!initialDocStatus[key]) {
                initialDocStatus[key] = 'pending';
            }
        });

        setTempDocStatus(initialDocStatus);
        setReviewModalOpen(true);
    };

    const handleFilterChange = (newStatus, newSearch) => {
        setStatusFilter(newStatus);
        router.get('/admin/admissions', {
            status: newStatus,
            search: newSearch,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSetDocStatus = (key, status) => {
        setTempDocStatus(prev => ({
            ...prev,
            [key]: status,
        }));
    };

    const handleApprove = () => {
        if (!selectedAdmission) return;
        if (confirm(`¿Confirmas la admisión oficial de ${selectedAdmission.first_name} ${selectedAdmission.last_name}? Se formalizará su cuenta de estudiante Activo y quedará inscrito en el curso o carrera.`)) {
            router.post(`/admin/admissions/${selectedAdmission.id}/approve`, {
                course_id: selectedCourseId || selectedAdmission.course_id,
                document_status: tempDocStatus,
                notes: reviewNotes,
            }, {
                onSuccess: () => {
                    setReviewModalOpen(false);
                },
            });
        }
    };

    const handleReject = () => {
        if (!selectedAdmission) return;
        if (!reviewNotes.trim()) {
            alert('Por favor especifica las observaciones o motivos del rechazo antes de continuar.');
            return;
        }
        if (confirm('¿Deseas enviar las observaciones y requerir correcciones al aspirante?')) {
            router.post(`/admin/admissions/${selectedAdmission.id}/reject`, {
                notes: reviewNotes,
            }, {
                onSuccess: () => {
                    setReviewModalOpen(false);
                },
            });
        }
    };

    const handleSaveReview = () => {
        if (!selectedAdmission) return;
        router.post(`/admin/admissions/${selectedAdmission.id}/review`, {
            document_status: tempDocStatus,
            notes: reviewNotes,
        }, {
            onSuccess: () => {
                setReviewModalOpen(false);
            },
        });
    };

    const statusPills = [
        { key: 'all', label: 'Todos', count: stats.total || 0 },
        { key: 'pending', label: 'Pendientes', count: stats.pending || 0 },
        { key: 'approved', label: 'Admitidos', count: stats.approved || 0 },
        { key: 'rejected', label: 'Rechazados / Observados', count: stats.rejected || 0 },
    ];

    return (
        <AuthenticatedLayout title="Admisiones y Nuevo Ingreso">
            <Head title="Admisiones & Nuevo Ingreso - SGA" />

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
                            Admisiones & Nuevo Ingreso
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            onClick={handleToggleAdmissionsMode}
                            disabled={togglingMode}
                            title="Habilita o deshabilita la vista de Selección de Materias en el Portal del Estudiante"
                            className={`inline-flex items-center gap-2.5 rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition active:scale-[0.98] disabled:opacity-60 ${
                                admissionsOpen
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-[#161622] dark:text-gray-200 dark:hover:bg-gray-800'
                            }`}
                        >
                            <span
                                className={`h-2.5 w-2.5 rounded-full transition ${
                                    admissionsOpen
                                        ? 'bg-emerald-500 ring-4 ring-emerald-500/20'
                                        : 'bg-gray-400 dark:bg-gray-500'
                                }`}
                            />
                            <span>
                                {admissionsOpen ? 'Admisiones Abiertas: Activo' : 'Admisiones Abiertas: Cerrado'}
                            </span>
                        </button>

                        <Link
                            href="/admin/students"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4 text-brand-primary dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Directorio de Estudiantes
                        </Link>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Solicitudes</span>
                            <span className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Aspirantes registrados
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Por Evaluar</span>
                            <span className="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">
                            {stats.pending || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Pendientes de dictamen
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Admitidos</span>
                            <span className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            {stats.approved || 0}
                        </div>
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                            Formalizados en el sistema
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Con Observaciones</span>
                            <span className="p-2 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                            {stats.rejected || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Rechazados o por subsanar
                        </div>
                    </div>
                </div>

                {/* Filter and Search Bar */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div className="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        {statusPills.map(pill => (
                            <button
                                key={pill.key}
                                type="button"
                                onClick={() => handleFilterChange(pill.key, searchTerm)}
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
                            value={searchTerm}
                            onChange={e => setSearchTerm(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && handleFilterChange(statusFilter, searchTerm)}
                            placeholder="Buscar por nombre, cédula o correo..."
                            className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs pl-8 pr-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                        />
                        <svg className="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {/* Admissions List */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    {admissions.data.length === 0 ? (
                        <div className="p-12 text-center text-gray-500">
                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 className="mt-3 text-base font-semibold text-gray-900 dark:text-white">No hay solicitudes de admisión</h3>
                            <p className="mt-1 text-sm text-gray-500">No se encontraron aspirantes bajo los filtros seleccionados.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-gray-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-gray-800/30">
                                        <th className="py-3 px-4">Aspirante</th>
                                        <th className="py-3 px-4">Cédula / Documento</th>
                                        <th className="py-3 px-4">Carrera / Curso Solicitado</th>
                                        <th className="py-3 px-4">Documentación</th>
                                        <th className="py-3 px-4">Estado</th>
                                        <th className="py-3 px-4">Fecha</th>
                                        <th className="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    {admissions.data.map(item => {
                                        const docCount = Object.keys(item.documents || {}).length;
                                        const approvedDocs = Object.values(item.document_status || {}).filter(st => st === 'approved').length;

                                        return (
                                            <tr key={item.id} className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition">
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="h-9 w-9 rounded-full bg-brand-primary/10 text-brand-primary dark:bg-blue-900/30 dark:text-blue-300 font-bold flex items-center justify-center shrink-0">
                                                            {item.first_name[0]}{item.last_name[0]}
                                                        </div>
                                                        <div>
                                                            <div className="font-bold text-gray-900 dark:text-white">
                                                                {item.first_name} {item.last_name}
                                                            </div>
                                                            <div className="text-[11px] text-gray-400 mt-0.5">
                                                                {item.email} {item.phone ? `• ${item.phone}` : ''}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="py-3 px-4 font-mono font-medium text-gray-700 dark:text-gray-300">
                                                    {item.identification_id || '—'}
                                                </td>

                                                <td className="py-3 px-4">
                                                    <span className="font-semibold text-gray-900 dark:text-white">
                                                        {item.course?.name || 'Carrera General'}
                                                    </span>
                                                    {item.previous_school && (
                                                        <div className="text-[10px] text-gray-400 mt-0.5">
                                                            Procedencia: {item.previous_school} {item.previous_gpa ? `(${item.previous_gpa})` : ''}
                                                        </div>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4">
                                                    {docCount === 0 ? (
                                                        <span className="text-gray-400 italic text-[11px]">Sin adjuntos</span>
                                                    ) : (
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold ${
                                                            approvedDocs === docCount
                                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                                                : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'
                                                        }`}>
                                                            {approvedDocs} de {docCount} aprobados
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4">
                                                    {item.status === 'approved' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Admitido
                                                        </span>
                                                    ) : item.status === 'rejected' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                            Con Observaciones
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                            Pendiente
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4 text-gray-500">
                                                    {new Date(item.created_at).toLocaleDateString('es-DO')}
                                                </td>

                                                <td className="py-3 px-4 text-right">
                                                    <div className="inline-flex items-center gap-2 justify-end">
                                                        {item.status === 'approved' && item.user?.student?.id && (
                                                            <Link
                                                                href={`/admin/students/profile/${item.user.student.id}`}
                                                                className="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 font-semibold text-xs transition"
                                                                title={`Matrícula: ${item.user.student.student_code || 'Activo'}`}
                                                            >
                                                                Ver Expediente ({item.user.student.student_code || 'Activo'})
                                                            </Link>
                                                        )}
                                                        <button
                                                            type="button"
                                                            onClick={() => openReviewModal(item)}
                                                            className="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/30 text-brand-primary dark:text-blue-300 font-semibold text-xs transition"
                                                        >
                                                            Examinar
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

                    {/* Pagination */}
                    {admissions.links && admissions.links.length > 3 && (
                        <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <span className="text-xs text-gray-500">
                                Mostrando {admissions.from || 0} a {admissions.to || 0} de {admissions.total} solicitudes
                            </span>
                            <div className="flex gap-1">
                                {admissions.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        preserveScroll
                                        className={`px-3 py-1 rounded-lg text-xs font-semibold ${
                                            link.active
                                                ? 'bg-brand-primary text-white'
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                        } ${!link.url ? 'opacity-40 cursor-not-allowed' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal: Expediente de Admisión */}
            {reviewModalOpen && selectedAdmission && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 space-y-6">
                        {/* Header */}
                        <div className="flex items-start justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    Expediente de Admisión: {selectedAdmission.first_name} {selectedAdmission.last_name}
                                </h3>
                                <p className="text-xs text-gray-500 mt-0.5">
                                    Solicitud para: <strong>{selectedAdmission.course?.name || 'Carrera / Grado'}</strong> • Cédula: {selectedAdmission.identification_id || 'No registrada'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setReviewModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        {/* Informacion Personal y Academica */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl text-xs">
                            <div className="space-y-1.5">
                                <div><span className="text-gray-400">Correo Electrónico:</span> <strong>{selectedAdmission.email}</strong></div>
                                <div><span className="text-gray-400">Teléfono:</span> <strong>{selectedAdmission.phone || 'No especificado'}</strong></div>
                                <div><span className="text-gray-400">Fecha de Nacimiento:</span> <strong>{selectedAdmission.birth_date ? new Date(selectedAdmission.birth_date).toLocaleDateString('es-DO') : 'No especificada'}</strong></div>
                                <div><span className="text-gray-400">Dirección:</span> <strong>{selectedAdmission.address || 'No especificada'}</strong></div>
                            </div>
                            <div className="space-y-1.5">
                                <div><span className="text-gray-400">Colegio / Institución de Origen:</span> <strong>{selectedAdmission.previous_school || 'No indicado'}</strong></div>
                                <div><span className="text-gray-400">Promedio / GPA:</span> <strong>{selectedAdmission.previous_gpa || 'N/A'}</strong></div>
                                <div><span className="text-gray-400">Lugar de Trabajo:</span> <strong>{selectedAdmission.work_place || 'No indicado'}</strong></div>
                                <div><span className="text-gray-400">Condición Médica / Salud:</span> <strong>{selectedAdmission.disease || 'Ninguna'}</strong></div>
                            </div>
                            <div className="sm:col-span-2 pt-2 border-t border-gray-200/70 dark:border-gray-800">
                                <label className="block text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                    Curso o Carrera de Inscripción:
                                </label>
                                <select
                                    value={selectedCourseId}
                                    onChange={e => setSelectedCourseId(e.target.value)}
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] text-xs px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">— Seleccionar Curso / Carrera —</option>
                                    {courses.map(c => (
                                        <option key={c.id} value={c.id}>
                                            {c.name} ({c.code}) — {c.program_type === 'degree' ? 'Carrera Universitaria' : 'Curso Técnico'}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {/* Documentos Adjuntos Checklist */}
                        <div>
                            <h4 className="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2">
                                <svg className="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                Documentación Presentada para Verificación
                            </h4>

                            {!selectedAdmission.documents || Object.keys(selectedAdmission.documents).length === 0 ? (
                                <div className="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-400 text-center">
                                    No se adjuntaron documentos digitales en esta solicitud.
                                </div>
                            ) : (
                                <div className="space-y-2.5">
                                    {Object.entries(selectedAdmission.documents).map(([key, path]) => {
                                        const docStatus = tempDocStatus[key] || 'pending';
                                        const label = getDocumentLabel(key);

                                        return (
                                            <div
                                                key={key}
                                                className="p-3.5 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div className="p-2 rounded-lg bg-white dark:bg-gray-700 shadow-xs text-gray-500">
                                                        <FileText className="w-4 h-4" />
                                                    </div>
                                                    <div>
                                                        <div className="font-bold text-xs text-gray-900 dark:text-white">
                                                            {label}
                                                        </div>
                                                        <a
                                                            href={`/admissions/document/${selectedAdmission.id}/${key}`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-[11px] font-semibold text-brand-primary dark:text-blue-400 hover:underline mt-0.5 inline-block"
                                                        >
                                                            Abrir / Descargar Documento ↗
                                                        </a>
                                                    </div>
                                                </div>

                                                {/* Verification Buttons */}
                                                <div className="flex items-center gap-1.5 self-end sm:self-center">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleSetDocStatus(key, 'approved')}
                                                        className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                                                            docStatus === 'approved'
                                                                ? 'bg-emerald-600 text-white shadow-xs'
                                                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-emerald-50'
                                                        }`}
                                                    >
                                                        <Check className="w-3.5 h-3.5 mr-1 inline" /> Válido
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleSetDocStatus(key, 'rejected')}
                                                        className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                                                            docStatus === 'rejected'
                                                                ? 'bg-red-600 text-white shadow-xs'
                                                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-red-50'
                                                        }`}
                                                    >
                                                        <X className="w-3.5 h-3.5 mr-1 inline" /> Observado
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleSetDocStatus(key, 'pending')}
                                                        className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition ${
                                                            docStatus === 'pending'
                                                                ? 'bg-amber-500 text-white shadow-xs'
                                                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600'
                                                        }`}
                                                    >
                                                        Pendiente
                                                    </button>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </div>

                        {/* Admin Notes */}
                        <div>
                            <label className="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                                Observaciones de Registro y Control Académico:
                            </label>
                            <textarea
                                rows="3"
                                value={reviewNotes}
                                onChange={e => setReviewNotes(e.target.value)}
                                placeholder="Escribe observaciones visibles para el dictamen o requerimientos de documentos a subsanar..."
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                            ></textarea>
                        </div>

                        {/* Modal Action Buttons */}
                        <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <button
                                type="button"
                                onClick={handleReject}
                                className="w-full sm:w-auto rounded-xl px-4 py-2 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 transition"
                            >
                                Rechazar / Notificar Correcciones
                            </button>

                            <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
                                <button
                                    type="button"
                                    onClick={handleSaveReview}
                                    className="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 transition"
                                >
                                    Guardar Revisión
                                </button>
                                <button
                                    type="button"
                                    onClick={handleApprove}
                                    className="rounded-xl px-5 py-2 text-xs font-semibold text-white bg-brand-primary hover:bg-brand-dark shadow-sm transition active:scale-[0.98]"
                                >
                                    <CheckCircle className="w-4 h-4 mr-1.5 inline" /> Aprobar Admisión Formal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}

const DOCUMENT_LABELS = {
    birth_certificate: 'Acta de Nacimiento',
    id_card: 'Cédula / Identidad',
    high_school_record: 'Récord de Bachillerato',
    photo: 'Fotografía 2x2',
    medical_certificate: 'Certificado Médico',
    payment_receipt: 'Recibo de Pago',
    bachelor_certificate: 'Certificado de Bachiller',
};

function getDocumentLabel(key) {
    return DOCUMENT_LABELS[key] || ucwords(String(key || '').replace(/_/g, ' '));
}

function ucwords(str) {
    return str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
        return letter.toUpperCase();
    });
}
