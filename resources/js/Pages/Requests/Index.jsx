import { X, Check } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function RequestsIndex({
    requests,
    requestTypes = [],
    courses = [],
    filters = {},
    stats = {},
}) {
    // Modals
    const [reviewModalOpen, setReviewModalOpen] = useState(false);
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [typesModalOpen, setTypesModalOpen] = useState(false);

    // Selected item for review
    const [selectedRequest, setSelectedRequest] = useState(null);
    const [adminNotes, setAdminNotes] = useState('');

    // Filters
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');

    // Create Manual Request State
    const [studentSearchQuery, setStudentSearchQuery] = useState('');
    const [searchedStudents, setSearchedStudents] = useState([]);
    const [selectedStudent, setSelectedStudent] = useState(null);
    const [searchingStudent, setSearchingStudent] = useState(false);

    const manualForm = useForm({
        student_id: '',
        request_type_id: requestTypes[0]?.id || '',
        course_id: '',
        details: '',
    });

    // Request Types Config State
    const [editingType, setEditingType] = useState(null);
    const typeForm = useForm({
        name: '',
        description: '',
        requires_payment: false,
        payment_amount: 0,
        requires_enrolled_course: false,
        requires_completed_course: false,
        is_active: true,
    });

    // Filter change
    const handleFilterChange = (newStatus, newSearch) => {
        setStatusFilter(newStatus);
        router.get('/admin/requests', {
            status: newStatus,
            search: newSearch,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    // Review Modal Open
    const openReview = (req) => {
        setSelectedRequest(req);
        setAdminNotes(req.admin_notes || '');
        setReviewModalOpen(true);
    };

    const handleUpdateStatus = (newStatus) => {
        if (!selectedRequest) return;
        router.post(`/admin/requests/${selectedRequest.id}/status`, {
            status: newStatus,
            admin_notes: adminNotes,
        }, {
            onSuccess: () => {
                setReviewModalOpen(false);
            },
        });
    };

    // Student Live Search
    const handleStudentSearch = async (val) => {
        setStudentSearchQuery(val);
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

    const handleSelectStudent = (student) => {
        setSelectedStudent(student);
        manualForm.setData('student_id', student.id);
        setSearchedStudents([]);
        setStudentSearchQuery(`${student.name} (${student.code})`);
    };

    const handleCreateManualRequest = (e) => {
        e.preventDefault();
        manualForm.post('/admin/requests', {
            onSuccess: () => {
                setCreateModalOpen(false);
                manualForm.reset();
                setSelectedStudent(null);
                setStudentSearchQuery('');
            },
        });
    };

    // Types modal handlers
    const openNewType = () => {
        setEditingType(null);
        typeForm.reset({
            name: '',
            description: '',
            requires_payment: false,
            payment_amount: 0,
            requires_enrolled_course: false,
            requires_completed_course: false,
            is_active: true,
        });
        typeForm.clearErrors();
        setTypesModalOpen(true);
    };

    const openEditType = (type) => {
        setEditingType(type);
        typeForm.setData({
            name: type.name,
            description: type.description || '',
            requires_payment: Boolean(type.requires_payment),
            payment_amount: type.payment_amount || 0,
            requires_enrolled_course: Boolean(type.requires_enrolled_course),
            requires_completed_course: Boolean(type.requires_completed_course),
            is_active: Boolean(type.is_active),
        });
        typeForm.clearErrors();
        setTypesModalOpen(true);
    };

    const handleSaveType = (e) => {
        e.preventDefault();
        if (editingType) {
            typeForm.put(`/admin/requests/types/${editingType.id}`, {
                onSuccess: () => {
                    setEditingType(null);
                    typeForm.reset();
                },
            });
        } else {
            typeForm.post('/admin/requests/types', {
                onSuccess: () => {
                    typeForm.reset();
                },
            });
        }
    };

    const handleDeleteType = (type) => {
        if (confirm(`¿Eliminar el tipo de trámite "${type.name}"?`)) {
            router.delete(`/admin/requests/types/${type.id}`);
        }
    };

    const statusPills = [
        { key: 'all', label: 'Todos', count: stats.total || 0 },
        { key: 'pendiente', label: 'Pendientes', count: stats.pending || 0 },
        { key: 'aprobado', label: 'Aprobados', count: stats.approved || 0 },
        { key: 'rechazado', label: 'Rechazados', count: stats.rejected || 0 },
    ];

    const currentSelectedType = requestTypes.find(t => t.id.toString() === manualForm.data.request_type_id?.toString());

    return (
        <AuthenticatedLayout title="Trámites y Solicitudes">
            <Head title="Trámites y Solicitudes Estudiantiles - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </span>
                            Trámites y Solicitudes Estudiantiles
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            onClick={() => setTypesModalOpen(true)}
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Tipos de Trámite
                        </button>
                        <button
                            type="button"
                            onClick={() => setCreateModalOpen(true)}
                            className="inline-flex items-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            + Nueva Solicitud Manual
                        </button>
                    </div>
                </div>

                {/* KPI Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Solicitudes</span>
                            <span className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Trámites gestionados en ventanilla
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Pendientes</span>
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
                            Por dictaminar por registro
                        </div>
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
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                            Completadas exitosamente
                        </div>
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
                        <div className="text-xs text-gray-500 mt-1">
                            No procedentes
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
                            placeholder="Buscar por estudiante, matrícula..."
                            className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs pl-8 pr-3.5 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                        />
                        <svg className="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {/* Table of Requests */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    {requests.data.length === 0 ? (
                        <div className="p-12 text-center text-gray-500">
                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 className="mt-3 text-base font-semibold text-gray-900 dark:text-white">No hay solicitudes registradas</h3>
                            <p className="mt-1 text-sm text-gray-500">No se encontraron trámites según los filtros indicados.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-gray-800 text-[11px] font-bold text-gray-400 uppercase tracking-wider bg-gray-50/50 dark:bg-gray-800/30">
                                        <th className="py-3 px-4">Estudiante</th>
                                        <th className="py-3 px-4">Tipo de Trámite</th>
                                        <th className="py-3 px-4">Materia / Carrera</th>
                                        <th className="py-3 px-4">Arancel / Cobro</th>
                                        <th className="py-3 px-4">Estado</th>
                                        <th className="py-3 px-4">Fecha Solicitud</th>
                                        <th className="py-3 px-4 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    {requests.data.map(req => {
                                        const studentName = req.student?.user?.name || `${req.student?.first_name || ''} ${req.student?.last_name || ''}`.trim() || 'Estudiante';
                                        const typeName = req.request_type?.name || req.type || 'Trámite';
                                        const payment = req.payment;

                                        return (
                                            <tr key={req.id} className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition">
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="h-8 w-8 rounded-full bg-brand-primary/10 text-brand-primary dark:bg-blue-900/30 dark:text-blue-300 font-bold flex items-center justify-center shrink-0 text-xs">
                                                            {studentName[0]}
                                                        </div>
                                                        <div>
                                                            <div className="font-bold text-gray-900 dark:text-white">
                                                                {studentName}
                                                            </div>
                                                            <div className="text-[11px] text-gray-400 font-mono mt-0.5">
                                                                {req.student?.student_code || req.student?.cedula || 'Sin matrícula'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="py-3 px-4">
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-brand-primary dark:bg-blue-950/40 dark:text-blue-300">
                                                        {typeName}
                                                    </span>
                                                </td>

                                                <td className="py-3 px-4 text-gray-700 dark:text-gray-300">
                                                    {req.course?.name || '—'}
                                                </td>

                                                <td className="py-3 px-4">
                                                    {payment ? (
                                                        <span className={`inline-flex items-center gap-1 font-semibold ${
                                                            payment.status === 'Pagado'
                                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                                : 'text-amber-600 dark:text-amber-400'
                                                        }`}>
                                                            {payment.status === 'Pagado' ? 'Pagado' : `Pendiente RD$ ${Number(payment.amount).toLocaleString('es-DO', { minimumFractionDigits: 2 })}`}
                                                        </span>
                                                    ) : req.request_type?.requires_payment ? (
                                                        <span className="text-gray-500 font-medium">
                                                            Arancel: RD$ {Number(req.request_type.payment_amount).toLocaleString('es-DO', { minimumFractionDigits: 2 })}
                                                        </span>
                                                    ) : (
                                                        <span className="text-gray-400 italic">Sin arancel</span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4">
                                                    {req.status === 'aprobado' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                            Aprobado
                                                        </span>
                                                    ) : req.status === 'en_proceso' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                                            En Proceso
                                                        </span>
                                                    ) : req.status === 'rechazado' ? (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                            Rechazado
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                                            <span className="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                            Pendiente
                                                        </span>
                                                    )}
                                                </td>

                                                <td className="py-3 px-4 text-gray-500">
                                                    {new Date(req.created_at).toLocaleDateString('es-DO')}
                                                </td>

                                                <td className="py-3 px-4 text-right">
                                                    <button
                                                        type="button"
                                                        onClick={() => openReview(req)}
                                                        className="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/30 text-brand-primary dark:text-blue-300 font-semibold text-xs transition"
                                                    >
                                                        Examinar
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Pagination */}
                    {requests.links && requests.links.length > 3 && (
                        <div className="p-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <span className="text-xs text-gray-500">
                                Mostrando {requests.from || 0} a {requests.to || 0} de {requests.total} trámites
                            </span>
                            <div className="flex gap-1">
                                {requests.links.map((link, idx) => (
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

            {/* Modal: Revisión y Dictamen de Solicitud */}
            {reviewModalOpen && selectedRequest && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-lg rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 space-y-5">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Revisión de Trámite #{selectedRequest.id}
                                </h3>
                                <p className="text-xs text-gray-500 mt-0.5">
                                    {selectedRequest.request_type?.name || selectedRequest.type}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setReviewModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        {/* Student and details info */}
                        <div className="bg-gray-50 dark:bg-gray-900/40 p-4 rounded-xl text-xs space-y-2">
                            <div>
                                <span className="text-gray-400">Estudiante:</span>{' '}
                                <strong className="text-gray-900 dark:text-white">
                                    {selectedRequest.student?.user?.name || `${selectedRequest.student?.first_name || ''} ${selectedRequest.student?.last_name || ''}`}
                                </strong>{' '}
                                <span className="text-gray-400">({selectedRequest.student?.student_code})</span>
                            </div>
                            <div>
                                <span className="text-gray-400">Correo:</span>{' '}
                                <strong>{selectedRequest.student?.email || selectedRequest.student?.user?.email}</strong>
                            </div>
                            {selectedRequest.course && (
                                <div>
                                    <span className="text-gray-400">Materia / Carrera:</span>{' '}
                                    <strong>{selectedRequest.course.name}</strong>
                                </div>
                            )}
                            <div className="pt-2 border-t border-gray-200 dark:border-gray-800">
                                <span className="text-gray-400 font-semibold block mb-0.5">Motivo / Justificación presentada:</span>
                                <p className="text-gray-700 dark:text-gray-300 italic bg-white dark:bg-gray-800 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">
                                    {selectedRequest.details}
                                </p>
                            </div>
                        </div>

                        {/* Admin Notes */}
                        <div>
                            <label className="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">
                                Observaciones / Respuesta Administrativa:
                            </label>
                            <textarea
                                rows="3"
                                value={adminNotes}
                                onChange={e => setAdminNotes(e.target.value)}
                                placeholder="Escribe observaciones para el estudiante o motivos de resolución..."
                                className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                            ></textarea>
                        </div>

                        {/* Status Buttons */}
                        <div className="pt-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-2">
                            <button
                                type="button"
                                onClick={() => handleUpdateStatus('rechazado')}
                                className="px-4 py-2 rounded-xl text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 transition"
                            >
                                <X className="w-4 h-4 mr-1 inline" /> Rechazar
                            </button>

                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={() => handleUpdateStatus('en_proceso')}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/30 hover:bg-blue-100 transition"
                                >
                                    En Proceso
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleUpdateStatus('aprobado')}
                                    className="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition active:scale-[0.98]"
                                >
                                    <Check className="w-4 h-4 mr-1 inline" /> Aprobar Solicitud
                                </button>
                            </div>
                        </div>
                    </div>
                </div>, document.body)
            )}

            {/* Modal: Nueva Solicitud Manual */}
            {createModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-lg rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                Registrar Solicitud Manual
                            </h3>
                            <button
                                type="button"
                                onClick={() => setCreateModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        <form onSubmit={handleCreateManualRequest} className="space-y-4 mt-4">
                            {/* Student Search */}
                            <div className="relative">
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Estudiante *
                                </label>
                                <input
                                    type="text"
                                    value={studentSearchQuery}
                                    onChange={e => handleStudentSearch(e.target.value)}
                                    placeholder="Buscar por nombre, matrícula o cédula..."
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
                                {manualForm.errors.student_id && (
                                    <p className="text-xs text-red-500 mt-1">{manualForm.errors.student_id}</p>
                                )}
                            </div>

                            {/* Request Type */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Tipo de Trámite *
                                </label>
                                <select
                                    value={manualForm.data.request_type_id}
                                    onChange={e => manualForm.setData('request_type_id', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    {requestTypes.map(t => (
                                        <option key={t.id} value={t.id}>
                                            {t.name} {t.requires_payment ? `(RD$ ${Number(t.payment_amount).toLocaleString('es-DO')})` : '(Gratis)'}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Course selection if required or available */}
                            {currentSelectedType && (currentSelectedType.requires_enrolled_course || currentSelectedType.requires_completed_course) && (
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Materia / Carrera Asociada *
                                    </label>
                                    <select
                                        value={manualForm.data.course_id}
                                        onChange={e => manualForm.setData('course_id', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    >
                                        <option value="">Seleccione el curso o materia...</option>
                                        {courses.map(c => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                    {manualForm.errors.course_id && (
                                        <p className="text-xs text-red-500 mt-1">{manualForm.errors.course_id}</p>
                                    )}
                                </div>
                            )}

                            {/* Details */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Detalle / Exposición de Motivos *
                                </label>
                                <textarea
                                    rows="3"
                                    value={manualForm.data.details}
                                    onChange={e => manualForm.setData('details', e.target.value)}
                                    placeholder="Indique el motivo por el cual el estudiante requiere el trámite..."
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                ></textarea>
                                {manualForm.errors.details && (
                                    <p className="text-xs text-red-500 mt-1">{manualForm.errors.details}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setCreateModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={manualForm.processing}
                                    className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {manualForm.processing ? 'Registrando...' : 'Crear Trámite'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* Modal: Configuración de Tipos de Trámite */}
            {typesModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 space-y-5">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                Tipos de Trámites & Aranceles Institucionales
                            </h3>
                            <button
                                type="button"
                                onClick={() => setTypesModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        {/* Form: Crear/Editar Tipo */}
                        <form onSubmit={handleSaveType} className="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-800 space-y-3">
                            <h4 className="text-xs font-bold uppercase tracking-wider text-gray-500">
                                {editingType ? `Editar: ${editingType.name}` : '+ Registrar Nuevo Tipo de Trámite'}
                            </h4>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Nombre *</label>
                                    <input
                                        type="text"
                                        value={typeForm.data.name}
                                        onChange={e => typeForm.setData('name', e.target.value)}
                                        placeholder="Ej: Carta de Salida, Convalidación"
                                        required
                                        className="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white focus:ring-brand-primary"
                                    />
                                </div>
                                <div>
                                    <label className="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Arancel / Costo (RD$)</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={typeForm.data.payment_amount}
                                        onChange={e => {
                                            const val = parseFloat(e.target.value) || 0;
                                            typeForm.setData({
                                                ...typeForm.data,
                                                payment_amount: val,
                                                requires_payment: val > 0,
                                            });
                                        }}
                                        className="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs px-3 py-2 text-gray-900 dark:text-white focus:ring-brand-primary"
                                    />
                                </div>
                            </div>

                            <div className="flex flex-wrap items-center gap-4 pt-1">
                                <label className="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={typeForm.data.requires_payment}
                                        onChange={e => typeForm.setData('requires_payment', e.target.checked)}
                                        className="rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                                    />
                                    Genera Cobro Automático
                                </label>
                                <label className="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={typeForm.data.requires_enrolled_course}
                                        onChange={e => typeForm.setData('requires_enrolled_course', e.target.checked)}
                                        className="rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                                    />
                                    Requiere Curso Cursando
                                </label>
                                <label className="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={typeForm.data.requires_completed_course}
                                        onChange={e => typeForm.setData('requires_completed_course', e.target.checked)}
                                        className="rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                                    />
                                    Requiere Curso Aprobado
                                </label>
                            </div>

                            <div className="flex justify-end gap-2 pt-2">
                                {editingType && (
                                    <button
                                        type="button"
                                        onClick={openNewType}
                                        className="rounded-lg px-3 py-1.5 text-xs text-gray-500 hover:bg-gray-200"
                                    >
                                        Cancelar Edición
                                    </button>
                                )}
                                <button
                                    type="submit"
                                    disabled={typeForm.processing}
                                    className="rounded-lg bg-brand-primary hover:bg-brand-dark px-4 py-1.5 text-xs font-semibold text-white shadow-xs"
                                >
                                    {editingType ? 'Actualizar Tipo' : 'Guardar Tipo'}
                                </button>
                            </div>
                        </form>

                        {/* List of Types */}
                        <div className="space-y-2">
                            <h4 className="text-xs font-bold uppercase tracking-wider text-gray-400">
                                Catálogo de Trámites ({requestTypes.length})
                            </h4>
                            <div className="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                                {requestTypes.map(t => (
                                    <div key={t.id} className="p-3 bg-white dark:bg-[#1a1a2e] flex items-center justify-between gap-3 text-xs">
                                        <div>
                                            <div className="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                {t.name}
                                                {t.requires_payment && (
                                                    <span className="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                                                        (RD$ {Number(t.payment_amount).toLocaleString('es-DO', { minimumFractionDigits: 2 })})
                                                    </span>
                                                )}
                                            </div>
                                            <div className="text-[11px] text-gray-400 mt-0.5">
                                                {t.requires_enrolled_course && '• Requiere curso activo '}
                                                {t.requires_completed_course && '• Requiere curso aprobado '}
                                                {!t.requires_enrolled_course && !t.requires_completed_course && '• Solicitud general'}
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <button
                                                type="button"
                                                onClick={() => openEditType(t)}
                                                className="p-1.5 text-indigo-600 hover:text-indigo-800 font-semibold"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => handleDeleteType(t)}
                                                className="p-1.5 text-red-500 hover:text-red-700"
                                            >
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="flex justify-end pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button
                                type="button"
                                onClick={() => setTypesModalOpen(false)}
                                className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
