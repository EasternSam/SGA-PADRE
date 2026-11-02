import React, { useState, useMemo } from 'react';
import Modal from '@/components/ui/Modal';
import {
    Search,
    BookOpen,
    Clock,
    Users,
    MapPin,
    CheckCircle2,
    Check,
    X,
    AlertTriangle,
    AlertCircle,
    ArrowRight,
    ShieldCheck,
    DollarSign,
    Calendar,
    ChevronRight,
    Loader2,
    Sparkles,
    User,
    FileText,
    CreditCard
} from 'lucide-react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function StudentProfile({
    student,
    kioskPin = null,
    enrollments,
    payments,
    financialSummary,
    allCourses = [],
    allScholarships = [],
}) {
    const { branding } = usePage().props;
    const palette = useMemo(() => ({
        primary: branding?.palette?.primary || 'var(--brand-primary, #211170)',
        secondary: branding?.palette?.secondary || 'var(--brand-secondary, #1c1917)',
        accent: branding?.palette?.accent || 'var(--brand-accent, #ea580c)',
        neutral: branding?.palette?.neutral || 'var(--brand-neutral, #78716c)',
    }), [branding]);
    const [activeTab, setActiveTab] = useState('enrollments'); // 'enrollments' | 'payments' | 'personal' | 'documents'

    // Modales de Gestión de Inscripciones
    const [isEnrollModalOpen, setIsEnrollModalOpen] = useState(false);
    const [scheduleSearch, setScheduleSearch] = useState('');
    const [availableSchedules, setAvailableSchedules] = useState([]);
    const [isSearchingSchedules, setIsSearchingSchedules] = useState(false);
    const [selectedSchedule, setSelectedSchedule] = useState(null);

    // Modal Cambio de Sección
    const [isChangeSectionModalOpen, setIsChangeSectionModalOpen] = useState(false);
    const [enrollmentToChange, setEnrollmentToChange] = useState(null);
    const [alternativeSchedules, setAlternativeSchedules] = useState([]);
    const [selectedNewScheduleId, setSelectedNewScheduleId] = useState('');
    const [isFetchingAlternatives, setIsFetchingAlternatives] = useState(false);

    // Modales de Confirmación
    const [isUnenrollModalOpen, setIsUnenrollModalOpen] = useState(false);
    const [enrollmentToCancel, setEnrollmentToCancel] = useState(null);
    const [isWithdrawModalOpen, setIsWithdrawModalOpen] = useState(false);
    const [enrollmentToWithdraw, setEnrollmentToWithdraw] = useState(null);

    // Modal de Edición de Ficha
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const editForm = useForm({
        first_name: student.first_name || '',
        last_name: student.last_name || '',
        cedula: student.cedula || '',
        email: student.email || '',
        mobile_phone: student.mobile_phone || '',
        home_phone: student.home_phone || '',
        address: student.address || '',
        city: student.city || '',
        sector: student.sector || '',
        birth_date: student.birth_date ? student.birth_date.substring(0, 10) : '',
        gender: student.gender || '',
        nationality: student.nationality || 'Dominicana',
        how_found: student.how_found || '',
        course_id: student.course_id ? String(student.course_id) : '',
        scholarship_id: student.scholarship_id ? String(student.scholarship_id) : '',
        is_minor: Boolean(student.is_minor),
        tutor_name: student.tutor_name || '',
        tutor_cedula: student.tutor_cedula || '',
        tutor_phone: student.tutor_phone || '',
        tutor_relationship: student.tutor_relationship || '',
        status: student.status || 'Activo',
        rnc: student.rnc || '',
    });

    // Formulario de inscripción
    const enrollForm = useForm({
        course_schedule_id: '',
    });

    // Buscar secciones en vivo
    const handleSearchSchedules = async (term) => {
        setScheduleSearch(term);
        if (!term.trim()) {
            setAvailableSchedules([]);
            return;
        }

        setIsSearchingSchedules(true);
        try {
            const res = await fetch(`/admin/students/${student.id}/available-schedules?q=${encodeURIComponent(term)}`);
            const data = await res.json();
            setAvailableSchedules(data);
        } catch (e) {
            console.error(e);
        } finally {
            setIsSearchingSchedules(false);
        }
    };

    const handleConfirmEnroll = (e) => {
        e.preventDefault();
        if (!selectedSchedule) return;

        enrollForm.setData('course_schedule_id', selectedSchedule.id);
        enrollForm.post(`/admin/students/${student.id}/enroll`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsEnrollModalOpen(false);
                setSelectedSchedule(null);
                setScheduleSearch('');
                setAvailableSchedules([]);
            },
        });
    };

    // Abrir cambio de sección
    const openChangeSection = async (enrollment) => {
        setEnrollmentToChange(enrollment);
        setSelectedNewScheduleId('');
        setIsChangeSectionModalOpen(true);
        setIsFetchingAlternatives(true);

        try {
            const res = await fetch(`/admin/students/${student.id}/enrollments/${enrollment.id}/available-schedules`);
            const data = await res.json();
            setAlternativeSchedules(data);
        } catch (e) {
            console.error(e);
        } finally {
            setIsFetchingAlternatives(false);
        }
    };

    const handleConfirmChangeSection = () => {
        if (!selectedNewScheduleId || !enrollmentToChange) return;

        router.post(
            `/admin/students/${student.id}/enrollments/${enrollmentToChange.id}/change-section`,
            { new_course_schedule_id: selectedNewScheduleId },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsChangeSectionModalOpen(false);
                    setEnrollmentToChange(null);
                },
            }
        );
    };

    // Anular inscripción
    const confirmUnenroll = (enrollment) => {
        setEnrollmentToCancel(enrollment);
        setIsUnenrollModalOpen(true);
    };

    const handleUnenroll = () => {
        if (!enrollmentToCancel) return;
        router.post(
            `/admin/students/${student.id}/enrollments/${enrollmentToCancel.id}/unenroll`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsUnenrollModalOpen(false);
                    setEnrollmentToCancel(null);
                },
            }
        );
    };

    // Retirar materia
    const confirmWithdraw = (enrollment) => {
        setEnrollmentToWithdraw(enrollment);
        setIsWithdrawModalOpen(true);
    };

    const handleWithdraw = () => {
        if (!enrollmentToWithdraw) return;
        router.post(
            `/admin/students/${student.id}/enrollments/${enrollmentToWithdraw.id}/withdraw`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsWithdrawModalOpen(false);
                    setEnrollmentToWithdraw(null);
                },
            }
        );
    };

    // Reingresar a materia retirada
    const handleReEnroll = (enrollmentId) => {
        router.post(
            `/admin/students/${student.id}/enrollments/${enrollmentId}/re-enroll`,
            {},
            { preserveScroll: true }
        );
    };

    // Resetear PIN de Kiosco
    const handleResetKioskPin = () => {
        router.post(`/admin/students/${student.id}/reset-kiosk-pin`, {}, { preserveScroll: true });
    };

    // Guardar cambios del perfil
    const handleSaveProfile = (e) => {
        e.preventDefault();
        editForm.put(`/admin/students/${student.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsEditModalOpen(false);
            },
        });
    };

    const initials = `${student.first_name?.[0] || ''}${student.last_name?.[0] || ''}`.toUpperCase();

    return (
        <AuthenticatedLayout title={`Expediente: ${student.first_name} ${student.last_name}`}>
            <Head title={`Expediente: ${student.first_name} ${student.last_name} — SGA`} />

            <div className="space-y-6">
                {/* Botón Volver y Migajas */}
                <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <Link href="/admin/students" className="hover:text-[var(--brand-primary)] dark:hover:text-blue-400 transition">
                        Directorio de Estudiantes
                    </Link>
                    <span>/</span>
                    <span className="font-semibold text-gray-900 dark:text-white">
                        {student.first_name} {student.last_name} ({student.student_code || `ID #${student.id}`})
                    </span>
                </div>

                {/* Tarjeta 360° del Estudiante */}
                <div className="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <div className="p-6 sm:p-8 text-white relative rounded-t-3xl overflow-hidden" style={{ background: `linear-gradient(135deg, ${palette.primary} 0%, color-mix(in srgb, ${palette.primary} 70%, black) 100%)` }}>
                        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative z-10">
                            <div className="flex items-start sm:items-center gap-5">
                                <div className="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white font-black text-2xl border border-white/20 backdrop-blur-md shadow-inner">
                                    {initials}
                                </div>
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                            {student.first_name} {student.last_name}
                                        </h1>
                                        <span className="rounded-full bg-white/20 px-3 py-0.5 text-xs font-semibold backdrop-blur-md">
                                            {student.status || 'Activo'}
                                        </span>
                                    </div>
                                    <p className="mt-1 text-sm text-blue-100 flex flex-wrap items-center gap-3">
                                        <span>Matrícula: <strong className="font-mono text-white">{student.student_code || `EST-${student.id}`}</strong></span>
                                        <span>•</span>
                                        <span>Cédula: <strong className="font-mono text-white">{student.cedula || 'N/A'}</strong></span>
                                        <span>•</span>
                                        <span>{student.email}</span>
                                    </p>
                                    <div className="mt-3 flex flex-wrap gap-2">
                                        <span className="inline-flex items-center rounded-lg bg-blue-900/60 px-2.5 py-1 text-xs font-medium text-blue-200 border border-blue-400/30">
                                            Programa: {student.course?.name || 'General / Técnico'}
                                        </span>
                                        {student.scholarship && (
                                            <span className="inline-flex items-center rounded-lg bg-amber-500/20 px-2.5 py-1 text-xs font-semibold text-amber-300 border border-amber-400/30">
                                                Beca {student.scholarship.name} ({Number(student.scholarship.discount_percentage)}% Desc.)
                                            </span>
                                        )}
                                        <div className="inline-flex items-center gap-2 rounded-lg bg-white/10 px-2.5 py-1 text-xs text-white backdrop-blur-sm">
                                            <span>PIN Kiosco: <strong className="font-mono text-amber-300">{kioskPin || 'Sin asignar'}</strong></span>
                                            <button
                                                type="button"
                                                onClick={handleResetKioskPin}
                                                title="Regenerar PIN"
                                                className="text-blue-200 hover:text-white transition"
                                            >
                                                <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Botones de Acción Inmediata */}
                            <div className="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => setIsEditModalOpen(true)}
                                    className="rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-md transition"
                                >
                                    Editar Ficha
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setIsEnrollModalOpen(true)}
                                    className="rounded-xl bg-white hover:bg-blue-50 px-4 py-2 text-xs font-bold text-[var(--brand-primary)] shadow-sm transition"
                                >
                                    + Inscribir Asignatura
                                </button>
                                <Link
                                    href={`/admin/pos?student_id=${student.id}`}
                                    className="rounded-xl bg-amber-400 hover:bg-amber-300 px-4 py-2 text-xs font-bold text-gray-900 shadow-sm transition inline-flex items-center gap-1.5"
                                >
                                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Cobrar en Caja (POS)
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Barra de Pestañas de Navegación */}
                    <div className="border-b border-gray-100 bg-white px-6 dark:border-gray-800 dark:bg-[#161622]">
                        <nav className="-mb-px flex space-x-8" aria-label="Tabs">
                            <button
                                type="button"
                                onClick={() => setActiveTab('enrollments')}
                                className={`whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition ${
                                    activeTab === 'enrollments'
                                        ? 'border-[var(--brand-primary)] text-[var(--brand-primary)]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                }`}
                            >
                                Asignaturas e Inscripciones ({enrollments?.all?.length || 0})
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveTab('payments')}
                                className={`whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition ${
                                    activeTab === 'payments'
                                        ? 'border-[var(--brand-primary)] text-[var(--brand-primary)]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                }`}
                            >
                                Historial Financiero ({payments?.length || 0})
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveTab('personal')}
                                className={`whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition ${
                                    activeTab === 'personal'
                                        ? 'border-[var(--brand-primary)] text-[var(--brand-primary)]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                }`}
                            >
                                Expediente Integral & Tutor
                            </button>

                            <button
                                type="button"
                                onClick={() => setActiveTab('documents')}
                                className={`whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition ${
                                    activeTab === 'documents'
                                        ? 'border-[var(--brand-primary)] text-[var(--brand-primary)]'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                }`}
                            >
                                Documentos y Reportes Oficiales
                            </button>
                        </nav>
                    </div>
                </div>

                {/* CONTENIDO TAB 1: ASIGNATURAS E INSCRIPCIONES */}
                {activeTab === 'enrollments' && (
                    <div className="space-y-6">
                        {/* 1. Inscripciones Pendientes de Pago */}
                        {enrollments?.pending && enrollments.pending.length > 0 && (
                            <div className="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 dark:border-amber-900/30 dark:bg-amber-950/20">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="flex items-center gap-2">
                                        <svg className="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <h3 className="text-sm font-bold text-amber-900 dark:text-amber-300">
                                            Inscripciones Pendientes de Pago ({enrollments.pending.length})
                                        </h3>
                                    </div>
                                    <span className="text-xs text-amber-700 dark:text-amber-400 font-medium">
                                        Requieren cancelación de arancel para confirmación oficial
                                    </span>
                                </div>

                                <div className="overflow-x-auto rounded-xl border border-amber-200/60 bg-white dark:bg-[#161622] dark:border-amber-900/40">
                                    <table className="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-left text-xs">
                                        <thead className="bg-amber-50/50 dark:bg-amber-950/30">
                                            <tr>
                                                <th className="py-3 px-4 font-bold text-gray-700 dark:text-gray-300">Módulo / Asignatura</th>
                                                <th className="py-3 px-3 font-bold text-gray-700 dark:text-gray-300">Docente & Horario</th>
                                                <th className="py-3 px-3 font-bold text-gray-700 dark:text-gray-300">Monto Inscripción</th>
                                                <th className="py-3 px-4 text-right font-bold text-gray-700 dark:text-gray-300">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                            {enrollments.pending.map((enr) => (
                                                <tr key={enr.id} className="hover:bg-amber-50/20">
                                                    <td className="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                                        <div>{enr.course_schedule?.module?.name || 'Módulo General'}</div>
                                                        <div className="text-[11px] text-gray-500 font-normal">
                                                            {enr.course_schedule?.module?.course?.name} ({enr.course_schedule?.section_name || `Sección #${enr.course_schedule_id}`})
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-3 text-gray-600 dark:text-gray-300">
                                                        <div>Prof. {enr.course_schedule?.teacher?.name || 'Por asignar'}</div>
                                                        <div className="text-[11px] text-gray-400">
                                                            {Array.isArray(enr.course_schedule?.days_of_week) ? enr.course_schedule.days_of_week.join(', ') : 'Horario flexible'}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-3">
                                                        <span className="font-mono font-bold text-amber-600 dark:text-amber-400">
                                                            DOP ${Number(enr.payment?.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                        </span>
                                                    </td>
                                                    <td className="py-3 px-4 text-right space-x-2">
                                                        <Link
                                                            href={`/admin/pos?student_id=${student.id}`}
                                                            className="inline-flex items-center rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition"
                                                        >
                                                            Pagar en POS
                                                        </Link>
                                                        <button
                                                            type="button"
                                                            onClick={() => openChangeSection(enr)}
                                                            className="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                                        >
                                                            Cambiar Sección
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => confirmUnenroll(enr)}
                                                            className="rounded-lg border border-red-300 px-2.5 py-1.5 text-xs text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400"
                                                        >
                                                            Anular
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}

                        {/* 2. Asignaturas Cursando / Activas */}
                        <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-2">
                                    <span className="flex h-3 w-3 rounded-full bg-emerald-500 animate-pulse" />
                                    <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                        Asignaturas Activas en Curso ({enrollments?.active?.length || 0})
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsEnrollModalOpen(true)}
                                    className="text-xs font-semibold text-[var(--brand-primary)] hover:underline dark:text-blue-400"
                                >
                                    + Agregar otra asignatura
                                </button>
                            </div>

                            {enrollments?.active && enrollments.active.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {enrollments.active.map((enr) => (
                                        <div
                                            key={enr.id}
                                            className="rounded-2xl border border-gray-200 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-gray-900/30 flex flex-col justify-between"
                                        >
                                            <div>
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <span className="text-[11px] font-bold uppercase tracking-wider text-[var(--brand-primary)] dark:text-blue-400">
                                                            {enr.course_schedule?.module?.course?.name || 'Programa Técnico'}
                                                        </span>
                                                        <h4 className="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                                                            {enr.course_schedule?.module?.name || 'Módulo'}
                                                        </h4>
                                                    </div>
                                                    <span className="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                        Cursando
                                                    </span>
                                                </div>

                                                <div className="mt-4 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        <span>Docente: <strong>{enr.course_schedule?.teacher?.name || 'Profesor Titular'}</strong></span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <span>
                                                            Horario: {Array.isArray(enr.course_schedule?.days_of_week) ? enr.course_schedule.days_of_week.join(', ') : 'Por definir'}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                        <span>Aula: <strong>{enr.course_schedule?.classroom?.name || 'Virtual / Aula Principal'}</strong></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="mt-5 pt-4 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                                                <span className="text-[11px] text-gray-400">
                                                    Sección: {enr.course_schedule?.section_name || `#${enr.course_schedule_id}`}
                                                </span>
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => openChangeSection(enr)}
                                                        className="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 transition"
                                                    >
                                                        Cambiar Sección
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => confirmWithdraw(enr)}
                                                        className="rounded-lg border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 transition"
                                                    >
                                                        Retirar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="py-10 text-center text-gray-500 dark:text-gray-400">
                                    <p className="text-sm">El estudiante no tiene asignaturas activas actualmente.</p>
                                    <button
                                        type="button"
                                        onClick={() => setIsEnrollModalOpen(true)}
                                        className="mt-3 inline-flex items-center gap-2 rounded-xl bg-[var(--brand-primary)] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition"
                                    >
                                        Inscribir en Asignatura
                                    </button>
                                </div>
                            )}
                        </div>

                        {/* 3. Asignaturas Completadas & Retiradas */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Completadas */}
                            <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                                <h3 className="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span className="flex h-2.5 w-2.5 rounded-full bg-blue-500" />
                                    Cursos y Módulos Completados ({enrollments?.completed?.length || 0})
                                </h3>
                                {enrollments?.completed && enrollments.completed.length > 0 ? (
                                    <ul className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                        {enrollments.completed.map((enr) => (
                                            <li key={enr.id} className="py-3 flex items-center justify-between">
                                                <div>
                                                    <p className="font-semibold text-gray-900 dark:text-white">
                                                        {enr.course_schedule?.module?.name || 'Módulo'}
                                                    </p>
                                                    <p className="text-gray-400 text-[11px]">
                                                        {enr.course_schedule?.module?.course?.name}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <span className="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                        Nota: {enr.final_grade ?? 'Aprobado'}
                                                    </span>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="text-xs text-gray-400 py-4 text-center">Aún no hay asignaturas completadas.</p>
                                )}
                            </div>

                            {/* Retiros Académicos */}
                            <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                                <h3 className="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span className="flex h-2.5 w-2.5 rounded-full bg-gray-400" />
                                    Historial de Retiros ({enrollments?.withdrawn?.length || 0})
                                </h3>
                                {enrollments?.withdrawn && enrollments.withdrawn.length > 0 ? (
                                    <ul className="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                        {enrollments.withdrawn.map((enr) => (
                                            <li key={enr.id} className="py-3 flex items-center justify-between">
                                                <div>
                                                    <p className="font-semibold text-gray-900 dark:text-white">
                                                        {enr.course_schedule?.module?.name || 'Módulo'}
                                                    </p>
                                                    <p className="text-gray-400 text-[11px]">
                                                        Sección #{enr.course_schedule_id} • Estado: Retirado
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() => handleReEnroll(enr.id)}
                                                    className="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-[var(--brand-primary)] hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 transition"
                                                >
                                                    Reingresar
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                ) : (
                                    <p className="text-xs text-gray-400 py-4 text-center">Sin retiros académicos registrados.</p>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* CONTENIDO TAB 2: HISTORIAL FINANCIERO */}
                {activeTab === 'payments' && (
                    <div className="space-y-6">
                        {/* Tarjetas KPI de Estado de Cuenta */}
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                                <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Facturado
                                </p>
                                <p className="text-2xl font-black text-gray-900 dark:text-white mt-1">
                                    DOP ${Number(financialSummary?.totalInvoiced || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                </p>
                            </div>

                            <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                                <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Cobrado / Pagado
                                </p>
                                <p className="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                    DOP ${Number(financialSummary?.totalPaid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                </p>
                            </div>

                            <div className="rounded-2xl border border-amber-200 bg-amber-50/50 p-5 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20">
                                <div className="flex items-center justify-between">
                                    <p className="text-xs font-semibold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                        Balance Pendiente (Deuda)
                                    </p>
                                    <Link
                                        href={`/admin/pos?student_id=${student.id}`}
                                        className="text-xs font-bold text-[var(--brand-primary)] hover:underline dark:text-blue-400"
                                    >
                                        Ir al POS →
                                    </Link>
                                </div>
                                <p className="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                                    DOP ${Number(financialSummary?.balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                </p>
                            </div>
                        </div>

                        {/* Tabla de Pagos */}
                        <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                            <div className="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                    Desglose de Facturación y Comprobantes
                                </h3>
                                <Link
                                    href={`/admin/pos?student_id=${student.id}`}
                                    className="rounded-xl bg-[var(--brand-primary)] px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition"
                                >
                                    + Registrar Cobro
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-100 dark:divide-gray-800 text-left text-xs">
                                    <thead className="bg-gray-50 dark:bg-gray-900/40">
                                        <tr>
                                            <th className="py-3.5 pl-6 pr-3 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Concepto</th>
                                            <th className="px-3 py-3.5 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">NCF / Recibo</th>
                                            <th className="px-3 py-3.5 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Monto Base</th>
                                            <th className="px-3 py-3.5 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Descuento Beca</th>
                                            <th className="px-3 py-3.5 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total a Pagar</th>
                                            <th className="px-3 py-3.5 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                                            <th className="px-3 py-3.5 font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                        {payments && payments.length > 0 ? (
                                            payments.map((p) => (
                                                <tr key={p.id} className="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                                    <td className="py-3.5 pl-6 pr-3 font-semibold text-gray-900 dark:text-white">
                                                        <div>{p.payment_concept?.name || 'Arancel Educativo'}</div>
                                                        <div className="text-[11px] text-gray-400 font-normal">
                                                            {p.enrollment?.course_schedule?.module?.name || 'Servicio Académico General'}
                                                        </div>
                                                    </td>
                                                    <td className="px-3 py-3.5 font-mono text-[11px] text-gray-600 dark:text-gray-300">
                                                        {p.ncf || `REC-#${p.id}`}
                                                    </td>
                                                    <td className="px-3 py-3.5 font-mono text-gray-500">
                                                        DOP ${Number(p.original_amount || p.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-3 py-3.5 font-mono text-indigo-600 dark:text-indigo-400">
                                                        {Number(p.discount_amount) > 0 ? `- DOP $${Number(p.discount_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}` : '—'}
                                                    </td>
                                                    <td className="px-3 py-3.5 font-mono font-bold text-gray-900 dark:text-white">
                                                        DOP ${Number(p.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                                                    </td>
                                                    <td className="px-3 py-3.5 text-gray-500">
                                                        {p.created_at ? new Date(p.created_at).toLocaleDateString('es-DO') : 'N/A'}
                                                    </td>
                                                    <td className="px-3 py-3.5">
                                                        <span
                                                            className={`inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${
                                                                p.status === 'Pagado'
                                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                                    : p.status === 'Pendiente'
                                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                                                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                                            }`}
                                                        >
                                                            {p.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="7" className="py-8 text-center text-gray-400">
                                                    No hay cobros registrados en la cuenta de este estudiante.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {/* CONTENIDO TAB 3: EXPEDIENTE INTEGRAL & TUTOR */}
                {activeTab === 'personal' && (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {/* Datos Personales */}
                        <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622] space-y-4">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">
                                Ficha de Identidad & Contacto
                            </h3>
                            <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                                <div>
                                    <dt className="text-gray-400">Nombres y Apellidos</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.first_name} {student.last_name}</dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">Cédula Oficial</dt>
                                    <dd className="font-mono font-semibold text-gray-900 dark:text-white mt-0.5">{student.cedula || 'No registrada'}</dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">Fecha de Nacimiento</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">
                                        {student.birth_date ? new Date(student.birth_date).toLocaleDateString('es-DO') : 'N/A'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">Género</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.gender || 'No especificado'}</dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">Nacionalidad</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.nationality || 'Dominicana'}</dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">RNC Registrado</dt>
                                    <dd className="font-mono font-semibold text-gray-900 dark:text-white mt-0.5">{student.rnc || 'No requerido'}</dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">Teléfono Celular</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.mobile_phone || 'N/A'}</dd>
                                </div>
                                <div>
                                    <dt className="text-gray-400">Teléfono Fijo</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.home_phone || 'N/A'}</dd>
                                </div>
                                <div className="col-span-2">
                                    <dt className="text-gray-400">Dirección y Localidad</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">
                                        {student.address || ''} {student.sector ? `(${student.sector})` : ''} {student.city ? `, ${student.city}` : ''}
                                    </dd>
                                </div>
                                <div className="col-span-2">
                                    <dt className="text-gray-400">Canal de Captación</dt>
                                    <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.how_found || 'Ingreso directo'}</dd>
                                </div>
                            </dl>
                        </div>

                        {/* Datos del Tutor & Cuenta de Usuario */}
                        <div className="space-y-6">
                            {/* Tutor */}
                            <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622] space-y-4">
                                <h3 className="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3 flex items-center justify-between">
                                    <span>Tutor Legal / Responsable</span>
                                    {student.is_minor ? (
                                        <span className="rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            Menor de Edad
                                        </span>
                                    ) : (
                                        <span className="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] text-gray-500 dark:bg-gray-800">
                                            Mayor de Edad
                                        </span>
                                    )}
                                </h3>
                                {student.is_minor || student.tutor_name ? (
                                    <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                                        <div>
                                            <dt className="text-gray-400">Nombre del Tutor</dt>
                                            <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.tutor_name || 'N/A'}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-gray-400">Teléfono de Contacto</dt>
                                            <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.tutor_phone || 'N/A'}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-gray-400">Cédula del Tutor</dt>
                                            <dd className="font-mono font-semibold text-gray-900 dark:text-white mt-0.5">{student.tutor_cedula || 'N/A'}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-gray-400">Parentesco</dt>
                                            <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.tutor_relationship || 'Tutor'}</dd>
                                        </div>
                                    </dl>
                                ) : (
                                    <p className="text-xs text-gray-400">No aplica información de tutor para este estudiante.</p>
                                )}
                            </div>

                            {/* Cuenta de Usuario */}
                            <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622] space-y-4">
                                <h3 className="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-3">
                                    Credenciales de Acceso al Campus Virtual
                                </h3>
                                <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-xs">
                                    <div>
                                        <dt className="text-gray-400">Usuario / Correo de Login</dt>
                                        <dd className="font-semibold text-gray-900 dark:text-white mt-0.5">{student.user?.email || student.email}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-gray-400">PIN Kiosco Táctil</dt>
                                        <dd className="font-mono font-bold text-amber-500 mt-0.5">{kioskPin || 'Sin asignar'}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-gray-400">Fecha de Registro</dt>
                                        <dd className="text-gray-600 dark:text-gray-300 mt-0.5">
                                            {student.created_at ? new Date(student.created_at).toLocaleDateString('es-DO') : 'N/A'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-gray-400">Estado de Cuenta</dt>
                                        <dd className="text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">Habilitada</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                )}

                {/* CONTENIDO TAB 4: DOCUMENTOS Y REPORTES */}
                {activeTab === 'documents' && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {/* Reporte Académico Integral */}
                        <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622] flex flex-col justify-between">
                            <div>
                                <div className="h-12 w-12 rounded-xl bg-blue-50 text-[var(--brand-primary)] dark:bg-blue-900/30 dark:text-blue-300 flex items-center justify-center mb-4">
                                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 className="text-base font-bold text-gray-900 dark:text-white">
                                    Reporte Académico Oficial
                                </h4>
                            </div>
                            <a
                                href={`/reports/student-report/${student.id}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--brand-primary)] py-2.5 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition"
                            >
                                Descargar PDF
                            </a>
                        </div>

                        {/* Carnet Estudiantil y PIN Kiosco */}
                        <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622] flex flex-col justify-between">
                            <div>
                                <div className="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 flex items-center justify-center mb-4">
                                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <h4 className="text-base font-bold text-gray-900 dark:text-white">
                                    Carnet & PIN de Kiosco
                                </h4>
                            </div>
                            <a
                                href={`/admin/students/${student.id}/kiosk-pin-pdf`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-6 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                            >
                                Imprimir Hoja de PIN
                            </a>
                        </div>

                        {/* Estado de Cuenta Financiero */}
                        <div className="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-[#161622] flex flex-col justify-between">
                            <div>
                                <div className="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 flex items-center justify-center mb-4">
                                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h4 className="text-base font-bold text-gray-900 dark:text-white">
                                    Estado de Cuenta Financiero
                                </h4>
                            </div>
                            <a
                                href={`/reports/financial/${student.id}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="mt-6 inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                            >
                                Descargar Estado Financiero
                            </a>
                        </div>
                    </div>
                )}
            </div>

            {/* MODAL 1: INSCRIBIR EN ASIGNATURA / SECCIÓN */}
            <Modal
                isOpen={isEnrollModalOpen}
                onClose={() => {
                    setIsEnrollModalOpen(false);
                    setSelectedSchedule(null);
                }}
                title="Inscribir en Asignatura / Sección"
                subtitle={`Busca y selecciona una sección activa para matricular a ${student.first_name} ${student.last_name || ''}.`}
                maxWidth="max-w-2xl"
                footer={
                    <div className="flex items-center justify-between w-full">
                        <span className="text-xs text-gray-500 dark:text-gray-400">
                            {selectedSchedule ? (
                                <span className="flex items-center gap-1.5 font-medium text-emerald-600 dark:text-emerald-400">
                                    <Check className="size-3.5" />
                                    Sección: <strong>{selectedSchedule.section_name}</strong>
                                </span>
                            ) : (
                                'Selecciona una sección para continuar'
                            )}
                        </span>
                        <div className="flex items-center gap-2.5">
                            <button
                                type="button"
                                onClick={() => {
                                    setIsEnrollModalOpen(false);
                                    setSelectedSchedule(null);
                                }}
                                className="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={handleConfirmEnroll}
                                disabled={!selectedSchedule || enrollForm.processing}
                                className="rounded-xl px-5 py-2 text-xs font-bold text-white shadow-sm disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-95 transition-all flex items-center gap-1.5 cursor-pointer"
                                style={{ backgroundColor: palette.primary }}
                            >
                                {enrollForm.processing ? (
                                    <>
                                        <Loader2 className="size-3.5 animate-spin" />
                                        <span>Inscribiendo...</span>
                                    </>
                                ) : (
                                    <>
                                        <Check className="size-3.5" />
                                        <span>Confirmar Inscripción</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                }
            >
                <div className="space-y-4">
                    {/* Buscador de Asignaturas */}
                    <div>
                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Buscar Asignatura o Sección
                        </label>
                        <div className="relative">
                            <Search className="size-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <input
                                type="text"
                                value={scheduleSearch}
                                onChange={(e) => handleSearchSchedules(e.target.value)}
                                placeholder="Escribe el nombre del módulo, código de materia o sección..."
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/60 pl-10 pr-9 py-2.5 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:border-transparent transition-all shadow-xs"
                                style={{ '--tw-ring-color': palette.primary }}
                                autoFocus
                            />
                            {scheduleSearch && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setScheduleSearch('');
                                        setAvailableSchedules([]);
                                        setSelectedSchedule(null);
                                    }}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                                >
                                    <X className="size-3.5" />
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Resultados de Búsqueda */}
                    <div>
                        <div className="flex items-center justify-between mb-1.5">
                            <span className="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                Secciones Disponibles
                            </span>
                            {availableSchedules.length > 0 && (
                                <span className="text-[11px] font-medium text-gray-400">
                                    {availableSchedules.length} {availableSchedules.length === 1 ? 'resultado' : 'resultados'}
                                </span>
                            )}
                        </div>

                        <div className="border border-gray-100 dark:border-gray-800 rounded-2xl bg-gray-50/30 dark:bg-black/20 p-2 max-h-64 overflow-y-auto space-y-2 overscroll-contain">
                            {isSearchingSchedules ? (
                                <div className="py-8 text-center">
                                    <Loader2 className="size-5 animate-spin mx-auto text-gray-400 mb-2" />
                                    <p className="text-xs text-gray-500 dark:text-gray-400">Buscando secciones activas en catálogo...</p>
                                </div>
                            ) : availableSchedules.length > 0 ? (
                                availableSchedules.map((sch) => {
                                    const isSelected = selectedSchedule?.id === sch.id;
                                    const uniqueDays = [...new Set(Array.isArray(sch.days) ? sch.days : [])];
                                    const daysText = uniqueDays.length > 0 ? uniqueDays.join(', ') : 'Días a convenir';
                                    const fee = sch.registration_fee || sch.module_price || 0;

                                    return (
                                        <div
                                            key={sch.id}
                                            onClick={() => setSelectedSchedule(sch)}
                                            className={`rounded-xl p-3 border cursor-pointer transition-all duration-150 ${
                                                isSelected
                                                    ? 'shadow-xs ring-1'
                                                    : 'border-gray-200/80 dark:border-gray-800 bg-white dark:bg-[#161622] hover:border-gray-300 dark:hover:border-gray-700 hover:shadow-xs'
                                            }`}
                                            style={isSelected ? {
                                                borderColor: palette.primary,
                                                backgroundColor: `${palette.primary}08`,
                                                '--tw-ring-color': palette.primary,
                                            } : {}}
                                        >
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0 flex-1">
                                                    <div className="flex items-center gap-2 flex-wrap">
                                                        <span className="font-bold text-xs text-gray-900 dark:text-white flex items-center gap-1.5">
                                                            <BookOpen className="size-3.5 shrink-0" style={{ color: palette.primary }} />
                                                            {sch.module_name}
                                                        </span>
                                                        <span className="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                            {sch.section_name}
                                                        </span>
                                                    </div>
                                                    <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1 truncate">
                                                        {sch.course_name}
                                                    </p>
                                                    <div className="flex items-center gap-3 mt-2 text-[11px] text-gray-500 dark:text-gray-400 flex-wrap">
                                                        <span className="flex items-center gap-1">
                                                            <Users className="size-3 text-gray-400 shrink-0" />
                                                            Prof. {sch.teacher_name}
                                                        </span>
                                                        <span className="flex items-center gap-1">
                                                            <Clock className="size-3 text-gray-400 shrink-0" />
                                                            {daysText} ({sch.start_time} - {sch.end_time})
                                                        </span>
                                                        <span className="flex items-center gap-1">
                                                            <MapPin className="size-3 text-gray-400 shrink-0" />
                                                            {sch.classroom_name}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div className="text-right shrink-0">
                                                    <span className="font-mono text-xs font-bold block" style={{ color: palette.primary }}>
                                                        {fee > 0 ? `DOP $${fee.toLocaleString()}` : 'Sin costo adicional'}
                                                    </span>
                                                    <span className={`inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full mt-1 ${
                                                        sch.available_spots <= 3
                                                            ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800'
                                                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'
                                                    }`}>
                                                        {sch.available_spots} cupos libres
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })
                            ) : scheduleSearch ? (
                                <div className="py-8 text-center">
                                    <AlertCircle className="size-6 text-gray-300 dark:text-gray-600 mx-auto mb-1.5" />
                                    <p className="text-xs text-gray-500 dark:text-gray-400 font-medium">No se encontraron secciones para "{scheduleSearch}".</p>
                                    <p className="text-[11px] text-gray-400 mt-0.5">Verifica el nombre del módulo o código de sección.</p>
                                </div>
                            ) : (
                                <div className="py-8 text-center">
                                    <Search className="size-6 text-gray-300 dark:text-gray-600 mx-auto mb-1.5" />
                                    <p className="text-xs text-gray-500 dark:text-gray-400 font-medium">Comienza a escribir para buscar secciones.</p>
                                    <p className="text-[11px] text-gray-400 mt-0.5">Puedes buscar por materia, código o sección activa.</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Resumen de Selección y Confirmación */}
                    {selectedSchedule && (
                        <div 
                            className="rounded-2xl p-4 border transition-all animate-in fade-in slide-in-from-top-2 duration-150"
                            style={{ backgroundColor: `${palette.primary}06`, borderColor: `${palette.primary}30` }}
                        >
                            <div className="flex items-center justify-between mb-2 pb-2 border-b border-gray-200/50 dark:border-gray-800">
                                <span className="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                    <CheckCircle2 className="size-3.5" style={{ color: palette.primary }} />
                                    Resumen de Matrícula
                                </span>
                                <span 
                                    className="text-xs font-bold font-mono px-2.5 py-0.5 rounded-lg text-white" 
                                    style={{ backgroundColor: palette.primary }}
                                >
                                    {(selectedSchedule.registration_fee || selectedSchedule.module_price || 0) > 0 
                                        ? `DOP $${(selectedSchedule.registration_fee || selectedSchedule.module_price).toLocaleString()}` 
                                        : 'Sin Arancel'}
                                </span>
                            </div>
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <span className="text-gray-400 text-[10px] uppercase font-bold tracking-wider block">Módulo</span>
                                    <span className="font-bold text-gray-900 dark:text-white truncate block">{selectedSchedule.module_name}</span>
                                </div>
                                <div>
                                    <span className="text-gray-400 text-[10px] uppercase font-bold tracking-wider block">Sección</span>
                                    <span className="font-bold text-gray-900 dark:text-white block">{selectedSchedule.section_name}</span>
                                </div>
                                <div>
                                    <span className="text-gray-400 text-[10px] uppercase font-bold tracking-wider block">Docente</span>
                                    <span className="font-medium text-gray-700 dark:text-gray-300 truncate block">Prof. {selectedSchedule.teacher_name}</span>
                                </div>
                                <div>
                                    <span className="text-gray-400 text-[10px] uppercase font-bold tracking-wider block">Aula & Horario</span>
                                    <span className="font-medium text-gray-700 dark:text-gray-300 truncate block">{selectedSchedule.classroom_name} ({selectedSchedule.start_time})</span>
                                </div>
                            </div>
                            {student.scholarship && (
                                <p className="mt-2.5 pt-2 border-t border-gray-200/40 dark:border-gray-800 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                    <ShieldCheck className="size-3.5 shrink-0" />
                                    Aplica {Number(student.scholarship.discount_percentage)}% de descuento por Beca ({student.scholarship.name})
                                </p>
                            )}
                        </div>
                    )}
                </div>
            </Modal>

            {/* MODAL 2: CAMBIO DE SECCIÓN */}
            <Modal
                isOpen={isChangeSectionModalOpen}
                onClose={() => setIsChangeSectionModalOpen(false)}
                title="Cambio de Sección"
                subtitle={`Módulo: ${enrollmentToChange?.course_schedule?.module?.name || 'Asignatura actual'}`}
                maxWidth="max-w-lg"
                footer={
                    <div className="flex items-center justify-end gap-2.5 w-full">
                        <button
                            type="button"
                            onClick={() => setIsChangeSectionModalOpen(false)}
                            className="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onClick={handleConfirmChangeSection}
                            disabled={!selectedNewScheduleId}
                            className="rounded-xl px-5 py-2 text-xs font-bold text-white shadow-sm disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-95 transition flex items-center gap-1.5 cursor-pointer"
                            style={{ backgroundColor: palette.primary }}
                        >
                            <Check className="size-3.5" />
                            <span>Guardar Cambio</span>
                        </button>
                    </div>
                }
            >
                <div>
                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Selecciona la nueva sección disponible:
                    </label>
                    {isFetchingAlternatives ? (
                        <div className="py-6 text-center">
                            <Loader2 className="size-5 animate-spin mx-auto text-gray-400 mb-2" />
                            <p className="text-xs text-gray-400">Consultando secciones disponibles...</p>
                        </div>
                    ) : alternativeSchedules.length > 0 ? (
                        <div className="space-y-2 max-h-56 overflow-y-auto p-1 overscroll-contain">
                            {alternativeSchedules.map((alt) => {
                                const isSelected = selectedNewScheduleId === alt.id;
                                const days = [...new Set(Array.isArray(alt.days) ? alt.days : [])].join(', ');
                                return (
                                    <label
                                        key={alt.id}
                                        className={`flex items-center justify-between p-3 rounded-xl border cursor-pointer text-xs transition-all ${
                                            isSelected
                                                ? 'shadow-xs ring-1'
                                                : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-[#161622] hover:border-gray-300 dark:hover:border-gray-700'
                                        }`}
                                        style={isSelected ? {
                                            borderColor: palette.primary,
                                            backgroundColor: `${palette.primary}08`,
                                            '--tw-ring-color': palette.primary,
                                        } : {}}
                                    >
                                        <div>
                                            <p className="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                <BookOpen className="size-3.5" style={{ color: palette.primary }} />
                                                {alt.section_name}
                                            </p>
                                            <p className="text-gray-500 dark:text-gray-400 text-[11px] mt-0.5">
                                                Prof. {alt.teacher_name} • {days || 'Días a convenir'}
                                            </p>
                                        </div>
                                        <input
                                            type="radio"
                                            name="new_section"
                                            value={alt.id}
                                            checked={isSelected}
                                            onChange={() => setSelectedNewScheduleId(alt.id)}
                                            className="size-4 text-emerald-600 focus:ring-0"
                                            style={{ accentColor: palette.primary }}
                                        />
                                    </label>
                                );
                            })}
                        </div>
                    ) : (
                        <div className="p-4 rounded-xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-center">
                            <AlertCircle className="size-5 text-amber-600 dark:text-amber-400 mx-auto mb-1" />
                            <p className="text-xs text-amber-700 dark:text-amber-300 font-medium">
                                No hay otras secciones con cupos disponibles para este módulo.
                            </p>
                        </div>
                    )}
                </div>
            </Modal>

            {/* MODAL 3: CONFIRMAR ANULACIÓN */}
            <Modal
                isOpen={isUnenrollModalOpen}
                onClose={() => setIsUnenrollModalOpen(false)}
                title="¿Anular Inscripción Pendiente?"
                maxWidth="max-w-md"
                footer={
                    <div className="flex items-center justify-end gap-2.5 w-full">
                        <button
                            type="button"
                            onClick={() => setIsUnenrollModalOpen(false)}
                            className="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onClick={handleUnenroll}
                            className="rounded-xl px-5 py-2 text-xs font-bold text-white shadow-sm bg-red-600 hover:bg-red-700 transition flex items-center gap-1.5 cursor-pointer"
                        >
                            Anular Inscripción
                        </button>
                    </div>
                }
            >
                <div className="text-center py-2">
                    <div className="mx-auto flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 mb-3">
                        <AlertTriangle className="size-6" />
                    </div>
                    <p className="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Se cancelará la matrícula en{' '}
                        <strong className="text-gray-900 dark:text-white">
                            {enrollmentToCancel?.course_schedule?.module?.name}
                        </strong>{' '}
                        y se eliminará el cargo por arancel pendiente de cobro.
                    </p>
                </div>
            </Modal>

            {/* MODAL 4: CONFIRMAR RETIRO ACADÉMICO */}
            <Modal
                isOpen={isWithdrawModalOpen}
                onClose={() => setIsWithdrawModalOpen(false)}
                title="¿Retirar Asignatura?"
                maxWidth="max-w-md"
                footer={
                    <div className="flex items-center justify-end gap-2.5 w-full">
                        <button
                            type="button"
                            onClick={() => setIsWithdrawModalOpen(false)}
                            className="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onClick={handleWithdraw}
                            className="rounded-xl px-5 py-2 text-xs font-bold text-white shadow-sm bg-amber-600 hover:bg-amber-700 transition flex items-center gap-1.5 cursor-pointer"
                        >
                            Confirmar Retiro
                        </button>
                    </div>
                }
            >
                <div className="text-center py-2">
                    <div className="mx-auto flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400 mb-3">
                        <AlertCircle className="size-6" />
                    </div>
                    <p className="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        El estudiante quedará marcado con estado <strong className="text-gray-900 dark:text-white">Retirado</strong> en{' '}
                        <strong className="text-gray-900 dark:text-white">
                            {enrollmentToWithdraw?.course_schedule?.module?.name}
                        </strong>.
                    </p>
                </div>
            </Modal>

            {/* MODAL 5: EDITAR FICHA DE ESTUDIANTE */}
            <Modal
                isOpen={isEditModalOpen}
                onClose={() => setIsEditModalOpen(false)}
                title="Editar Ficha del Estudiante"
                subtitle="Actualización de datos personales, académicos y de contacto."
                maxWidth="max-w-3xl"
                footer={
                    <div className="flex items-center justify-end gap-2.5 w-full">
                        <button
                            type="button"
                            onClick={() => setIsEditModalOpen(false)}
                            className="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            form="edit-student-profile-form"
                            disabled={editForm.processing}
                            className="rounded-xl px-5 py-2 text-xs font-bold text-white shadow-sm disabled:opacity-50 hover:opacity-95 transition flex items-center gap-1.5 cursor-pointer"
                            style={{ backgroundColor: palette.primary }}
                        >
                            {editForm.processing ? (
                                <>
                                    <Loader2 className="size-3.5 animate-spin" />
                                    <span>Guardando...</span>
                                </>
                            ) : (
                                <>
                                    <Check className="size-3.5" />
                                    <span>Guardar Cambios</span>
                                </>
                            )}
                        </button>
                    </div>
                }
            >
                <form id="edit-student-profile-form" onSubmit={handleSaveProfile} className="space-y-4">
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Nombres *
                            </label>
                            <input
                                type="text"
                                value={editForm.data.first_name}
                                onChange={(e) => editForm.setData('first_name', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Apellidos *
                            </label>
                            <input
                                type="text"
                                value={editForm.data.last_name}
                                onChange={(e) => editForm.setData('last_name', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Cédula *
                            </label>
                            <input
                                type="text"
                                value={editForm.data.cedula}
                                onChange={(e) => editForm.setData('cedula', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Correo Electrónico *
                            </label>
                            <input
                                type="email"
                                value={editForm.data.email}
                                onChange={(e) => editForm.setData('email', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono Celular *
                            </label>
                            <input
                                type="text"
                                value={editForm.data.mobile_phone}
                                onChange={(e) => editForm.setData('mobile_phone', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono Residencial
                            </label>
                            <input
                                type="text"
                                value={editForm.data.home_phone}
                                onChange={(e) => editForm.setData('home_phone', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                            />
                        </div>
                        <div className="sm:col-span-2">
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Dirección
                            </label>
                            <input
                                type="text"
                                value={editForm.data.address}
                                onChange={(e) => editForm.setData('address', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Ciudad
                            </label>
                            <input
                                type="text"
                                value={editForm.data.city}
                                onChange={(e) => editForm.setData('city', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Carrera Principal
                            </label>
                            <select
                                value={editForm.data.course_id}
                                onChange={(e) => editForm.setData('course_id', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                            >
                                <option value="">Seleccione carrera...</option>
                                {allCourses.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Beca Asignada
                            </label>
                            <select
                                value={editForm.data.scholarship_id}
                                onChange={(e) => editForm.setData('scholarship_id', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                            >
                                <option value="">Ninguna</option>
                                {allScholarships.map((s) => (
                                    <option key={s.id} value={s.id}>{s.name} ({Number(s.discount_percentage)}%)</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Estado Académico
                            </label>
                            <select
                                value={editForm.data.status}
                                onChange={(e) => editForm.setData('status', e.target.value)}
                                className="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 py-2 px-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 transition"
                                style={{ '--tw-ring-color': palette.primary }}
                            >
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Suspendido">Suspendido</option>
                                <option value="Egresado">Egresado</option>
                            </select>
                        </div>
                    </div>
                </form>
            </Modal>
        </AuthenticatedLayout>
    );
}
