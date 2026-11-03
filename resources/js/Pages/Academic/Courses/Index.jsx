import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ProgressFoldButton } from '@/components/godui';
import { Activity, BookOpen, Calendar, DollarSign } from 'lucide-react';
import {
    Plus,
    Globe,
    GraduationCap,
    Link2,
    ExternalLink,
    RefreshCw,
    Loader2,
    Check,
    X,
    AlertCircle,
    Unlink
} from 'lucide-react';

export default function CoursesIndex({
    courses,
    selectedCourse,
    modules = [],
    selectedModule,
    schedules = [],
    teachers = [],
    classrooms = [],
    filters,
    metrics,
}) {
    const [search, setSearch] = useState(filters?.search || '');

    // Modales de Curso
    const [isCourseModalOpen, setIsCourseModalOpen] = useState(false);
    const [editingCourse, setEditingCourse] = useState(null);

    // Modales de Módulo
    const [isModuleModalOpen, setIsModuleModalOpen] = useState(false);
    const [editingModule, setEditingModule] = useState(null);

    // Modales de Sección
    const [isScheduleModalOpen, setIsScheduleModalOpen] = useState(false);
    const [editingSchedule, setEditingSchedule] = useState(null);

    // Modal de Confirmación de Borrado
    const [deleteConfig, setDeleteConfig] = useState(null);

    // --- INTEGRACIÓN: MODAL ENLACE CURSO WP ---
    const [isWpModalOpen, setIsWpModalOpen] = useState(false);
    const [linkingWpCourse, setLinkingWpCourse] = useState(null);
    const [wpCoursesList, setWpCoursesList] = useState([]);
    const [loadingWpCourses, setLoadingWpCourses] = useState(false);
    const [selectedWpCourseId, setSelectedWpCourseId] = useState('');
    const [savingWpCourse, setSavingWpCourse] = useState(false);
    const [wpError, setWpError] = useState('');

    // --- INTEGRACIÓN: MODAL ENLACE MOODLE (CASCADA 3 NIVELES) ---
    const [isMoodleModalOpen, setIsMoodleModalOpen] = useState(false);
    const [moodleLinkingType, setMoodleLinkingType] = useState('course'); // 'course'|'module'|'schedule'
    const [moodleLinkingId, setMoodleLinkingId] = useState(null);
    const [moodleLinkingTitle, setMoodleLinkingTitle] = useState('');
    const [moodleCoursesList, setMoodleCoursesList] = useState([]);
    const [loadingMoodleCourses, setLoadingMoodleCourses] = useState(false);
    const [selectedMoodleCourseId, setSelectedMoodleCourseId] = useState('');
    const [savingMoodle, setSavingMoodle] = useState(false);
    const [moodleError, setMoodleError] = useState('');

    // --- INTEGRACIÓN: MODAL ENLACE SECCIÓN HORARIO WP ---
    const [isWpScheduleModalOpen, setIsWpScheduleModalOpen] = useState(false);
    const [linkingSchedule, setLinkingSchedule] = useState(null);
    const [wpSchedulesList, setWpSchedulesList] = useState([]);
    const [loadingWpSchedules, setLoadingWpSchedules] = useState(false);
    const [selectedWpScheduleString, setSelectedWpScheduleString] = useState('');
    const [savingWpSchedule, setSavingWpSchedule] = useState(false);
    const [scheduleWpError, setScheduleWpError] = useState('');

    // Forms
    const courseForm = useForm({
        name: '',
        code: '',
        registration_fee: 0,
        monthly_fee: 0,
        is_sequential: false,
        status: 'Activo',
    });

    const moduleForm = useForm({
        name: '',
        code: '',
        price: 0,
        duration_hours: 40,
        order: 0,
    });

    const scheduleForm = useForm({
        section_name: '',
        teacher_id: '',
        classroom_id: '',
        days_of_week: [],
        start_time: '18:00',
        end_time: '20:00',
        start_date: new Date().toISOString().split('T')[0],
        end_date: new Date(Date.now() + 60 * 86400000).toISOString().split('T')[0],
        capacity: 30,
        modality: 'Presencial',
        status: 'Activo',
    });

    const handleSearch = (e) => {
        e.preventDefault();
        router.get('/admin/courses', { search }, { preserveState: true, replace: true });
    };

    const handleSelectCourse = (courseId) => {
        router.get('/admin/courses', {
            search,
            selectedCourse: courseId,
        }, { preserveState: true });
    };

    const handleSelectModule = (moduleId) => {
        router.get('/admin/courses', {
            search,
            selectedCourse: selectedCourse?.id,
            selectedModule: moduleId,
        }, { preserveState: true });
    };

    // CURSO: Crear / Editar
    const openCreateCourseModal = () => {
        setEditingCourse(null);
        courseForm.reset();
        courseForm.clearErrors();
        setIsCourseModalOpen(true);
    };

    const openEditCourseModal = (c, e) => {
        e.stopPropagation();
        setEditingCourse(c);
        courseForm.clearErrors();
        courseForm.setData({
            name: c.name || '',
            code: c.code || '',
            registration_fee: c.registration_fee || 0,
            monthly_fee: c.monthly_fee || 0,
            is_sequential: Boolean(c.is_sequential),
            status: c.status || 'Activo',
        });
        setIsCourseModalOpen(true);
    };

    const handleSaveCourse = (e) => {
        e.preventDefault();
        if (editingCourse) {
            courseForm.put(`/admin/courses/${editingCourse.id}`, {
                preserveScroll: true,
                onSuccess: () => setIsCourseModalOpen(false),
            });
        } else {
            courseForm.post('/admin/courses', {
                preserveScroll: true,
                onSuccess: () => setIsCourseModalOpen(false),
            });
        }
    };

    // MÓDULO: Crear / Editar
    const openCreateModuleModal = () => {
        if (!selectedCourse) return;
        setEditingModule(null);
        moduleForm.reset();
        moduleForm.clearErrors();
        setIsModuleModalOpen(true);
    };

    const openEditModuleModal = (m, e) => {
        e.stopPropagation();
        setEditingModule(m);
        moduleForm.clearErrors();
        moduleForm.setData({
            name: m.name || '',
            code: m.code || '',
            price: m.price || 0,
            duration_hours: m.duration_hours || 40,
            order: m.order || 0,
        });
        setIsModuleModalOpen(true);
    };

    const handleSaveModule = (e) => {
        e.preventDefault();
        if (editingModule) {
            moduleForm.put(`/admin/courses/modules/${editingModule.id}`, {
                preserveScroll: true,
                onSuccess: () => setIsModuleModalOpen(false),
            });
        } else {
            moduleForm.post(`/admin/courses/${selectedCourse.id}/modules`, {
                preserveScroll: true,
                onSuccess: () => setIsModuleModalOpen(false),
            });
        }
    };

    // SECCIÓN: Crear / Editar
    const openCreateScheduleModal = () => {
        if (!selectedModule) return;
        setEditingSchedule(null);
        scheduleForm.reset();
        scheduleForm.clearErrors();
        setIsScheduleModalOpen(true);
    };

    const openEditScheduleModal = (sch) => {
        setEditingSchedule(sch);
        scheduleForm.clearErrors();
        scheduleForm.setData({
            section_name: sch.section_name || '',
            teacher_id: sch.teacher_id ? String(sch.teacher_id) : '',
            classroom_id: sch.classroom_id ? String(sch.classroom_id) : '',
            days_of_week: Array.isArray(sch.days) ? sch.days : [],
            start_time: sch.raw_start_time || '18:00',
            end_time: sch.raw_end_time || '20:00',
            start_date: sch.start_date || '',
            end_date: sch.end_date || '',
            capacity: sch.capacity || 30,
            modality: sch.modality || 'Presencial',
            status: sch.status || 'Activo',
        });
        setIsScheduleModalOpen(true);
    };

    const handleSaveSchedule = (e) => {
        e.preventDefault();
        if (editingSchedule) {
            scheduleForm.put(`/admin/courses/schedules/${editingSchedule.id}`, {
                preserveScroll: true,
                onSuccess: () => setIsScheduleModalOpen(false),
            });
        } else {
            scheduleForm.post(`/admin/courses/modules/${selectedModule.id}/schedules`, {
                preserveScroll: true,
                onSuccess: () => setIsScheduleModalOpen(false),
            });
        }
    };

    const toggleDay = (day) => {
        const current = [...scheduleForm.data.days_of_week];
        const idx = current.indexOf(day);
        if (idx > -1) current.splice(idx, 1);
        else current.push(day);
        scheduleForm.setData('days_of_week', current);
    };

    // Eliminar
    const confirmDeleteAction = (type, id, name, url, e) => {
        if (e) e.stopPropagation();
        setDeleteConfig({ type, id, name, url });
    };

    const executeDelete = () => {
        if (!deleteConfig) return;
        router.delete(deleteConfig.url, {
            preserveScroll: true,
            onSuccess: () => setDeleteConfig(null),
        });
    };

    // --- HANDLERS: ENLACE WORDPRESS CURSO ---
    const fetchWpCourses = () => {
        setLoadingWpCourses(true);
        setWpError('');
        fetch('/admin/courses/wp-courses')
            .then((res) => res.json())
            .then((res) => {
                if (res.success && Array.isArray(res.data)) {
                    setWpCoursesList(res.data);
                } else {
                    setWpError(res.message || 'No se pudieron obtener cursos desde WordPress.');
                }
            })
            .catch(() => {
                setWpError('Error de red al conectar con WordPress.');
            })
            .finally(() => setLoadingWpCourses(false));
    };

    const openLinkWpModal = (c, e) => {
        if (e) e.stopPropagation();
        setLinkingWpCourse(c);
        setSelectedWpCourseId(c.mapping?.wp_course_id ? String(c.mapping.wp_course_id) : '');
        setWpError('');
        setIsWpModalOpen(true);
        if (wpCoursesList.length === 0) {
            fetchWpCourses();
        }
    };

    const handleSaveWpCourse = (e) => {
        e.preventDefault();
        if (!linkingWpCourse) return;
        setSavingWpCourse(true);
        router.post(`/admin/courses/${linkingWpCourse.id}/link-wp`, {
            wp_course_id: selectedWpCourseId || null,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setIsWpModalOpen(false);
                setSavingWpCourse(false);
            },
            onError: () => setSavingWpCourse(false),
        });
    };

    // --- HANDLERS: ENLACE MOODLE (CASCADA) ---
    const fetchMoodleCourses = () => {
        setLoadingMoodleCourses(true);
        setMoodleError('');
        fetch('/admin/courses/moodle-courses')
            .then((res) => res.json())
            .then((res) => {
                if (res.success && Array.isArray(res.data)) {
                    setMoodleCoursesList(res.data);
                } else {
                    setMoodleError(res.message || 'No se pudieron obtener cursos desde Moodle.');
                }
            })
            .catch(() => {
                setMoodleError('Error de red al conectar con el aula virtual Moodle.');
            })
            .finally(() => setLoadingMoodleCourses(false));
    };

    const openMoodleModal = (type, id, title, currentMoodleId, e) => {
        if (e) e.stopPropagation();
        setMoodleLinkingType(type);
        setMoodleLinkingId(id);
        setMoodleLinkingTitle(title);
        setSelectedMoodleCourseId(currentMoodleId ? String(currentMoodleId) : '');
        setMoodleError('');
        setIsMoodleModalOpen(true);
        if (moodleCoursesList.length === 0) {
            fetchMoodleCourses();
        }
    };

    const handleSaveMoodle = (e) => {
        e.preventDefault();
        if (!moodleLinkingId) return;
        setSavingMoodle(true);
        router.post('/admin/courses/link-moodle', {
            type: moodleLinkingType,
            id: moodleLinkingId,
            moodle_course_id: selectedMoodleCourseId || null,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setIsMoodleModalOpen(false);
                setSavingMoodle(false);
            },
            onError: () => setSavingMoodle(false),
        });
    };

    // --- HANDLERS: ENLACE SECCIÓN WP HORARIOS ---
    const openLinkWpScheduleModal = (sch, e) => {
        if (e) e.stopPropagation();
        setLinkingSchedule(sch);
        setSelectedWpScheduleString(sch.mapping?.wp_schedule_string || '');
        setScheduleWpError('');
        setIsWpScheduleModalOpen(true);
        setLoadingWpSchedules(true);

        fetch(`/admin/courses/schedules/${sch.id}/wp-schedules`)
            .then((res) => res.json())
            .then((res) => {
                if (res.success && Array.isArray(res.data)) {
                    setWpSchedulesList(res.data);
                } else {
                    setScheduleWpError(res.message || 'No se encontraron horarios en WordPress para este curso.');
                }
            })
            .catch(() => {
                setScheduleWpError('Error de red al consultar horarios de WordPress.');
            })
            .finally(() => setLoadingWpSchedules(false));
    };

    const handleSaveWpSchedule = (e) => {
        e.preventDefault();
        if (!linkingSchedule) return;
        setSavingWpSchedule(true);
        router.post(`/admin/courses/schedules/${linkingSchedule.id}/link-wp`, {
            wp_schedule_string: selectedWpScheduleString || null,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setIsWpScheduleModalOpen(false);
                setSavingWpSchedule(false);
            },
            onError: () => setSavingWpSchedule(false),
        });
    };

    const weekDaysList = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    return (
        <AuthenticatedLayout title="Oferta Académica — Cursos Técnicos">
            <Head title="Cursos Técnicos — SGA" />

            <div className="space-y-6">
                {/* Encabezado */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Oferta de Cursos Técnicos
                        </h1>
                        
                    </div>
                    <div className="flex gap-3">
                        <Link
                            href="/admin/careers"
                            className="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition"
                        >
                            Ver Carreras Universitarias →
                        </Link>
                        <ProgressFoldButton
                            variant="primary"
                            onClick={openCreateCourseModal}
                        >
                            <span className="inline-flex items-center gap-2">
                                <Plus className="size-4" />
                                <span>Nuevo Curso</span>
                            </span>
                        </ProgressFoldButton>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cursos Registrados</p>
                        <p className="text-2xl font-bold text-gray-900 dark:text-white mt-1">{metrics?.total_courses || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total de Módulos</p>
                        <p className="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{metrics?.total_modules || 0}</p>
                    </div>
                    <div className="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-[#161622]">
                        <p className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Secciones Creadas</p>
                        <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{metrics?.total_schedules || 0}</p>
                    </div>
                </div>

                {/* Estructura Tri-Panel: Cursos | Módulos | Secciones */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {/* PANEL 1: CURSOS (4 COLS) */}
                    <div className="lg:col-span-4 rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622] p-5 space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="flex h-2.5 w-2.5 rounded-full bg-brand-primary" />
                                Cursos Técnicos
                            </h3>
                            <button
                                type="button"
                                onClick={openCreateCourseModal}
                                className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                            >
                                + Nuevo
                            </button>
                        </div>

                        {/* Buscador de cursos */}
                        <form onSubmit={handleSearch}>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Filtrar cursos..."
                                className="w-full rounded-xl border-0 py-2 px-3 text-xs text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                            />
                        </form>

                        {/* Lista de Cursos */}
                        <div className="space-y-2 max-h-[520px] overflow-y-auto pr-1">
                            {courses?.data && courses.data.length > 0 ? (
                                courses.data.map((c) => {
                                    const isSelected = selectedCourse?.id === c.id;
                                    return (
                                        <div
                                            key={c.id}
                                            onClick={() => handleSelectCourse(c.id)}
                                            className={`p-3.5 rounded-2xl border cursor-pointer transition ${
                                                isSelected
                                                    ? 'border-brand-primary bg-blue-50/50 dark:border-blue-500 dark:bg-blue-900/20'
                                                    : 'border-gray-200 hover:border-gray-300 dark:border-gray-800'
                                            }`}
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1 pr-2">
                                                    <span className="font-mono text-[10px] font-bold text-gray-400 uppercase">
                                                        {c.code}
                                                    </span>
                                                    <h4 className="text-xs font-bold text-gray-900 dark:text-white">
                                                        {c.name}
                                                    </h4>
                                                    <p className="text-[11px] text-gray-500 mt-1">
                                                        {c.modules_count || 0} módulos • {c.students_count || 0} inscritos • Inscripción: DOP ${Number(c.registration_fee || 0).toLocaleString()}
                                                    </p>

                                                    {/* INSIGNIAS DE INTEGRACIÓN */}
                                                    {(c.mapping || c.moodle_course_id) && (
                                                        <div className="flex flex-wrap items-center gap-1.5 mt-2">
                                                            {c.mapping && (
                                                                <span
                                                                    className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800"
                                                                    title={`ID WordPress: ${c.mapping.wp_course_id}`}
                                                                >
                                                                    <Globe className="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
                                                                    WP: {c.mapping.wp_course_name || `#${c.mapping.wp_course_id}`}
                                                                </span>
                                                            )}
                                                            {c.moodle_course_id && (
                                                                <span
                                                                    className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800"
                                                                    title={`ID Aula Virtual Moodle: ${c.moodle_course_id}`}
                                                                >
                                                                    <GraduationCap className="w-3 h-3 text-amber-600 dark:text-amber-400" />
                                                                    Moodle ID: {c.moodle_course_id}
                                                                </span>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="flex items-center gap-1">
                                                    {/* Botón Enlazar con WordPress */}
                                                    <button
                                                        type="button"
                                                        onClick={(e) => openLinkWpModal(c, e)}
                                                        className={`p-1.5 rounded transition-colors ${
                                                            c.mapping
                                                                ? 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30'
                                                                : 'text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30'
                                                        }`}
                                                        title="Enlazar con WordPress (Academic+ Catalog)"
                                                    >
                                                        <Globe className="w-4 h-4" />
                                                    </button>

                                                    {/* Botón Enlazar con Moodle */}
                                                    <button
                                                        type="button"
                                                        onClick={(e) => openMoodleModal('course', c.id, `Curso: ${c.name}`, c.moodle_course_id, e)}
                                                        className={`p-1.5 rounded transition-colors ${
                                                            c.moodle_course_id
                                                                ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30'
                                                                : 'text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30'
                                                        }`}
                                                        title="Enlazar con Moodle (Aula Virtual)"
                                                    >
                                                        <GraduationCap className="w-4 h-4" />
                                                    </button>

                                                    {/* Editar Curso */}
                                                    <button
                                                        type="button"
                                                        onClick={(e) => openEditCourseModal(c, e)}
                                                        className="p-1.5 text-gray-400 hover:text-gray-900 dark:hover:text-white"
                                                        title="Editar datos del curso"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>

                                                    {/* Eliminar Curso */}
                                                    <button
                                                        type="button"
                                                        onClick={(e) => confirmDeleteAction('course', c.id, c.name, `/admin/courses/${c.id}`, e)}
                                                        className="p-1.5 text-red-400 hover:text-red-600"
                                                        title="Eliminar curso"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })
                            ) : (
                                <p className="text-center py-8 text-xs text-gray-400">No se encontraron cursos.</p>
                            )}
                        </div>
                    </div>

                    {/* PANEL 2: MÓDULOS DEL CURSO (4 COLS) */}
                    <div className="lg:col-span-4 rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622] p-5 space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="flex h-2.5 w-2.5 rounded-full bg-blue-500" />
                                Módulos / Materias
                            </h3>
                            {selectedCourse && (
                                <button
                                    type="button"
                                    onClick={openCreateModuleModal}
                                    className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                                >
                                    + Agregar Módulo
                                </button>
                            )}
                        </div>

                        {selectedCourse ? (
                            <>
                                <div className="rounded-xl bg-blue-50/50 p-2.5 dark:bg-blue-950/20 text-xs text-brand-primary dark:text-blue-300 font-semibold truncate flex items-center justify-between">
                                    <span className="truncate">Curso: {selectedCourse.name}</span>
                                    {selectedCourse.mapping && (
                                        <span className="text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200 px-2 py-0.5 rounded-full shrink-0">
                                            WP Enlazado
                                        </span>
                                    )}
                                </div>

                                <div className="space-y-2 max-h-[520px] overflow-y-auto pr-1">
                                    {modules && modules.length > 0 ? (
                                        modules.map((m) => {
                                            const isSelected = selectedModule?.id === m.id;
                                            return (
                                                <div
                                                    key={m.id}
                                                    onClick={() => handleSelectModule(m.id)}
                                                    className={`p-3 rounded-2xl border cursor-pointer transition ${
                                                        isSelected
                                                            ? 'border-blue-600 bg-blue-50/50 dark:border-blue-500 dark:bg-blue-900/20'
                                                            : 'border-gray-200 hover:border-gray-300 dark:border-gray-800'
                                                    }`}
                                                >
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex-1 pr-2">
                                                            <h5 className="text-xs font-bold text-gray-900 dark:text-white">
                                                                {m.name}
                                                            </h5>
                                                            <p className="text-[11px] text-gray-400 mt-0.5">
                                                                {m.schedules_count || 0} secciones • DOP ${Number(m.price || 0).toLocaleString()}
                                                            </p>

                                                            {/* INSIGNIA MOODLE MÓDULO */}
                                                            {m.moodle_course_id && (
                                                                <div className="mt-1.5">
                                                                    <span
                                                                        className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800"
                                                                        title={`ID Aula Virtual Moodle: ${m.moodle_course_id}`}
                                                                    >
                                                                        <GraduationCap className="w-3 h-3 text-amber-600 dark:text-amber-400" />
                                                                        Moodle ID: {m.moodle_course_id}
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>

                                                        <div className="flex items-center gap-1">
                                                            {/* Botón Enlazar Módulo con Moodle */}
                                                            <button
                                                                type="button"
                                                                onClick={(e) => openMoodleModal('module', m.id, `Módulo: ${m.name}`, m.moodle_course_id, e)}
                                                                className={`p-1 rounded transition-colors ${
                                                                    m.moodle_course_id
                                                                        ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30'
                                                                        : 'text-gray-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30'
                                                                }`}
                                                                title="Enlazar módulo con Moodle"
                                                            >
                                                                <GraduationCap className="w-3.5 h-3.5" />
                                                            </button>

                                                            <button
                                                                type="button"
                                                                onClick={(e) => openEditModuleModal(m, e)}
                                                                className="p-1 text-gray-400 hover:text-gray-900 dark:hover:text-white"
                                                                title="Editar módulo"
                                                            >
                                                                <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                </svg>
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={(e) => confirmDeleteAction('module', m.id, m.name, `/admin/courses/modules/${m.id}`, e)}
                                                                className="p-1 text-red-400 hover:text-red-600"
                                                                title="Eliminar módulo"
                                                            >
                                                                <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })
                                    ) : (
                                        <p className="text-center py-8 text-xs text-gray-400">
                                            Este curso aún no tiene módulos registrados.
                                        </p>
                                    )}
                                </div>
                            </>
                        ) : (
                            <div className="py-16 text-center text-xs text-gray-400">
                                Selecciona un curso a la izquierda para visualizar y editar sus módulos.
                            </div>
                        )}
                    </div>

                    {/* PANEL 3: SECCIONES Y HORARIOS (4 COLS) */}
                    <div className="lg:col-span-4 rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-[#161622] p-5 space-y-4">
                        <div className="flex items-center justify-between">
                            <h3 className="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span className="flex h-2.5 w-2.5 rounded-full bg-emerald-500" />
                                Secciones Activas
                            </h3>
                            {selectedModule && (
                                <button
                                    type="button"
                                    onClick={openCreateScheduleModal}
                                    className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                                >
                                    + Nueva Sección
                                </button>
                            )}
                        </div>

                        {selectedModule ? (
                            <>
                                <div className="rounded-xl bg-emerald-50/50 p-2.5 dark:bg-emerald-950/20 text-xs text-emerald-800 dark:text-emerald-300 font-semibold truncate">
                                    Módulo: {selectedModule.name}
                                </div>

                                <div className="space-y-2.5 max-h-[520px] overflow-y-auto pr-1">
                                    {schedules && schedules.length > 0 ? (
                                        schedules.map((sch) => (
                                            <div
                                                key={sch.id}
                                                className="p-3.5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/30 text-xs space-y-2"
                                            >
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <h5 className="font-bold text-gray-900 dark:text-white">
                                                            {sch.section_name}
                                                        </h5>
                                                        <p className="text-[11px] text-gray-500">
                                                            Prof. {sch.teacher_name}
                                                        </p>
                                                    </div>
                                                    <span className="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-brand-primary dark:bg-blue-900/40 dark:text-blue-300">
                                                        {sch.enrolled_count}/{sch.capacity} cupos
                                                    </span>
                                                </div>

                                                <div className="text-[11px] text-gray-500 space-y-0.5">
                                                    <div>Días: {Array.isArray(sch.days) ? sch.days.join(', ') : 'Variables'}</div>
                                                    <div>Horario: {sch.start_time} - {sch.end_time}</div>
                                                    <div>Aula: {sch.classroom_name} ({sch.modality})</div>
                                                </div>

                                                {Array.isArray(sch.enrolled_students) && sch.enrolled_students.length > 0 && (
                                                    <div className="pt-1.5 border-t border-gray-200/60 dark:border-gray-800">
                                                        <p className="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                                                            Estudiantes Inscritos ({sch.enrolled_students.length}):
                                                        </p>
                                                        <div className="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                                            {sch.enrolled_students.map((st) => (
                                                                <Link
                                                                    key={st.id}
                                                                    href={`/admin/students/profile/${st.id}`}
                                                                    className="inline-flex items-center gap-1 rounded-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2 py-0.5 text-[10px] font-medium text-gray-700 dark:text-gray-200 hover:border-brand-primary hover:text-brand-primary transition"
                                                                >
                                                                    <span>{st.name}</span>
                                                                    {st.student_code && (
                                                                        <span className="font-mono text-[9px] text-gray-400">({st.student_code})</span>
                                                                    )}
                                                                </Link>
                                                            ))}
                                                        </div>
                                                    </div>
                                                )}

                                                {/* INSIGNIAS DE INTEGRACIÓN EN SECCIÓN */}
                                                {(sch.moodle_course_id || sch.mapping) && (
                                                    <div className="flex flex-wrap items-center gap-1.5 pt-1">
                                                        {sch.mapping && (
                                                            <span
                                                                className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800"
                                                                title={`Horario en WordPress: ${sch.mapping.wp_schedule_string}`}
                                                            >
                                                                <Globe className="w-3 h-3 text-emerald-600" />
                                                                WP: {sch.mapping.wp_schedule_string}
                                                            </span>
                                                        )}
                                                        {sch.moodle_course_id && (
                                                            <span
                                                                className="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800"
                                                                title={`ID Aula Virtual Moodle: ${sch.moodle_course_id}`}
                                                            >
                                                                <GraduationCap className="w-3 h-3 text-amber-600" />
                                                                Moodle ID: {sch.moodle_course_id}
                                                            </span>
                                                        )}
                                                    </div>
                                                )}

                                                <div className="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-800">
                                                    {/* ACCIONES DE INTEGRACIÓN */}
                                                    <div className="flex items-center gap-1.5">
                                                        <button
                                                            type="button"
                                                            onClick={(e) => openMoodleModal('schedule', sch.id, `Sección: ${sch.section_name}`, sch.moodle_course_id, e)}
                                                            className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold border transition-colors ${
                                                                sch.moodle_course_id
                                                                    ? 'bg-amber-100 text-amber-900 border-amber-300 dark:bg-amber-950/60 dark:text-amber-200 dark:border-amber-800'
                                                                    : 'bg-white text-gray-700 hover:bg-amber-50 hover:text-amber-700 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'
                                                            }`}
                                                            title="Enlazar sección con Moodle"
                                                        >
                                                            <GraduationCap className="w-3 h-3" />
                                                            Moodle
                                                        </button>

                                                        {selectedCourse?.mapping && (
                                                            <button
                                                                type="button"
                                                                onClick={(e) => openLinkWpScheduleModal(sch, e)}
                                                                className={`inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold border transition-colors ${
                                                                    sch.mapping
                                                                        ? 'bg-emerald-100 text-emerald-900 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-200 dark:border-emerald-800'
                                                                        : 'bg-white text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700'
                                                                }`}
                                                                title="Vincular con horario de WordPress"
                                                            >
                                                                <Globe className="w-3 h-3" />
                                                                WP Horario
                                                            </button>
                                                        )}
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <button
                                                            type="button"
                                                            onClick={() => openEditScheduleModal(sch)}
                                                            className="text-xs font-semibold text-brand-primary hover:underline dark:text-blue-400"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => confirmDeleteAction('schedule', sch.id, sch.section_name, `/admin/courses/schedules/${sch.id}`)}
                                                            className="text-xs font-semibold text-red-500 hover:underline"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-center py-8 text-xs text-gray-400">
                                            No hay secciones creadas para este módulo.
                                        </p>
                                    )}
                                </div>
                            </>
                        ) : (
                            <div className="py-16 text-center text-xs text-gray-400">
                                Selecciona un módulo en el panel central para ver y programar sus secciones.
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* MODAL CREAR / EDITAR CURSO */}
            {isCourseModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-md bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            {editingCourse ? 'Editar Curso Técnico' : 'Registrar Nuevo Curso'}
                        </h3>
                        <form onSubmit={handleSaveCourse} className="mt-4 space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre del Curso *</label>
                                <input
                                    type="text"
                                    value={courseForm.data.name}
                                    onChange={(e) => courseForm.setData('name', e.target.value)}
                                    className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                />
                            </div>
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Código del Curso *</label>
                                <input
                                    type="text"
                                    value={courseForm.data.code}
                                    onChange={(e) => courseForm.setData('code', e.target.value)}
                                    placeholder="Ej: TEC-WEB-01"
                                    className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Arancel Inscripción (DOP)</label>
                                    <input
                                        type="number"
                                        value={courseForm.data.registration_fee}
                                        onChange={(e) => courseForm.setData('registration_fee', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Cuota Mensual (DOP)</label>
                                    <input
                                        type="number"
                                        value={courseForm.data.monthly_fee}
                                        onChange={(e) => courseForm.setData('monthly_fee', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                            </div>
                            <div className="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsCourseModalOpen(false)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={courseForm.processing}
                                    className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50"
                                >
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL CREAR / EDITAR MÓDULO */}
            {isModuleModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-md bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            {editingModule ? 'Editar Módulo' : `Nuevo Módulo para ${selectedCourse?.name}`}
                        </h3>
                        <form onSubmit={handleSaveModule} className="mt-4 space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre del Módulo *</label>
                                <input
                                    type="text"
                                    value={moduleForm.data.name}
                                    onChange={(e) => moduleForm.setData('name', e.target.value)}
                                    className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Precio / Costo (DOP)</label>
                                    <input
                                        type="number"
                                        value={moduleForm.data.price}
                                        onChange={(e) => moduleForm.setData('price', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Duración (Horas)</label>
                                    <input
                                        type="number"
                                        value={moduleForm.data.duration_hours}
                                        onChange={(e) => moduleForm.setData('duration_hours', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                            </div>
                            <div className="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsModuleModalOpen(false)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={moduleForm.processing}
                                    className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50"
                                >
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL CREAR / EDITAR SECCIÓN */}
            {isScheduleModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-xl bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            {editingSchedule ? 'Editar Sección' : `Nueva Sección para ${selectedModule?.name}`}
                        </h3>
                        <form onSubmit={handleSaveSchedule} className="mt-4 space-y-4">
                            {Object.keys(scheduleForm.errors).length > 0 && (
                                <div className="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 px-3.5 py-2.5 text-xs text-rose-700 dark:text-rose-300">
                                    {Object.values(scheduleForm.errors)[0]}
                                </div>
                            )}
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nombre Sección *</label>
                                    <input
                                        type="text"
                                        value={scheduleForm.data.section_name}
                                        onChange={(e) => scheduleForm.setData('section_name', e.target.value)}
                                        placeholder="Ej: Sec-01 Matutina"
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Docente Asignado</label>
                                    <select
                                        value={scheduleForm.data.teacher_id}
                                        onChange={(e) => scheduleForm.setData('teacher_id', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    >
                                        <option value="">Por asignar</option>
                                        {teachers.map((t) => (
                                            <option key={t.id} value={t.id}>{t.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Aula</label>
                                    <select
                                        value={scheduleForm.data.classroom_id}
                                        onChange={(e) => scheduleForm.setData('classroom_id', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    >
                                        <option value="">Virtual / Sin aula fija</option>
                                        {classrooms.map((cr) => (
                                            <option key={cr.id} value={cr.id}>{cr.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Cupo Máximo</label>
                                    <input
                                        type="number"
                                        value={scheduleForm.data.capacity}
                                        onChange={(e) => scheduleForm.setData('capacity', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                            </div>

                            {/* Días */}
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Días de Clase *</label>
                                <div className="flex flex-wrap gap-2">
                                    {weekDaysList.map((day) => {
                                        const isSel = scheduleForm.data.days_of_week.includes(day);
                                        return (
                                            <button
                                                key={day}
                                                type="button"
                                                onClick={() => toggleDay(day)}
                                                className={`px-3 py-1 rounded-lg text-xs font-semibold border transition ${
                                                    isSel
                                                        ? 'bg-brand-primary text-white border-brand-primary'
                                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300'
                                                }`}
                                            >
                                                {day}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Horas */}
                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Hora Inicio *</label>
                                    <input
                                        type="time"
                                        value={scheduleForm.data.start_time}
                                        onChange={(e) => scheduleForm.setData('start_time', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Hora Fin *</label>
                                    <input
                                        type="time"
                                        value={scheduleForm.data.end_time}
                                        onChange={(e) => scheduleForm.setData('end_time', e.target.value)}
                                        className="w-full rounded-xl border-0 py-2 px-3 text-sm text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-brand-primary dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    />
                                </div>
                            </div>

                            <div className="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setIsScheduleModalOpen(false)}
                                    className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={scheduleForm.processing}
                                    className="rounded-xl bg-brand-primary px-5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-dark disabled:opacity-50"
                                >
                                    Guardar Sección
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL 1: ENLACE CON WORDPRESS (ACADEMIC+ CATALOG) */}
            {isWpModalOpen && linkingWpCourse && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-lg bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div className="flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <Globe className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                        Enlazar con WordPress
                                    </h3>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setIsWpModalOpen(false)}
                                className="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="mt-4 p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 text-xs">
                            <span className="text-gray-400 uppercase font-mono text-[10px] font-bold">Curso Local:</span>
                            <div className="font-bold text-gray-900 dark:text-white text-sm mt-0.5">
                                {linkingWpCourse.name}
                            </div>
                            <div className="text-gray-500 font-mono mt-0.5">
                                Código: {linkingWpCourse.code}
                            </div>
                        </div>

                        <form onSubmit={handleSaveWpCourse} className="mt-4 space-y-4">
                            {loadingWpCourses ? (
                                <div className="py-8 text-center space-y-3">
                                    <Loader2 className="w-6 h-6 animate-spin text-emerald-600 mx-auto" />
                                    <p className="text-xs text-gray-500">Cargando cursos remotos desde WordPress...</p>
                                </div>
                            ) : wpError ? (
                                <div className="p-3 rounded-xl bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 text-xs flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <AlertCircle className="w-4 h-4 shrink-0" />
                                        <span>{wpError}</span>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={fetchWpCourses}
                                        className="text-xs font-bold underline hover:no-underline ml-2"
                                    >
                                        Reintentar
                                    </button>
                                </div>
                            ) : (
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                        Selecciona el Curso correspondiente en WordPress:
                                    </label>
                                    <select
                                        value={selectedWpCourseId}
                                        onChange={(e) => setSelectedWpCourseId(e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3 text-xs text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    >
                                        <option value="">-- Sin vincular / Desvincular enlace --</option>
                                        {wpCoursesList.map((wpC) => (
                                            <option key={wpC.wp_course_id} value={wpC.wp_course_id}>
                                                {wpC.wp_course_name} (ID: {wpC.wp_course_id})
                                            </option>
                                        ))}
                                    </select>
                                    <p className="text-[11px] text-gray-400 mt-1.5">
                                        Al vincular este curso, las inscripciones web recibidas desde el catálogo de WordPress ingresarán directamente a este curso.
                                    </p>
                                </div>
                            )}

                            <div className="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                {selectedWpCourseId ? (
                                    <button
                                        type="button"
                                        onClick={() => setSelectedWpCourseId('')}
                                        className="text-xs text-red-500 hover:text-red-600 font-semibold flex items-center gap-1"
                                    >
                                        <Unlink className="w-3.5 h-3.5" />
                                        Quitar enlace
                                    </button>
                                ) : (
                                    <div />
                                )}
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setIsWpModalOpen(false)}
                                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={savingWpCourse || loadingWpCourses}
                                        className="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 py-2 text-xs font-semibold text-white shadow-sm disabled:opacity-50 flex items-center gap-1.5"
                                    >
                                        {savingWpCourse && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                                        Guardar Enlace
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL 2: ENLACE CON MOODLE (CASCADA 3 NIVELES) */}
            {isMoodleModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-lg bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div className="flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                                    <GraduationCap className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                        Enlace con Aula Virtual (Moodle)
                                    </h3>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setIsMoodleModalOpen(false)}
                                className="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="mt-4 p-3 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 text-xs">
                            <span className="text-amber-700 dark:text-amber-400 uppercase font-mono text-[10px] font-bold">Entidad a Vincular:</span>
                            <div className="font-bold text-gray-900 dark:text-white text-sm mt-0.5">
                                {moodleLinkingTitle}
                            </div>
                            <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                <strong>Lógica de Cascada:</strong> El sistema busca primero el ID de Moodle en la Sección, luego en el Módulo y finalmente en el Curso general.
                            </p>
                        </div>

                        <form onSubmit={handleSaveMoodle} className="mt-4 space-y-4">
                            {loadingMoodleCourses ? (
                                <div className="py-8 text-center space-y-3">
                                    <Loader2 className="w-6 h-6 animate-spin text-amber-600 mx-auto" />
                                    <p className="text-xs text-gray-500">Consultando cursos activos en Moodle...</p>
                                </div>
                            ) : moodleError ? (
                                <div className="p-3 rounded-xl bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 text-xs flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <AlertCircle className="w-4 h-4 shrink-0" />
                                        <span>{moodleError}</span>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={fetchMoodleCourses}
                                        className="text-xs font-bold underline hover:no-underline ml-2"
                                    >
                                        Reintentar
                                    </button>
                                </div>
                            ) : (
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                        Selecciona el curso en el Aula Virtual Moodle:
                                    </label>
                                    <select
                                        value={selectedMoodleCourseId}
                                        onChange={(e) => setSelectedMoodleCourseId(e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3 text-xs text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-amber-500 dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    >
                                        <option value="">-- Sin vincular / Desvincular de Moodle --</option>
                                        {moodleCoursesList.map((mC) => (
                                            <option key={mC.id} value={mC.id}>
                                                {mC.fullname || mC.shortname} (ID: {mC.id})
                                            </option>
                                        ))}
                                    </select>
                                    <p className="text-[11px] text-gray-400 mt-1.5">
                                        Cuando un estudiante pague su matrícula, el sistema creará su usuario en Moodle y lo matriculará automáticamente en este curso.
                                    </p>
                                </div>
                            )}

                            <div className="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                {selectedMoodleCourseId ? (
                                    <button
                                        type="button"
                                        onClick={() => setSelectedMoodleCourseId('')}
                                        className="text-xs text-red-500 hover:text-red-600 font-semibold flex items-center gap-1"
                                    >
                                        <Unlink className="w-3.5 h-3.5" />
                                        Quitar enlace
                                    </button>
                                ) : (
                                    <div />
                                )}
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setIsMoodleModalOpen(false)}
                                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={savingMoodle || loadingMoodleCourses}
                                        className="rounded-xl bg-amber-600 hover:bg-amber-700 px-5 py-2 text-xs font-semibold text-white shadow-sm disabled:opacity-50 flex items-center gap-1.5"
                                    >
                                        {savingMoodle && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                                        Guardar en Moodle
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL 3: ENLACE SECCIÓN CON HORARIOS WORDPRESS */}
            {isWpScheduleModalOpen && linkingSchedule && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-lg bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800">
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div className="flex items-center gap-2.5">
                                <div className="p-2 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <Link2 className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="text-base font-bold text-gray-900 dark:text-white">
                                        Enlazar Sección con Horario de WordPress
                                    </h3>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setIsWpScheduleModalOpen(false)}
                                className="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        <div className="mt-4 p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 text-xs space-y-1">
                            <div>
                                <span className="text-gray-400 uppercase font-mono text-[10px] font-bold">Sección Local:</span>
                                <span className="font-bold text-gray-900 dark:text-white ml-2">{linkingSchedule.section_name}</span>
                            </div>
                            <div>
                                <span className="text-gray-400 uppercase font-mono text-[10px] font-bold">Curso WP Vinculado:</span>
                                <span className="text-emerald-600 dark:text-emerald-400 font-semibold ml-2">
                                    {selectedCourse?.mapping?.wp_course_name} (ID: {selectedCourse?.mapping?.wp_course_id})
                                </span>
                            </div>
                        </div>

                        <form onSubmit={handleSaveWpSchedule} className="mt-4 space-y-4">
                            {loadingWpSchedules ? (
                                <div className="py-8 text-center space-y-3">
                                    <Loader2 className="w-6 h-6 animate-spin text-emerald-600 mx-auto" />
                                    <p className="text-xs text-gray-500">Cargando horarios configurados en WordPress...</p>
                                </div>
                            ) : scheduleWpError ? (
                                <div className="p-3 rounded-xl bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 text-xs">
                                    <div className="flex items-center gap-2">
                                        <AlertCircle className="w-4 h-4 shrink-0" />
                                        <span>{scheduleWpError}</span>
                                    </div>
                                </div>
                            ) : (
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                        Selecciona el horario correspondiente de WordPress:
                                    </label>
                                    <select
                                        value={selectedWpScheduleString}
                                        onChange={(e) => setSelectedWpScheduleString(e.target.value)}
                                        className="w-full rounded-xl border-0 py-2.5 px-3 text-xs text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 dark:bg-gray-900 dark:text-white dark:ring-gray-700"
                                    >
                                        <option value="">-- Sin horario específico / Desvincular --</option>
                                        {wpSchedulesList.map((schStr, idx) => (
                                            <option key={idx} value={typeof schStr === 'string' ? schStr : (schStr.schedule_string || schStr.name || JSON.stringify(schStr))}>
                                                {typeof schStr === 'string' ? schStr : (schStr.schedule_string || schStr.name || JSON.stringify(schStr))}
                                            </option>
                                        ))}
                                    </select>
                                    <p className="text-[11px] text-gray-400 mt-1.5">
                                        Cuando un prospecto seleccione este horario en la página web, será asignado automáticamente a esta sección en el SGA.
                                    </p>
                                </div>
                            )}

                            <div className="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                                {selectedWpScheduleString ? (
                                    <button
                                        type="button"
                                        onClick={() => setSelectedWpScheduleString('')}
                                        className="text-xs text-red-500 hover:text-red-600 font-semibold flex items-center gap-1"
                                    >
                                        <Unlink className="w-3.5 h-3.5" />
                                        Quitar enlace
                                    </button>
                                ) : (
                                    <div />
                                )}
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => setIsWpScheduleModalOpen(false)}
                                        className="rounded-xl border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={savingWpSchedule || loadingWpSchedules}
                                        className="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-5 py-2 text-xs font-semibold text-white shadow-sm disabled:opacity-50 flex items-center gap-1.5"
                                    >
                                        {savingWpSchedule && <Loader2 className="w-3.5 h-3.5 animate-spin" />}
                                        Guardar Horario WP
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* MODAL CONFIRMAR ELIMINACIÓN */}
            {deleteConfig && (
                createPortal(<div className="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="relative w-full max-w-md bg-white dark:bg-[#161622] rounded-3xl p-6 shadow-2xl border border-gray-100 dark:border-gray-800 text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 mb-4">
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 className="text-base font-bold text-gray-900 dark:text-white">
                            ¿Eliminar {deleteConfig.name}?
                        </h3>
                        <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Esta acción eliminará el elemento seleccionado de la oferta académica.
                        </p>
                        <div className="mt-6 flex gap-3">
                            <button
                                type="button"
                                onClick={() => setDeleteConfig(null)}
                                className="flex-1 rounded-xl border border-gray-300 py-2.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                onClick={executeDelete}
                                className="flex-1 rounded-xl bg-red-600 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700"
                            >
                                Sí, Eliminar
                            </button>
                        </div>
                    </div>
                </div>, document.body)
            )}
        
            </AuthenticatedLayout>
    );
}
