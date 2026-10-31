import React, { useState, useMemo } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Modal from '@/components/ui/Modal';
import { ProgressFoldButton } from '@/components/godui';
import {
    Activity,
    Users,
    Calendar,
    DollarSign,
    UserPlus,
    Check,
    Loader2,
    X,
    AlertTriangle,
    KeyRound,
    Printer,
    CheckCircle2,
    AlertCircle,
    Search
} from 'lucide-react';

export default function StudentsIndex({
    students,
    filters,
    metrics,
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
    const [search, setSearch] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.status || 'all');
    const [courseFilter, setCourseFilter] = useState(filters?.course_id || 'all');

    // Modales
    const [isFormModalOpen, setIsFormModalOpen] = useState(false);
    const [editingStudent, setEditingStudent] = useState(null);
    const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);
    const [studentToDelete, setStudentToDelete] = useState(null);
    const [isPinModalOpen, setIsPinModalOpen] = useState(false);
    const [pinInfo, setPinInfo] = useState({ name: '', pin: '', id: null });

    // Estado de consulta JCE Cédula
    const [isSearchingCedula, setIsSearchingCedula] = useState(false);
    const [cedulaStatus, setCedulaStatus] = useState(null); // { type: 'success' | 'error', message: '' }

    // Formulario de Estudiante (Creación y Edición)
    const studentForm = useForm({
        first_name: '',
        last_name: '',
        cedula: '',
        email: '',
        mobile_phone: '',
        home_phone: '',
        address: '',
        city: '',
        sector: '',
        birth_date: '',
        gender: '',
        nationality: 'Dominicana',
        how_found: '',
        course_id: '',
        scholarship_id: '',
        is_minor: false,
        tutor_name: '',
        tutor_cedula: '',
        tutor_phone: '',
        tutor_relationship: '',
        password: '',
        status: 'Activo',
        rnc: '',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(
            '/admin/students',
            {
                search,
                status: statusFilter,
                course_id: courseFilter,
            },
            { preserveState: true, replace: true }
        );
    };

    const handleClearFilters = () => {
        setSearch('');
        setStatusFilter('all');
        setCourseFilter('all');
        router.get('/admin/students', {}, { preserveState: true });
    };

    // Abrir Modal de Creación
    const openCreateModal = () => {
        setEditingStudent(null);
        setCedulaStatus(null);
        studentForm.reset();
        studentForm.clearErrors();
        studentForm.setData({
            first_name: '',
            last_name: '',
            cedula: '',
            email: '',
            mobile_phone: '',
            home_phone: '',
            address: '',
            city: '',
            sector: '',
            birth_date: '',
            gender: '',
            nationality: 'Dominicana',
            how_found: '',
            course_id: '',
            scholarship_id: '',
            is_minor: false,
            tutor_name: '',
            tutor_cedula: '',
            tutor_phone: '',
            tutor_relationship: '',
            password: '',
            status: 'Activo',
            rnc: '',
        });
        setIsFormModalOpen(true);
    };

    // Abrir Modal de Edición
    const openEditModal = (student) => {
        setEditingStudent(student);
        setCedulaStatus(null);
        studentForm.clearErrors();
        studentForm.setData({
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
            password: '',
            status: student.status || 'Activo',
            rnc: student.rnc || '',
        });
        setIsFormModalOpen(true);
    };

    // Verificación en vivo de cédula en padrón electoral JCE
    const handleLookupCedula = async () => {
        const cleanCedula = (studentForm.data.cedula || '').replace(/[^0-9]/g, '');
        if (cleanCedula.length !== 11) {
            setCedulaStatus({
                type: 'error',
                message: 'La cédula debe contener exactamente 11 dígitos.',
            });
            return;
        }

        setIsSearchingCedula(true);
        setCedulaStatus(null);

        try {
            const res = await fetch(`/admin/students/lookup-cedula?cedula=${cleanCedula}`);
            const data = await res.json();

            if (data.found && data.citizen) {
                const c = data.citizen;
                if (studentForm.data.is_minor) {
                    const tutorName = [c.nombres, c.apellido1, c.apellido2].filter(Boolean).join(' ');
                    studentForm.setData((prev) => ({
                        ...prev,
                        tutor_name: tutorName,
                        tutor_cedula: cleanCedula,
                    }));
                    setCedulaStatus({
                        type: 'success',
                        message: `Tutor verificado en JCE: ${tutorName}`,
                    });
                } else {
                    const firstName = c.nombres || '';
                    const lastName = [c.apellido1, c.apellido2].filter(Boolean).join(' ');
                    let gender = '';
                    if (c.sexo) {
                        const s = c.sexo.toUpperCase();
                        gender = s === 'M' || s.startsWith('MASC') ? 'Masculino' : s === 'F' || s.startsWith('FEM') ? 'Femenino' : '';
                    }

                    let birthDate = '';
                    if (c.fechaNacimiento) {
                        try {
                            birthDate = new Date(c.fechaNacimiento).toISOString().split('T')[0];
                        } catch (e) {}
                    }

                    studentForm.setData((prev) => ({
                        ...prev,
                        first_name: firstName,
                        last_name: lastName,
                        gender: gender || prev.gender,
                        birth_date: birthDate || prev.birth_date,
                        nationality: 'Dominicana',
                    }));

                    setCedulaStatus({
                        type: 'success',
                        message: `Ciudadano validado en JCE: ${firstName} ${lastName}`,
                    });
                }
            } else {
                setCedulaStatus({
                    type: 'error',
                    message: data.error || 'Cédula no encontrada en el padrón electoral oficial.',
                });
            }
        } catch (err) {
            setCedulaStatus({
                type: 'error',
                message: 'Error al conectar con el servicio de verificación electoral.',
            });
        } finally {
            setIsSearchingCedula(false);
        }
    };

    // Guardar (Crear o Actualizar)
    const handleSaveStudent = (e) => {
        e.preventDefault();
        if (editingStudent) {
            studentForm.put(`/admin/students/${editingStudent.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    setIsFormModalOpen(false);
                },
            });
        } else {
            studentForm.post('/admin/students', {
                preserveScroll: true,
                onSuccess: () => {
                    setIsFormModalOpen(false);
                },
            });
        }
    };

    // Resetear PIN de Kiosco
    const handleResetKioskPin = (student) => {
        router.post(
            `/admin/students/${student.id}/reset-kiosk-pin`,
            {},
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    // Si se reseteó, podemos mostrar el PIN temporal
                    setPinInfo({
                        name: student.full_name || `${student.first_name} ${student.last_name}`,
                        pin: student.user?.kiosk_pin || 'Actualizado',
                        id: student.id,
                    });
                    setIsPinModalOpen(true);
                },
            }
        );
    };

    // Confirmar eliminación
    const confirmDelete = (student) => {
        setStudentToDelete(student);
        setIsDeleteModalOpen(true);
    };

    const handleDelete = () => {
        if (!studentToDelete) return;
        router.delete(`/admin/students/${studentToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsDeleteModalOpen(false);
                setStudentToDelete(null);
            },
        });
    };

    return (
        <AuthenticatedLayout title="Gestión de Estudiantes">
            <Head title="Directorio de Estudiantes — SGA" />

            <div className="space-y-6">
                {/* Encabezado y Acción Principal */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Directorio de Estudiantes
                        </h1>
                        
                    </div>
                    <div className="flex items-center gap-3">
                        <ProgressFoldButton
                            variant="primary"
                            onClick={openCreateModal}
                        >
                            <span className="inline-flex items-center gap-2">
                                <UserPlus className="size-4" />
                                <span>Nuevo Estudiante</span>
                            </span>
                        </ProgressFoldButton>
                    </div>
                </div>

                {/* Métricas Principales (KPIs) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-[var(--brand-primary)] dark:bg-blue-900/20 dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Total Alumnos
                                </p>
                                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {metrics?.total?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Estudiantes Activos
                                </p>
                                <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                    {metrics?.active?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Becados Oficiales
                                </p>
                                <p className="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    {metrics?.scholarships?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <div className="flex items-center gap-4">
                            <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Con Cobros Pendientes
                                </p>
                                <p className="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                    {metrics?.with_debt?.toLocaleString() ?? 0}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Barra de Filtros y Búsqueda */}
                <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <form onSubmit={handleSearch} className="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                        <div className="sm:col-span-5 relative">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar por nombre, cédula, matrícula, teléfono o email..."
                                className="w-full rounded-xl border-0 py-2.5 pl-10 pr-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900/50 dark:text-white dark:ring-gray-700"
                            />
                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <div className="sm:col-span-3">
                            <select
                                value={statusFilter}
                                onChange={(e) => setStatusFilter(e.target.value)}
                                className="w-full rounded-xl border-0 py-2.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900/50 dark:text-white dark:ring-gray-700"
                            >
                                <option value="all">Todos los estados</option>
                                <option value="Activo">Activos</option>
                                <option value="Inactivo">Inactivos</option>
                                <option value="Suspendido">Suspendidos</option>
                            </select>
                        </div>

                        <div className="sm:col-span-2">
                            <select
                                value={courseFilter}
                                onChange={(e) => setCourseFilter(e.target.value)}
                                className="w-full rounded-xl border-0 py-2.5 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900/50 dark:text-white dark:ring-gray-700 truncate"
                            >
                                <option value="all">Todas las carreras</option>
                                {allCourses.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="sm:col-span-2 flex items-center gap-2">
                            <button
                                type="submit"
                                className="w-full rounded-xl bg-[var(--brand-primary)] py-2.5 px-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark transition"
                            >
                                Filtrar
                            </button>
                            {(search || statusFilter !== 'all' || courseFilter !== 'all') && (
                                <button
                                    type="button"
                                    onClick={handleClearFilters}
                                    title="Limpiar filtros"
                                    className="rounded-xl border border-gray-300 p-2.5 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                >
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            )}
                        </div>
                    </form>
                </div>

                {/* Tabla de Resultados */}
                <div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-left text-sm">
                            <thead className="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th className="py-3.5 pl-6 pr-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Estudiante
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Cédula / Matrícula
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Contacto
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Carrera / Beca
                                    </th>
                                    <th className="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Estado
                                    </th>
                                    <th className="py-3.5 pl-3 pr-6 text-right text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-gray-800">
                                {students?.data && students.data.length > 0 ? (
                                    students.data.map((student) => {
                                        const initials = `${student.first_name?.[0] || ''}${student.last_name?.[0] || ''}`.toUpperCase();
                                        return (
                                            <tr
                                                key={student.id}
                                                className="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors"
                                            >
                                                {/* Estudiante & Avatar */}
                                                <td className="py-4 pl-6 pr-3">
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[var(--brand-primary)]/10 text-[var(--brand-primary)] font-bold text-sm dark:bg-blue-900/30 dark:text-blue-300">
                                                            {initials || 'EST'}
                                                        </div>
                                                        <div>
                                                            <Link
                                                                href={`/admin/students/profile/${student.id}`}
                                                                className="font-semibold text-gray-900 hover:text-[var(--brand-primary)] dark:text-white dark:hover:text-blue-400 transition"
                                                            >
                                                                {student.first_name} {student.last_name}
                                                            </Link>
                                                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                                                {student.email}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {/* Cédula & Matrícula */}
                                                <td className="px-3 py-4">
                                                    <div className="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                                        {student.cedula || 'Sin Cédula'}
                                                    </div>
                                                    <span className="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                        {student.student_code || `ID #${student.id}`}
                                                    </span>
                                                </td>

                                                {/* Contacto */}
                                                <td className="px-3 py-4">
                                                    <div className="text-xs text-gray-900 dark:text-white font-medium">
                                                        {student.mobile_phone || 'Sin móvil'}
                                                    </div>
                                                    <div className="text-[11px] text-gray-500 dark:text-gray-400">
                                                        {student.city || student.sector || 'República Dominicana'}
                                                    </div>
                                                </td>

                                                {/* Carrera & Beca */}
                                                <td className="px-3 py-4">
                                                    <div className="text-xs font-medium text-gray-900 dark:text-white truncate max-w-[180px]">
                                                        {student.course?.name || 'General / Técnico'}
                                                    </div>
                                                    {student.scholarship ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 mt-1">
                                                            Beca {student.scholarship.name} ({Number(student.scholarship.discount_percentage)}%)
                                                        </span>
                                                    ) : (
                                                        <span className="text-[11px] text-gray-400">Regular</span>
                                                    )}
                                                </td>

                                                {/* Estado */}
                                                <td className="px-3 py-4">
                                                    <span
                                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                            student.status === 'Activo'
                                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'
                                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                                        }`}
                                                    >
                                                        {student.status || 'Activo'}
                                                    </span>
                                                </td>

                                                {/* Acciones */}
                                                <td className="py-4 pl-3 pr-6 text-right">
                                                    <div className="flex items-center justify-end gap-1.5">
                                                        {/* Ver Expediente 360° */}
                                                        <Link
                                                            href={`/admin/students/profile/${student.id}`}
                                                            title="Ver Expediente 360°"
                                                            className="rounded-lg p-1.5 text-gray-500 hover:bg-blue-50 hover:text-[var(--brand-primary)] dark:hover:bg-blue-900/20 dark:hover:text-blue-400 transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </Link>

                                                        {/* Editar Ficha */}
                                                        <button
                                                            type="button"
                                                            onClick={() => openEditModal(student)}
                                                            title="Editar datos"
                                                            className="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>

                                                        {/* Regenerar PIN Kiosco */}
                                                        <button
                                                            type="button"
                                                            onClick={() => handleResetKioskPin(student)}
                                                            title={`Regenerar PIN Kiosco (Actual: ${student.user?.kiosk_pin || 'Sin PIN'})`}
                                                            className="rounded-lg p-1.5 text-amber-500 hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/20 transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                                            </svg>
                                                        </button>

                                                        {/* Imprimir Carnet / Kiosk PDF */}
                                                        <a
                                                            href={`/admin/students/${student.id}/kiosk-pin-pdf`}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            title="Imprimir Carnet / PIN Kiosco"
                                                            className="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                            </svg>
                                                        </a>

                                                        {/* Eliminar */}
                                                        <button
                                                            type="button"
                                                            onClick={() => confirmDelete(student)}
                                                            title="Eliminar Estudiante"
                                                            className="rounded-lg p-1.5 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 transition"
                                                        >
                                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div className="flex flex-col items-center justify-center">
                                                <svg className="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                                <p className="text-base font-medium">No se encontraron estudiantes</p>
                                                <p className="text-xs text-gray-400 mt-1">
                                                    Intenta ajustar los términos de búsqueda o registra un nuevo estudiante.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Paginación */}
                    {students?.links && students.links.length > 3 && (
                        <div className="flex items-center justify-between border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-[#161622]">
                            <div className="text-xs text-gray-500 dark:text-gray-400">
                                Mostrando del <span className="font-semibold">{students.from || 0}</span> al{' '}
                                <span className="font-semibold">{students.to || 0}</span> de{' '}
                                <span className="font-semibold">{students.total}</span> estudiantes
                            </div>
                            <div className="flex items-center gap-1">
                                {students.links.map((link, idx) => (
                                    <Link
                                        key={idx}
                                        href={link.url || '#'}
                                        preserveScroll
                                        className={`px-3 py-1.5 text-xs rounded-lg font-medium transition ${
                                            link.active
                                                ? 'bg-[var(--brand-primary)] text-white shadow-sm'
                                                : link.url
                                                ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                                                : 'text-gray-300 pointer-events-none dark:text-gray-600'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal de Crear / Editar Estudiante */}
            <Modal
                isOpen={isFormModalOpen}
                onClose={() => setIsFormModalOpen(false)}
                title={editingStudent ? `Editar Estudiante: ${editingStudent.full_name || editingStudent.first_name}` : 'Registrar Nuevo Estudiante'}
                subtitle="Completa la ficha técnica del estudiante y genera automáticamente su cuenta de usuario."
                maxWidth="max-w-4xl"
                footer={
                    <div className="flex items-center justify-end gap-3 w-full">
                        <button
                            type="button"
                            onClick={() => setIsFormModalOpen(false)}
                            className="rounded-xl border border-gray-300 dark:border-gray-700 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            form="student-form-modal"
                            disabled={studentForm.processing}
                            className="rounded-xl px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95 disabled:opacity-50 transition flex items-center gap-2 cursor-pointer"
                            style={{ backgroundColor: palette.primary }}
                        >
                            {studentForm.processing ? (
                                <>
                                    <Loader2 className="size-4 animate-spin" />
                                    <span>Guardando...</span>
                                </>
                            ) : (
                                <>
                                    <Check className="size-4" />
                                    <span>{editingStudent ? 'Actualizar Ficha' : 'Registrar Estudiante'}</span>
                                </>
                            )}
                        </button>
                    </div>
                }
            >
                <form id="student-form-modal" onSubmit={handleSaveStudent} className="space-y-6 py-1">
                    {/* Bloque Cédula y Validación JCE */}
                            <div className="rounded-2xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/30 dark:bg-blue-950/20">
                                <label className="block text-xs font-semibold uppercase tracking-wider text-blue-900 dark:text-blue-300">
                                    Cédula de Identidad (Padrón Electoral JCE) *
                                </label>
                                <div className="mt-2 flex gap-3">
                                    <input
                                        type="text"
                                        value={studentForm.data.cedula}
                                        onChange={(e) => studentForm.setData('cedula', e.target.value)}
                                        placeholder="Ej: 402-1482708-7"
                                        className="flex-1 rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                    <button
                                        type="button"
                                        onClick={handleLookupCedula}
                                        disabled={isSearchingCedula || !studentForm.data.cedula}
                                        style={{ backgroundColor: palette.primary }}
                                        className="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition disabled:opacity-50 transition"
                                    >
                                        {isSearchingCedula ? (
                                            <>
                                                <svg className="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                                </svg>
                                                Verificando...
                                            </>
                                        ) : (
                                            <>
                                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Validar JCE
                                            </>
                                        )}
                                    </button>
                                </div>
                                {cedulaStatus && (
                                    <div
                                        className={`mt-2 text-xs font-medium rounded-lg p-2 ${
                                            cedulaStatus.type === 'success'
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                : 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300'
                                        }`}
                                    >
                                        {cedulaStatus.message}
                                    </div>
                                )}
                                {studentForm.errors.cedula && (
                                    <p className="mt-1 text-xs text-red-500">{studentForm.errors.cedula}</p>
                                )}
                            </div>

                            {/* Datos Personales */}
                            <div>
                                <h4 className="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                                    Información Personal
                                </h4>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Nombres *
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.first_name}
                                            onChange={(e) => studentForm.setData('first_name', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                        {studentForm.errors.first_name && (
                                            <p className="mt-1 text-xs text-red-500">{studentForm.errors.first_name}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Apellidos *
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.last_name}
                                            onChange={(e) => studentForm.setData('last_name', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                        {studentForm.errors.last_name && (
                                            <p className="mt-1 text-xs text-red-500">{studentForm.errors.last_name}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Fecha de Nacimiento *
                                        </label>
                                        <input
                                            type="date"
                                            value={studentForm.data.birth_date}
                                            onChange={(e) => studentForm.setData('birth_date', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                        {studentForm.errors.birth_date && (
                                            <p className="mt-1 text-xs text-red-500">{studentForm.errors.birth_date}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Género
                                        </label>
                                        <select
                                            value={studentForm.data.gender}
                                            onChange={(e) => studentForm.setData('gender', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        >
                                            <option value="">Seleccione...</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Nacionalidad
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.nationality}
                                            onChange={(e) => studentForm.setData('nationality', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            RNC (Opcional)
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.rnc}
                                            onChange={(e) => studentForm.setData('rnc', e.target.value)}
                                            placeholder="Para facturación fiscal"
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Contacto y Dirección */}
                            <div>
                                <h4 className="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                                    Contacto y Localización
                                </h4>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Correo Electrónico *
                                        </label>
                                        <input
                                            type="email"
                                            value={studentForm.data.email}
                                            onChange={(e) => studentForm.setData('email', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                        {studentForm.errors.email && (
                                            <p className="mt-1 text-xs text-red-500">{studentForm.errors.email}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Teléfono Celular *
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.mobile_phone}
                                            onChange={(e) => studentForm.setData('mobile_phone', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                        {studentForm.errors.mobile_phone && (
                                            <p className="mt-1 text-xs text-red-500">{studentForm.errors.mobile_phone}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Teléfono Residencial
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.home_phone}
                                            onChange={(e) => studentForm.setData('home_phone', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>

                                    <div className="sm:col-span-2">
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Dirección Física
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.address}
                                            onChange={(e) => studentForm.setData('address', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Ciudad / Municipio
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.city}
                                            onChange={(e) => studentForm.setData('city', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Carrera, Beca y Fuente de Captación */}
                            <div>
                                <h4 className="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                                    Programa Académico y Beca
                                </h4>
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Carrera o Curso Principal
                                        </label>
                                        <select
                                            value={studentForm.data.course_id}
                                            onChange={(e) => studentForm.setData('course_id', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700 truncate"
                                        >
                                            <option value="">Seleccione carrera o programa...</option>
                                            {allCourses.map((c) => (
                                                <option key={c.id} value={c.id}>
                                                    {c.name} ({c.code})
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Beca Asignada
                                        </label>
                                        <select
                                            value={studentForm.data.scholarship_id}
                                            onChange={(e) => studentForm.setData('scholarship_id', e.target.value)}
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        >
                                            <option value="">Ninguna (Pago regular 100%)</option>
                                            {allScholarships.map((s) => (
                                                <option key={s.id} value={s.id}>
                                                    {s.name} ({Number(s.discount_percentage)}% Desc.)
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            ¿Cómo nos conoció?
                                        </label>
                                        <input
                                            type="text"
                                            value={studentForm.data.how_found}
                                            onChange={(e) => studentForm.setData('how_found', e.target.value)}
                                            placeholder="Redes, Feria, Recomendación..."
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Sección Tutor para Menores de Edad */}
                            <div className="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                <div className="flex items-center gap-3">
                                    <input
                                        type="checkbox"
                                        id="is_minor_cb"
                                        checked={studentForm.data.is_minor}
                                        onChange={(e) => studentForm.setData('is_minor', e.target.checked)}
                                        className="h-4 w-4 rounded border-gray-300 text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"
                                    />
                                    <label htmlFor="is_minor_cb" className="text-sm font-semibold text-gray-900 dark:text-white">
                                        ¿El estudiante es menor de edad? (Requiere información del tutor legal)
                                    </label>
                                </div>

                                {studentForm.data.is_minor && (
                                    <div className="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                                        <div>
                                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Nombre del Tutor *
                                            </label>
                                            <input
                                                type="text"
                                                value={studentForm.data.tutor_name}
                                                onChange={(e) => studentForm.setData('tutor_name', e.target.value)}
                                                className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                            />
                                            {studentForm.errors.tutor_name && (
                                                <p className="mt-1 text-xs text-red-500">{studentForm.errors.tutor_name}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Teléfono del Tutor *
                                            </label>
                                            <input
                                                type="text"
                                                value={studentForm.data.tutor_phone}
                                                onChange={(e) => studentForm.setData('tutor_phone', e.target.value)}
                                                className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                            />
                                            {studentForm.errors.tutor_phone && (
                                                <p className="mt-1 text-xs text-red-500">{studentForm.errors.tutor_phone}</p>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Cédula del Tutor
                                            </label>
                                            <input
                                                type="text"
                                                value={studentForm.data.tutor_cedula}
                                                onChange={(e) => studentForm.setData('tutor_cedula', e.target.value)}
                                                className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Parentesco
                                            </label>
                                            <input
                                                type="text"
                                                value={studentForm.data.tutor_relationship}
                                                onChange={(e) => studentForm.setData('tutor_relationship', e.target.value)}
                                                placeholder="Madre, Padre, Tutor..."
                                                className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Seguridad y Contraseña */}
                            <div className="rounded-2xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                                <h4 className="text-sm font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1">
                                    Seguridad y Credenciales
                                </h4>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                    {editingStudent
                                        ? 'Deja el campo en blanco si no deseas cambiar la contraseña del estudiante.'
                                        : 'Por defecto, la contraseña inicial será el número de cédula sin guiones.'}
                                </p>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            {editingStudent ? 'Nueva Contraseña (Opcional)' : 'Contraseña Personalizada (Opcional)'}
                                        </label>
                                        <input
                                            type="password"
                                            value={studentForm.data.password}
                                            onChange={(e) => studentForm.setData('password', e.target.value)}
                                            placeholder="Mínimo 8 caracteres"
                                            className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                        />
                                        {studentForm.errors.password && (
                                            <p className="mt-1 text-xs text-red-500">{studentForm.errors.password}</p>
                                        )}
                                    </div>
                                    {editingStudent && (
                                        <div>
                                            <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Estado Académico
                                            </label>
                                            <select
                                                value={studentForm.data.status}
                                                onChange={(e) => studentForm.setData('status', e.target.value)}
                                                className="w-full rounded-xl border-0 py-2.5 px-3.5 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-[var(--brand-primary)] dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                            >
                                                <option value="Activo">Activo</option>
                                                <option value="Inactivo">Inactivo</option>
                                                <option value="Suspendido">Suspendido</option>
                                                <option value="Egresado">Egresado</option>
                                            </select>
                                        </div>
                                    )}
                                </div>
                            </div>
                </form>
            </Modal>

            {/* Modal de PIN Kiosco Regenerado */}
            <Modal
                isOpen={isPinModalOpen}
                onClose={() => setIsPinModalOpen(false)}
                title="PIN de Kiosco Regenerado"
                subtitle={`Nuevo código de acceso para ${pinInfo.name}`}
                maxWidth="max-w-md"
                footer={
                    <div className="flex items-center justify-end gap-3 w-full">
                        <a
                            href={`/admin/students/${pinInfo.id}/kiosk-pin-pdf`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition text-center flex items-center justify-center gap-1.5"
                        >
                            <Printer className="size-3.5" />
                            <span>Imprimir Hoja / Carnet</span>
                        </a>
                        <button
                            type="button"
                            onClick={() => setIsPinModalOpen(false)}
                            className="flex-1 rounded-xl py-2.5 text-xs font-bold text-white shadow-sm hover:opacity-95 transition flex items-center justify-center gap-1.5 cursor-pointer"
                            style={{ backgroundColor: palette.primary }}
                        >
                            <Check className="size-3.5" />
                            <span>Entendido</span>
                        </button>
                    </div>
                }
            >
                <div className="text-center py-2">
                    <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 mb-3">
                        <KeyRound className="size-7" />
                    </div>
                    <div className="my-4 rounded-2xl bg-gray-50 py-4 px-6 border border-dashed border-gray-300 dark:bg-gray-900/60 dark:border-gray-700">
                        <div className="font-mono text-3xl font-extrabold tracking-widest text-[var(--brand-primary)] dark:text-amber-400">
                            {pinInfo.pin}
                        </div>
                        <span className="text-[11px] text-gray-400">PIN de 4 dígitos para autoservicio</span>
                    </div>
                </div>
            </Modal>

            {/* Modal de Confirmación de Eliminación */}
            <Modal
                isOpen={isDeleteModalOpen}
                onClose={() => setIsDeleteModalOpen(false)}
                title="¿Eliminar Estudiante?"
                maxWidth="max-w-md"
                footer={
                    <div className="flex items-center justify-end gap-3 w-full">
                        <button
                            type="button"
                            onClick={() => setIsDeleteModalOpen(false)}
                            className="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition text-center cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onClick={handleDelete}
                            className="flex-1 rounded-xl bg-red-600 hover:bg-red-700 py-2.5 text-sm font-bold text-white shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            Sí, Eliminar
                        </button>
                    </div>
                }
            >
                <div className="text-center py-2">
                    <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 mb-3">
                        <AlertTriangle className="size-6" />
                    </div>
                    <p className="text-xs text-center text-gray-500 dark:text-gray-400 leading-relaxed">
                        Estás a punto de eliminar la ficha de{' '}
                        <span className="font-semibold text-gray-900 dark:text-white">
                            {studentToDelete?.first_name} {studentToDelete?.last_name}
                        </span>
                        . Esta acción es irreversible y eliminará su cuenta de usuario asociada.
                    </p>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
