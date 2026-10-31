import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ElasticText, WordsPullUp } from '@/components/godui';
import {
    BookOpen,
    Users,
    Clock,
    Calendar,
    Award,
    CheckCircle2,
    ArrowRight,
    MapPin,
    FileSpreadsheet,
    DollarSign,
    Sparkles,
    Lock
} from 'lucide-react';

export default function TeacherDashboard({
    auth,
    teacher,
    sections = [],
    stats = {},
    nextClass = null
}) {
    const page = usePage();
    const branding = page.props?.branding || {};
    const primaryColor = branding?.primary_color || '#211170';
    const accentColor = branding?.accent_color || '#ea580c';

    return (
        <AuthenticatedLayout auth={auth} title="Portal del Docente">
            <Head title="Portal del Docente | SGA" />

            <div className="space-y-6 pb-20">
                {/* Hero Teacher Banner (Bills Clean Dark/Light Standard) */}
                <div className="relative overflow-hidden rounded-2xl p-6 text-white shadow-md border border-slate-800/10 dark:border-[#262626] bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 dark:from-[#171717] dark:via-[#1c1917] dark:to-[#171717]">
                    {/* Glow decorativo sutil en esquina con color primario */}
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
                                {teacher?.name?.substring(0, 2).toUpperCase() || 'PR'}
                            </div>
                            <div>
                                <div className="flex items-center gap-2">
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-400/20 px-2.5 py-0.5 text-xs font-bold text-emerald-300 backdrop-blur-sm border border-emerald-400/30">
                                        <CheckCircle2 className="h-3 w-3" />
                                        Docente Activo
                                    </span>
                                    <span className="text-xs text-white/70">
                                        {teacher?.email}
                                    </span>
                                </div>
                                <h1 className="mt-1 text-2xl font-black tracking-tight text-white sm:text-3xl">
                                    <WordsPullUp
                                        text={`Hola, Prof. ${teacher?.name || auth?.user?.name || 'Docente'}`}
                                        wrapperClassName="justify-start inline-flex items-center"
                                        className="text-2xl font-black tracking-tight text-white sm:text-3xl md:text-3xl md:leading-normal pr-1.5"
                                    />
                                </h1>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-2.5">
                            <Link
                                href={route('teacher.payroll')}
                                className="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition shadow-sm active:scale-95 bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md dark:bg-[#262626] dark:hover:bg-[#303030] dark:border-[#383838] dark:text-white"
                            >
                                <DollarSign className="h-4 w-4 text-emerald-400" />
                                Mis Recibos de Nómina
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Key KPIs Cards (Bills Dark Mode Standard) */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm">
                        <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Secciones Asignadas</span>
                            <BookOpen className="h-4 w-4 text-orange-500 dark:text-orange-400" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-slate-900 dark:text-white">
                                {stats.total_sections || sections.length}
                            </span>
                            <span className="text-xs font-medium text-slate-500 dark:text-neutral-400">grupos activos</span>
                        </div>
                    </div>

                    <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm">
                        <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Estudiantes a Cargo</span>
                            <Users className="h-4 w-4 text-emerald-500" />
                        </div>
                        <div className="mt-3 flex items-baseline gap-2">
                            <span className="text-3xl font-black text-emerald-600 dark:text-emerald-400">
                                {stats.total_students || 0}
                            </span>
                            <span className="text-xs font-medium text-slate-500 dark:text-neutral-400">alumnos matriculados</span>
                        </div>
                    </div>

                    <div className="rounded-xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm">
                        <div className="flex items-center justify-between text-slate-500 dark:text-neutral-400">
                            <span className="text-xs font-semibold uppercase tracking-wider">Próxima Clase</span>
                            <Clock className="h-4 w-4 text-blue-500 dark:text-blue-400" />
                        </div>
                        <div className="mt-2">
                            <span className="text-sm font-bold text-slate-900 dark:text-white line-clamp-1">
                                {nextClass?.subject_name || 'Sin clases pendientes hoy'}
                            </span>
                            <p className="text-xs text-slate-400 dark:text-neutral-500 mt-0.5">
                                {nextClass ? `${nextClass.classroom} • ${nextClass.time}` : 'Horario completado'}
                            </p>
                        </div>
                    </div>
                </div>

                {/* My Sections Grid */}
                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <BookOpen className="h-4 w-4 text-orange-500 dark:text-orange-400" />
                            Mis Asignaturas & Cursos Asignados
                        </h2>
                        <span className="text-xs font-semibold text-slate-500 dark:text-neutral-400">
                            {sections.length} secciones
                        </span>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {sections.map(section => (
                            <div
                                key={section.id}
                                className="rounded-2xl border border-gray-200 dark:border-[#262626] bg-white dark:bg-[#171717] p-5 shadow-sm hover:border-gray-300 dark:hover:border-[#383838] transition flex flex-col justify-between"
                            >
                                <div>
                                    <div className="flex items-center justify-between">
                                        <span className="rounded-lg bg-gray-100 dark:bg-[#262626] border border-gray-200/60 dark:border-[#333333] px-2.5 py-0.5 font-mono text-xs font-bold text-gray-700 dark:text-gray-200">
                                            {section.subject_code}
                                        </span>
                                        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                                            <Users className="h-3 w-3" />
                                            {section.students_count} alumnos
                                        </span>
                                    </div>

                                    <h3 className="mt-2 text-base font-bold text-slate-900 dark:text-white">
                                        {section.subject_name}
                                    </h3>
                                    <p className="text-xs text-slate-500 dark:text-neutral-400 line-clamp-1 mt-0.5">
                                        {section.course_name}
                                    </p>

                                    <div className="mt-4 space-y-1.5 text-xs text-slate-500 dark:text-neutral-400 border-t border-gray-100 dark:border-[#262626] pt-3">
                                        <div className="flex items-center gap-2">
                                            <MapPin className="h-3.5 w-3.5 text-slate-400 dark:text-neutral-500" />
                                            <span>{section.classroom}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Clock className="h-3.5 w-3.5 text-orange-500 dark:text-orange-400" />
                                            <span>{section.days} &bull; {section.start_time} - {section.end_time}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="mt-5 grid grid-cols-2 gap-2 border-t border-gray-100 dark:border-[#262626] pt-4">
                                    <Link
                                        href={route('teacher.attendance', section.id)}
                                        className="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-200 dark:border-[#383838] bg-gray-50 dark:bg-[#262626] py-2 text-xs font-semibold text-slate-700 dark:text-gray-200 shadow-sm hover:bg-gray-100 dark:hover:bg-[#303030] transition"
                                    >
                                        <CheckCircle2 className="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                                        Asistencia
                                    </Link>

                                    <Link
                                        href={route('teacher.grades', section.id)}
                                        className="inline-flex items-center justify-center gap-1.5 rounded-lg py-2 text-xs font-bold text-white shadow-sm hover:opacity-90 transition"
                                        style={{ backgroundColor: primaryColor }}
                                    >
                                        <Award className="h-3.5 w-3.5 text-white" />
                                        Calificaciones
                                    </Link>
                                </div>
                            </div>
                        ))}

                        {sections.length === 0 && (
                            <div className="col-span-3 rounded-2xl border border-dashed border-gray-200 dark:border-[#262626] p-12 text-center text-xs text-slate-400 dark:text-neutral-500 bg-white dark:bg-[#171717]">
                                No tienes secciones asignadas en este momento.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
