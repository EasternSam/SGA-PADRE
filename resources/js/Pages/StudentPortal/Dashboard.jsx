import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ElasticText, WordsPullUp } from '@/components/godui';
import {
    GraduationCap,
    BookOpen,
    Clock,
    CreditCard,
    TrendingUp,
    Calendar,
    Award,
    CheckCircle2,
    AlertCircle,
    ArrowRight,
    MapPin,
    User,
    Sparkles,
    FileText,
    ExternalLink,
    Check,
    AlertTriangle,
    XCircle,
    ChevronRight,
    Layers,
    ShieldAlert
} from 'lucide-react';

const WEEKDAYS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const DAYS_LOOKUP = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

export default function StudentDashboard({
    auth,
    student,
    career,
    activeEnrollments = [],
    weeklySchedule = {},
    stats = {},
    pendingPayments = []
}) {
    const page = usePage();
    const branding = page.props?.branding || {};
    const admissionsOpen = Boolean(page.props?.admissions_open);
    const primaryColor = branding?.primary_color || '#211170';
    const accentColor = branding?.accent_color || '#ea580c';

    // Determinar día actual para activar la pestaña del horario por defecto
    const currentDayIdx = new Date().getDay();
    const currentDayName = DAYS_LOOKUP[currentDayIdx];
    const defaultDay = (currentDayIdx >= 1 && currentDayIdx <= 6) ? currentDayName : 'Lunes';
    const [selectedDay, setSelectedDay] = useState(defaultDay);

    const formatCurrency = (val) => {
        return new Intl.NumberFormat('es-DO', { style: 'currency', currency: 'DOP' }).format(val || 0);
    };

    const attendance = stats.attendance || {
        total: 0,
        present: 0,
        late: 0,
        absent: 0,
        percentage: 100,
        rate_badge: 'Sin registros'
    };

    const dayClasses = weeklySchedule[selectedDay] || [];

    return (
        <AuthenticatedLayout auth={auth} title="Portal del Estudiante">
            <Head title="Portal del Estudiante | SGA" />

            <div className="space-y-6 pb-20">
                {/* ── HERO BANNER ESTUDIANTE (Clean Dark/Light Standard) ── */}
                <div className="relative overflow-hidden rounded-2xl p-6 text-white shadow-md border border-slate-800/10 dark:border-[#262626] bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 dark:from-[#171717] dark:via-[#1c1917] dark:to-[#171717]">
                    <div 
                        className="absolute -right-16 -top-16 h-72 w-72 rounded-full blur-3xl pointer-events-none opacity-20 dark:opacity-15"
                        style={{ backgroundColor: primaryColor }}
                    />

                    <div className="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-center gap-4">
                            <div 
                                className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-2xl font-black text-white shadow-inner border border-white/20 dark:border-white/10"
                                style={{ background: `linear-gradient(135deg, ${primaryColor}, ${accentColor})` }}
                            >
                                {student?.name?.substring(0, 2).toUpperCase() || 'ES'}
                            </div>
                            <div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-400/20 px-2.5 py-0.5 text-xs font-bold text-emerald-300 backdrop-blur-sm border border-emerald-400/30">
                                        <CheckCircle2 className="h-3 w-3" />
                                        Estudiante Activo
                                    </span>
                                    <span className="font-mono text-xs text-white/70">
                                        Matrícula: {student?.student_code || 'EST-0023'}
                                    </span>
                                </div>
                                <h1 className="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl">
                                    <WordsPullUp
                                        text={`Hola, ${student?.name || auth?.user?.name || 'Estudiante'}`}
                                        wrapperClassName="justify-start inline-flex items-center"
                                        className="text-2xl font-black tracking-tight text-white sm:text-3xl md:text-3xl md:leading-normal pr-1.5"
                                    />
                                </h1>
                                <p className="mt-0.5 text-xs text-white/80 dark:text-neutral-300 sm:text-sm font-medium flex items-center gap-1.5">
                                    <GraduationCap className="h-4 w-4 text-orange-400" />
                                    <span>{career?.name || 'Carrera de Grado / Técnico Superior'}</span>
                                    {career?.code && (
                                        <span className="font-mono text-[11px] px-1.5 py-0.5 rounded bg-white/10 text-white/90">
                                            {career.code}
                                        </span>
                                    )}
                                </p>
                            </div>
                        </div>

                        {/* Botones de Acción Rápida en Cabecera */}
                        <div className="flex flex-wrap items-center gap-2.5">
                            <a
                                href={route('student.moodle.auth')}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-sm active:scale-95 transition bg-orange-600 hover:bg-orange-500 border border-orange-500/30"
                            >
                                <GraduationCap className="h-4 w-4 text-white" />
                                <span>Aula Virtual</span>
                                <ExternalLink className="h-3 w-3 opacity-70" />
                            </a>

                            {admissionsOpen && (
                                <Link
                                    href={route('student.selection')}
                                    className="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-sm active:scale-95 bg-white/15 hover:bg-white/25 text-white border border-white/20 backdrop-blur-md dark:bg-[#262626] dark:hover:bg-[#303030] dark:border-[#383838]"
                                >
                                    <BookOpen className="h-4 w-4 text-white" />
                                    Selección
                                </Link>
                            )}

                            <Link
                                href={route('student.payments')}
                                className="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-sm active:scale-95 bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md dark:bg-[#262626]/80 dark:hover:bg-[#303030] dark:border-[#383838]"
                            >
                                <CreditCard className="h-4 w-4 text-white" />
                                Mis Pagos
                            </Link>
                        </div>
                    </div>
                </div>

                {/* ── KPI METRICS GRID (5 CARDS HONESTAS Y REALES) ── */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    {/* 1. Índice Académico */}
                    <div className="rounded-2xl border border-gray-200/90 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-xs flex flex-col justify-between min-h-[116px] hover:border-gray-300 dark:hover:border-[#383838] transition-all">
                        <div>
                            <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Índice (GPA)</span>
                                <Award className="h-4 w-4 text-amber-500" />
                            </div>
                            <div className="mt-2.5 flex items-baseline gap-1.5">
                                <span className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                    {stats.has_gpa ? stats.gpa : '--'}
                                </span>
                                {stats.has_gpa && (
                                    <span className="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        / 100
                                    </span>
                                )}
                            </div>
                        </div>
                        <p className="mt-2 text-[11px] font-medium text-slate-400 dark:text-neutral-500 truncate">
                            {stats.has_gpa ? 'Promedio acumulado' : 'En curso (1er período)'}
                        </p>
                    </div>

                    {/* 2. Materias Cursando */}
                    <div className="rounded-2xl border border-gray-200/90 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-xs flex flex-col justify-between min-h-[116px] hover:border-gray-300 dark:hover:border-[#383838] transition-all">
                        <div>
                            <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Asignaturas</span>
                                <BookOpen className="h-4 w-4 text-orange-500 dark:text-orange-400" />
                            </div>
                            <div className="mt-2.5 flex items-baseline gap-1.5">
                                <span className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                    {stats.active_subjects ?? activeEnrollments.length}
                                </span>
                                <span className="text-xs font-semibold text-slate-500 dark:text-neutral-400">activas</span>
                            </div>
                        </div>
                        <p className="mt-2 text-[11px] font-medium text-slate-400 dark:text-neutral-500 truncate">
                            Ciclo Académico Actual
                        </p>
                    </div>

                    {/* 3. Avance de Carrera (Créditos Reales) */}
                    <div className="rounded-2xl border border-gray-200/90 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-xs flex flex-col justify-between min-h-[116px] hover:border-gray-300 dark:hover:border-[#383838] transition-all">
                        <div>
                            <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Créditos Pensum</span>
                                <TrendingUp className="h-4 w-4 text-emerald-500" />
                            </div>
                            <div className="mt-2.5 flex items-baseline gap-1.5">
                                <span className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                    {stats.in_progress_credits || stats.approved_credits || 0}
                                </span>
                                <span className="text-xs font-medium text-slate-400 dark:text-neutral-500">
                                    / {stats.total_credits || 60} cr
                                </span>
                            </div>
                        </div>
                        <div className="mt-2.5">
                            <div className="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden dark:bg-[#262626]">
                                <div
                                    className="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                    style={{
                                        width: `${Math.min(100, Math.round((((stats.approved_credits || 0) + (stats.in_progress_credits || 0)) / Math.max(1, stats.total_credits || 60)) * 100))}%`
                                    }}
                                />
                            </div>
                        </div>
                    </div>

                    {/* 4. Asistencia Global (Datos Reales de la BD) */}
                    <div className="rounded-2xl border border-gray-200/90 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-xs flex flex-col justify-between min-h-[116px] hover:border-gray-300 dark:hover:border-[#383838] transition-all">
                        <div>
                            <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Asistencia</span>
                                <Clock className="h-4 w-4 text-blue-500" />
                            </div>
                            <div className="mt-2.5 flex items-baseline gap-1.5">
                                <span className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                    {attendance.percentage}%
                                </span>
                                <span className={`text-[10px] font-bold px-1.5 py-0.5 rounded ${
                                    attendance.percentage >= 80 
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' 
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400'
                                }`}>
                                    {attendance.rate_badge}
                                </span>
                            </div>
                        </div>
                        <p className="mt-2 text-[11px] font-medium text-slate-400 dark:text-neutral-500 truncate">
                            {attendance.total} sesiones registradas
                        </p>
                    </div>

                    {/* 5. Estado Financiero / Balance */}
                    <div className="rounded-2xl border border-gray-200/90 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-xs col-span-2 sm:col-span-1 lg:col-span-1 flex flex-col justify-between min-h-[116px] hover:border-gray-300 dark:hover:border-[#383838] transition-all">
                        <div>
                            <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                                <span className="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Balance</span>
                                <CreditCard className="h-4 w-4 text-rose-500" />
                            </div>
                            <div className="mt-2.5 flex items-baseline gap-1.5">
                                <span className={`text-xl sm:text-2xl font-black ${
                                    (stats.pending_balance || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'
                                }`}>
                                    {formatCurrency(stats.pending_balance)}
                                </span>
                            </div>
                        </div>
                        <p className="mt-2 text-[11px] font-medium text-slate-400 dark:text-neutral-500 truncate">
                            {(stats.pending_balance || 0) > 0 ? 'Cargos por saldar' : 'Sin deuda pendiente'}
                        </p>
                    </div>
                </div>

                {/* ── CUERPO PRINCIPAL EN DOS COLUMNAS ── */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* COLUMNA IZQUIERDA (2/3): HORARIO SEMANAL INTERACTIVO & ASIGNATURAS */}
                    <div className="lg:col-span-2 space-y-6">

                        {/* 1. HORARIO INTERACTIVO SEMANAL */}
                        <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm">
                            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100 dark:border-[#262626]">
                                <div className="flex items-center gap-2">
                                    <div className="p-2 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400">
                                        <Calendar className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h2 className="text-base font-bold text-slate-900 dark:text-white">
                                            Horario & Agenda Semanal
                                        </h2>
                                    </div>
                                </div>

                                {currentDayName !== 'Domingo' && (
                                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-[#262626] text-slate-700 dark:text-neutral-300">
                                        <span className="h-2 w-2 rounded-full bg-orange-500 animate-pulse"></span>
                                        Hoy es {currentDayName}
                                    </span>
                                )}
                            </div>

                            {/* Pestañas de Días de la Semana */}
                            <div className="mt-4 flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-gray-100 dark:border-[#262626]">
                                {WEEKDAYS.map((day) => {
                                    const count = (weeklySchedule[day] || []).length;
                                    const isSelected = selectedDay === day;
                                    const isToday = currentDayName === day;

                                    return (
                                        <button
                                            key={day}
                                            type="button"
                                            onClick={() => setSelectedDay(day)}
                                            className={`flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition whitespace-nowrap ${
                                                isSelected
                                                    ? 'bg-orange-600 text-white shadow-sm'
                                                    : 'text-slate-600 dark:text-neutral-400 hover:bg-slate-100 dark:hover:bg-[#262626]'
                                            }`}
                                        >
                                            <span>{day}</span>
                                            {count > 0 && (
                                                <span className={`text-[10px] px-1.5 py-0.5 rounded-full font-mono ${
                                                    isSelected ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-[#333] text-slate-700 dark:text-neutral-300'
                                                }`}>
                                                    {count}
                                                </span>
                                            )}
                                            {isToday && !isSelected && (
                                                <span className="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                            )}
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Lista de Clases del Día Seleccionado */}
                            <div className="mt-4 space-y-3">
                                {dayClasses.length > 0 ? (
                                    dayClasses.map((item, idx) => (
                                        <div
                                            key={item.id || idx}
                                            className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border border-gray-100 dark:border-[#262626] bg-slate-50/60 dark:bg-[#1f1f1f] hover:border-orange-200 dark:hover:border-orange-500/30 transition"
                                        >
                                            <div className="flex items-start gap-3">
                                                <div className="flex flex-col items-center justify-center h-12 w-20 rounded-lg bg-white dark:bg-[#171717] border border-gray-200/80 dark:border-[#2e2e2e] shadow-xs text-center shrink-0">
                                                    <Clock className="h-3.5 w-3.5 text-orange-500 mb-0.5" />
                                                    <span className="text-[11px] font-mono font-bold text-slate-800 dark:text-neutral-200">
                                                        {item.start_time}
                                                    </span>
                                                    <span className="text-[9px] text-slate-400 dark:text-neutral-500">
                                                        {item.end_time}
                                                    </span>
                                                </div>

                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-400">
                                                            {item.subject_code}
                                                        </span>
                                                        <span className="text-[11px] text-slate-400 dark:text-neutral-500 font-medium">
                                                            {item.credits} créditos
                                                        </span>
                                                    </div>
                                                    <h4 className="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                                                        {item.subject_name}
                                                    </h4>
                                                    <div className="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-neutral-400">
                                                        <span className="flex items-center gap-1">
                                                            <User className="h-3.5 w-3.5 text-slate-400" />
                                                            {item.teacher}
                                                        </span>
                                                        <span className="flex items-center gap-1 font-medium text-slate-700 dark:text-neutral-300">
                                                            <MapPin className="h-3.5 w-3.5 text-orange-500" />
                                                            {item.classroom}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex items-center justify-end gap-2 pt-2 sm:pt-0 border-t border-gray-200/40 dark:border-[#2b2b2b] sm:border-0">
                                                <a
                                                    href={route('student.moodle.auth')}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-[#262626] border border-gray-200 dark:border-[#383838] text-slate-700 dark:text-neutral-200 hover:bg-orange-50 hover:text-orange-600 dark:hover:bg-[#303030] dark:hover:text-orange-400 transition shadow-xs"
                                                >
                                                    <span>Ir al Aula</span>
                                                    <ExternalLink className="h-3 w-3" />
                                                </a>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="py-8 text-center bg-slate-50/50 dark:bg-[#1a1a1a] rounded-xl border border-dashed border-gray-200 dark:border-[#2a2a2a]">
                                        <Calendar className="mx-auto h-7 w-7 text-slate-400 dark:text-neutral-500 mb-2" />
                                        <p className="text-xs font-semibold text-slate-700 dark:text-neutral-300">
                                            No tienes clases programadas para los días {selectedDay}
                                        </p>
                                        <p className="text-[11px] text-slate-400 dark:text-neutral-500 mt-0.5">
                                            Aprovecha para revisar tu material de estudio y asignaciones en el Aula Virtual.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* 2. MIS ASIGNATURAS EN CURSO */}
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <h2 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <BookOpen className="h-5 w-5 text-orange-500 dark:text-orange-400" />
                                    <span>Mis Asignaturas en Curso ({activeEnrollments.length})</span>
                                </h2>
                                <Link
                                    href={route('student.projection')}
                                    className="text-xs font-semibold hover:underline flex items-center gap-1 text-orange-600 dark:text-orange-400"
                                >
                                    <span>Ver Pensum Completo</span>
                                    <ArrowRight className="h-3.5 w-3.5" />
                                </Link>
                            </div>

                            <div className="space-y-3">
                                {activeEnrollments.map((enr) => {
                                    const att = enr.attendance_stats || {};
                                    return (
                                        <div
                                            key={enr.id}
                                            className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm transition hover:border-gray-300 dark:hover:border-[#383838]"
                                        >
                                            <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                                <div className="space-y-2">
                                                    <div className="flex items-center gap-2">
                                                        <span className="rounded-lg bg-gray-100 dark:bg-[#262626] border border-gray-200/60 dark:border-[#333333] px-2.5 py-0.5 font-mono text-xs font-bold text-gray-700 dark:text-gray-200">
                                                            {enr.subject_code}
                                                        </span>
                                                        <span className="text-xs font-semibold text-slate-400 dark:text-neutral-400">
                                                            {enr.credits} Créditos
                                                        </span>
                                                        <span className="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 px-2 py-0.5 text-[11px] font-bold text-emerald-700 dark:text-emerald-400">
                                                            {enr.status}
                                                        </span>
                                                    </div>

                                                    <h3 className="text-base font-bold text-slate-900 dark:text-white leading-snug">
                                                        {enr.subject_name}
                                                    </h3>

                                                    <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-neutral-400 pt-1">
                                                        <span className="flex items-center gap-1.5">
                                                            <User className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                                            {enr.teacher}
                                                        </span>
                                                        <span className="flex items-center gap-1.5">
                                                            <MapPin className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                                            {enr.classroom}
                                                        </span>
                                                        <span className="flex items-center gap-1.5 font-medium text-slate-700 dark:text-neutral-300">
                                                            <Clock className="h-3.5 w-3.5 text-orange-500 dark:text-orange-400" />
                                                            {enr.days} &bull; {enr.start_time} - {enr.end_time}
                                                        </span>
                                                    </div>
                                                </div>

                                                {/* Asistencia Específica & Botón Aula */}
                                                <div className="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 border-t border-gray-100 dark:border-[#262626] pt-3 sm:border-0 sm:pt-0 shrink-0">
                                                    {att.total > 0 && (
                                                        <div className="text-right">
                                                            <div className="flex items-center gap-1 text-xs font-bold text-slate-700 dark:text-neutral-200">
                                                                <span>Asistencia:</span>
                                                                <span className={`px-1.5 py-0.5 rounded text-[11px] font-mono ${
                                                                    att.percentage >= 80 
                                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' 
                                                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400'
                                                                }`}>
                                                                    {att.percentage}%
                                                                </span>
                                                            </div>
                                                            <p className="text-[10px] text-slate-400 dark:text-neutral-500 mt-0.5">
                                                                {att.present}P / {att.late}T / {att.absent}A ({att.total} clases)
                                                            </p>
                                                        </div>
                                                    )}

                                                    <a
                                                        href={route('student.moodle.auth')}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold bg-orange-600 hover:bg-orange-500 text-white shadow-xs transition"
                                                    >
                                                        <GraduationCap className="h-3.5 w-3.5" />
                                                        <span>Aula Virtual</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}

                                {activeEnrollments.length === 0 && (
                                    <div className="rounded-xl border border-dashed border-gray-200 dark:border-[#262626] p-8 text-center bg-white dark:bg-[#171717]">
                                        <BookOpen className="mx-auto h-8 w-8 text-slate-400 dark:text-neutral-500 mb-2" />
                                        <p className="text-sm font-semibold text-slate-700 dark:text-neutral-300">
                                            No tienes materias inscritas para el ciclo activo
                                        </p>
                                        <p className="text-xs text-slate-400 dark:text-neutral-500 mt-1">
                                            El proceso de selección de asignaturas está disponible.
                                        </p>
                                        <Link
                                            href={route('student.selection')}
                                            className="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-orange-600 hover:bg-orange-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition"
                                        >
                                            Seleccionar Asignaturas
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* COLUMNA DERECHA (1/3): WIDGETS DE ASISTENCIA, FINANZAS Y SERVICIOS */}
                    <div className="space-y-6">

                        {/* 1. RESUMEN DETALLADO DE ASISTENCIA */}
                        <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm">
                            <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-[#262626]">
                                <h3 className="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <Clock className="h-4 w-4 text-blue-500" />
                                    <span>Control de Asistencia</span>
                                </h3>
                                <span className={`text-[11px] font-bold px-2 py-0.5 rounded-full ${
                                    attendance.percentage >= 80 
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60'
                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60'
                                }`}>
                                    {attendance.rate_badge} ({attendance.percentage}%)
                                </span>
                            </div>

                            <div className="mt-4">
                                {/* Barra visual tricolor de asistencia */}
                                <div className="h-2.5 w-full rounded-full bg-slate-100 dark:bg-[#262626] overflow-hidden flex">
                                    {attendance.total > 0 ? (
                                        <>
                                            <div 
                                                className="bg-emerald-500 h-full"
                                                style={{ width: `${(attendance.present / attendance.total) * 100}%` }}
                                                title={`Presentes: ${attendance.present}`}
                                            />
                                            <div 
                                                className="bg-amber-500 h-full"
                                                style={{ width: `${(attendance.late / attendance.total) * 100}%` }}
                                                title={`Tardanzas: ${attendance.late}`}
                                            />
                                            <div 
                                                className="bg-rose-500 h-full"
                                                style={{ width: `${(attendance.absent / attendance.total) * 100}%` }}
                                                title={`Ausencias: ${attendance.absent}`}
                                            />
                                        </>
                                    ) : (
                                        <div className="bg-slate-300 dark:bg-neutral-600 h-full w-full" />
                                    )}
                                </div>

                                {/* Desglose numérico */}
                                <div className="mt-3 grid grid-cols-3 gap-2 text-center">
                                    <div className="p-2 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40">
                                        <p className="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400">Presente</p>
                                        <p className="text-base font-black text-emerald-800 dark:text-emerald-300 mt-0.5">{attendance.present}</p>
                                    </div>
                                    <div className="p-2 rounded-lg bg-amber-50/60 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40">
                                        <p className="text-[10px] font-semibold text-amber-700 dark:text-amber-400">Tardanza</p>
                                        <p className="text-base font-black text-amber-800 dark:text-amber-300 mt-0.5">{attendance.late}</p>
                                    </div>
                                    <div className="p-2 rounded-lg bg-rose-50/60 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40">
                                        <p className="text-[10px] font-semibold text-rose-700 dark:text-rose-400">Ausente</p>
                                        <p className="text-base font-black text-rose-800 dark:text-rose-300 mt-0.5">{attendance.absent}</p>
                                    </div>
                                </div>

                                <div className="mt-3 p-2.5 rounded-lg bg-slate-50 dark:bg-[#1a1a1a] border border-gray-100 dark:border-[#262626] flex items-start gap-2">
                                    <ShieldAlert className="h-4 w-4 text-slate-400 dark:text-neutral-500 shrink-0 mt-0.5" />
                                    <p className="text-[11px] text-slate-500 dark:text-neutral-400 leading-tight">
                                        El reglamento institucional requiere al menos un <strong className="text-slate-700 dark:text-neutral-300">80% de asistencia</strong> para conservar el derecho a examen final.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* 2. CUENTAS POR PAGAR & FACTURACIÓN */}
                        <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm">
                            <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-[#262626]">
                                <h3 className="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <CreditCard className="h-4 w-4 text-rose-500" />
                                    <span>Cuentas por Pagar</span>
                                </h3>
                                <Link
                                    href={route('student.payments')}
                                    className="text-xs font-semibold hover:underline text-orange-600 dark:text-orange-400"
                                >
                                    Ver Historial
                                </Link>
                            </div>

                            <div className="mt-3 space-y-3">
                                {pendingPayments.map(p => (
                                    <div key={p.id} className="flex items-center justify-between text-xs p-2.5 rounded-lg bg-slate-50 dark:bg-[#1f1f1f] border border-gray-100 dark:border-[#262626]">
                                        <div>
                                            <p className="font-semibold text-slate-800 dark:text-neutral-200">{p.concept}</p>
                                            <p className="text-[11px] text-slate-400 dark:text-neutral-500">Vence: {p.due_date}</p>
                                        </div>
                                        <span className="font-mono font-bold text-rose-600 dark:text-rose-400 text-sm">
                                            {formatCurrency(p.amount)}
                                        </span>
                                    </div>
                                ))}

                                {pendingPayments.length === 0 && (
                                    <div className="py-4 text-center text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center justify-center gap-1.5">
                                        <CheckCircle2 className="h-4 w-4" />
                                        ¡Tu balance financiero está completamente al día!
                                    </div>
                                )}
                            </div>

                            {pendingPayments.length > 0 && (
                                <Link
                                    href={route('student.payments')}
                                    className="mt-4 w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-95"
                                >
                                    <CreditCard className="h-4 w-4" />
                                    <span>Pagar en Línea con Tarjeta</span>
                                </Link>
                            )}
                        </div>

                        {/* 3. AULA VIRTUAL MOODLE BANNER */}
                        <div className="relative overflow-hidden rounded-xl border border-orange-200/60 dark:border-[#262626] bg-gradient-to-br from-orange-500 via-orange-600 to-amber-600 dark:from-[#1c1917] dark:via-[#1a1715] dark:to-[#171717] p-5 text-white shadow-sm">
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 dark:bg-orange-500/20 backdrop-blur-md shadow-inner border border-white/20 dark:border-orange-500/30">
                                        <GraduationCap className="h-6 w-6 text-white dark:text-orange-400" />
                                    </div>
                                    <div>
                                        <h4 className="text-sm font-black text-white">Aula Virtual (Moodle)</h4>
                                        <p className="text-[11px] text-orange-100 dark:text-neutral-400 font-medium">Campus Virtual & Evaluaciones</p>
                                    </div>
                                </div>
                                <span className="rounded-full bg-white/25 dark:bg-emerald-500/20 border border-white/20 dark:border-emerald-500/30 px-2 py-0.5 text-[10px] font-bold text-white dark:text-emerald-400 uppercase tracking-wider backdrop-blur-sm">
                                    En línea
                                </span>
                            </div>
                            <div className="mt-4 flex items-center justify-end pt-3 border-t border-white/20 dark:border-[#262626]">
                                <a
                                    href={route('student.moodle.auth')}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold shadow-sm active:scale-95 transition bg-white text-orange-600 hover:bg-orange-50 dark:bg-orange-600 dark:text-white dark:hover:bg-orange-500"
                                >
                                    <span>Ingresar</span>
                                    <ExternalLink className="h-3.5 w-3.5" />
                                </a>
                            </div>
                        </div>

                        {/* 4. SERVICIOS Y TRÁMITES ACADÉMICOS */}
                        <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm space-y-3">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-neutral-500">
                                Trámites & Servicios al Estudiante
                            </h3>

                            <div className="space-y-1.5">
                                {admissionsOpen && (
                                    <Link
                                        href={route('student.selection')}
                                        className="flex items-center justify-between rounded-lg p-2.5 hover:bg-slate-50 dark:hover:bg-[#222222] transition text-xs font-semibold text-slate-700 dark:text-neutral-200 group"
                                    >
                                        <div className="flex items-center gap-2.5">
                                            <BookOpen className="h-4 w-4 text-orange-500 group-hover:scale-110 transition-transform" />
                                            <span>Selección de Asignaturas</span>
                                        </div>
                                        <ChevronRight className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                    </Link>
                                )}

                                <Link
                                    href={route('student.projection')}
                                    className="flex items-center justify-between rounded-lg p-2.5 hover:bg-slate-50 dark:hover:bg-[#222222] transition text-xs font-semibold text-slate-700 dark:text-neutral-200 group"
                                >
                                    <div className="flex items-center gap-2.5">
                                        <Layers className="h-4 w-4 text-blue-500 group-hover:scale-110 transition-transform" />
                                        <span>Pensum & Avance Académico</span>
                                    </div>
                                    <ChevronRight className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                </Link>

                                <Link
                                    href={route('student.requests')}
                                    className="flex items-center justify-between rounded-lg p-2.5 hover:bg-slate-50 dark:hover:bg-[#222222] transition text-xs font-semibold text-slate-700 dark:text-neutral-200 group"
                                >
                                    <div className="flex items-center gap-2.5">
                                        <FileText className="h-4 w-4 text-emerald-600 group-hover:scale-110 transition-transform" />
                                        <span>Solicitud de Récord & Certificaciones</span>
                                    </div>
                                    <ChevronRight className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                </Link>

                                <Link
                                    href={route('student.profile.edit')}
                                    className="flex items-center justify-between rounded-lg p-2.5 hover:bg-slate-50 dark:hover:bg-[#222222] transition text-xs font-semibold text-slate-700 dark:text-neutral-200 group"
                                >
                                    <div className="flex items-center gap-2.5">
                                        <User className="h-4 w-4 text-purple-500 group-hover:scale-110 transition-transform" />
                                        <span>Mi Perfil & Datos de Contacto</span>
                                    </div>
                                    <ChevronRight className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                </Link>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
