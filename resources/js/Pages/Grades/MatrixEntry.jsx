import React, { useState, useEffect, useRef } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    Award,
    CheckCircle2,
    XCircle,
    AlertCircle,
    Lock,
    Unlock,
    Save,
    Download,
    RefreshCw,
    SlidersHorizontal,
    Table,
    FileSpreadsheet,
    ArrowRight,
    TrendingUp,
    Users,
    Sparkles,
    Calendar,
    BookOpen
} from 'lucide-react';

export default function MatrixEntry({
    auth,
    activeYear,
    sections = [],
    periods = [],
    sectionSubjects = [],
    selectedSectionId,
    selectedPeriodId,
    selectedSubjectId,
    students = [],
    stats = {},
    isLocked = false,
    viewMode = 'single',
    overviewMatrix = []
}) {
    // Local editable copy of grades
    const [gradesData, setGradesData] = useState([]);
    const [dirty, setDirty] = useState(false);
    const [saving, setSaving] = useState(false);
    const [filterQuery, setFilterQuery] = useState('');
    const inputRefs = useRef([]);

    useEffect(() => {
        setGradesData(students.map(s => ({
            student_id: s.student_id,
            student_code: s.student_code,
            student_name: s.student_name,
            score: s.score ?? '',
            observations: s.observations ?? '',
            dirty: false,
        })));
        setDirty(false);
    }, [students]);

    // Handle parameter switch
    const handleFilterChange = (params) => {
        if (dirty) {
            if (!confirm('Tiene cambios sin guardar en esta sección. ¿Desea descartarlos y continuar?')) {
                return;
            }
        }
        router.get(route('admin.grades.matrix'), {
            section_id: params.section_id !== undefined ? params.section_id : selectedSectionId,
            period_id: params.period_id !== undefined ? params.period_id : selectedPeriodId,
            section_subject_id: params.section_subject_id !== undefined ? params.section_subject_id : selectedSubjectId,
            view_mode: params.view_mode !== undefined ? params.view_mode : viewMode,
        }, { preserveState: false, preserveScroll: true });
    };

    // Calculate live minerd performance level
    const getPerformanceLevel = (val) => {
        if (val === '' || val === null || isNaN(val)) return null;
        const num = parseFloat(val);
        if (num >= 89) return { label: 'Destacado', badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' };
        if (num >= 77) return { label: 'Logro Evidenciado', badge: 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' };
        if (num >= 65) return { label: 'En Proceso', badge: 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' };
        return { label: 'Insuficiente', badge: 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' };
    };

    const handleScoreChange = (index, value) => {
        if (isLocked) return;
        const updated = [...gradesData];
        // Allow empty or number between 0 and 100
        if (value === '' || (!isNaN(value) && parseFloat(value) >= 0 && parseFloat(value) <= 100)) {
            updated[index].score = value;
            updated[index].dirty = true;
            setGradesData(updated);
            setDirty(true);
        }
    };

    const handleObsChange = (index, value) => {
        if (isLocked) return;
        const updated = [...gradesData];
        updated[index].observations = value;
        updated[index].dirty = true;
        setGradesData(updated);
        setDirty(true);
    };

    const handleKeyDown = (e, index) => {
        if (e.key === 'ArrowDown' || e.key === 'Enter') {
            e.preventDefault();
            if (index + 1 < gradesData.length) {
                inputRefs.current[index + 1]?.focus();
                inputRefs.current[index + 1]?.select();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (index - 1 >= 0) {
                inputRefs.current[index - 1]?.focus();
                inputRefs.current[index - 1]?.select();
            }
        }
    };

    const handleQuickFill = (targetScore) => {
        if (isLocked) return;
        if (!confirm(`¿Desea asignar una calificación de ${targetScore} a todos los alumnos sin nota registrada?`)) return;
        const updated = gradesData.map(g => {
            if (g.score === '' || g.score === null) {
                return { ...g, score: targetScore.toString(), dirty: true };
            }
            return g;
        });
        setGradesData(updated);
        setDirty(true);
    };

    const handleSaveGrades = (e) => {
        if (e) e.preventDefault();
        if (isLocked) {
            alert('El período seleccionado se encuentra bloqueado para modificaciones.');
            return;
        }

        setSaving(true);
        router.post(route('admin.grades.matrix.save'), {
            section_id: selectedSectionId,
            period_id: selectedPeriodId,
            section_subject_id: selectedSubjectId,
            grades: gradesData.map(g => ({
                student_id: g.student_id,
                score: g.score === '' ? null : parseFloat(g.score),
                observations: g.observations,
            })),
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setDirty(false);
                setSaving(false);
            },
            onError: () => setSaving(false),
        });
    };

    // Keyboard shortcut Ctrl+S / Cmd+S
    useEffect(() => {
        const handleGlobalKeyDown = (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                if (dirty && !isLocked) {
                    handleSaveGrades();
                }
            }
        };
        window.addEventListener('keydown', handleGlobalKeyDown);
        return () => window.removeEventListener('keydown', handleGlobalKeyDown);
    }, [dirty, isLocked, gradesData]);

    // Export to CSV
    const exportCSV = () => {
        let headers = ['Matrícula', 'Estudiante', 'Calificación', 'Nivel', 'Observación'];
        let rows = gradesData.map(g => {
            const lvl = getPerformanceLevel(g.score);
            return [
                `"${g.student_code}"`,
                `"${g.student_name}"`,
                g.score !== '' ? g.score : '0',
                `"${lvl?.label ?? 'Sin calificar'}"`,
                `"${g.observations.replace(/"/g, '""')}"`
            ].join(',');
        });
        const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `Calificaciones_Sec_${selectedSectionId}_Per_${selectedPeriodId}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const filteredGrades = gradesData.filter(g =>
        g.student_name.toLowerCase().includes(filterQuery.toLowerCase()) ||
        g.student_code.toLowerCase().includes(filterQuery.toLowerCase())
    );

    const currentPeriod = periods.find(p => p.id === selectedPeriodId);
    const currentSubject = sectionSubjects.find(s => s.id === selectedSubjectId);
    const currentSection = sections.find(s => s.id === selectedSectionId);

    return (
        <AuthenticatedLayout auth={auth}>
            <Head title="Sábana de Calificaciones & Registro" />

            <div className="space-y-6 pb-20">
                {/* Header & Quick Action Bar */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="inline-flex items-center gap-1.5 rounded-md bg-brand-primary/10 px-2.5 py-1 text-xs font-semibold text-brand-primary dark:bg-blue-900/30 dark:text-blue-300">
                                <Award className="h-3.5 w-3.5" />
                                MINERD Ordenanza 04-2023
                            </span>
                            {activeYear && (
                                <span className="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <Calendar className="h-3 w-3" />
                                    Año {activeYear.name}
                                </span>
                            )}
                        </div>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Entrada de Calificaciones & Sábana de Notas
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <div className="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <button
                                type="button"
                                onClick={() => handleFilterChange({ view_mode: 'single' })}
                                className={`inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition ${
                                    viewMode === 'single'
                                        ? 'bg-brand-primary text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                }`}
                            >
                                <BookOpen className="h-3.5 w-3.5" />
                                Por Asignatura
                            </button>
                            <button
                                type="button"
                                onClick={() => handleFilterChange({ view_mode: 'matrix' })}
                                className={`inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-semibold transition ${
                                    viewMode === 'matrix'
                                        ? 'bg-brand-primary text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                }`}
                            >
                                <Table className="h-3.5 w-3.5" />
                                Sábana General
                            </button>
                        </div>

                        <Link
                            href={route('admin.grades.locks')}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            <Lock className="h-3.5 w-3.5 text-slate-500" />
                            Cierre de Actas
                        </Link>

                        <button
                            type="button"
                            onClick={exportCSV}
                            disabled={gradesData.length === 0}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Download className="h-3.5 w-3.5 text-slate-500" />
                            Exportar CSV
                        </button>
                    </div>
                </div>

                {/* Filter Selector Panel */}
                <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        {/* Section Selector */}
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Sección Escolar
                            </label>
                            <select
                                value={selectedSectionId || ''}
                                onChange={(e) => handleFilterChange({ section_id: e.target.value })}
                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">Seleccionar Sección...</option>
                                {sections.map(s => (
                                    <option key={s.id} value={s.id}>
                                        {s.full_label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Period Selector */}
                        <div>
                            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                Período Evaluativo
                            </label>
                            <select
                                value={selectedPeriodId || ''}
                                onChange={(e) => handleFilterChange({ period_id: e.target.value })}
                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">Seleccionar Período...</option>
                                {periods.map(p => (
                                    <option key={p.id} value={p.id}>
                                        {p.name} {p.is_locked ? '(Bloqueado)' : ''}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Subject Selector (Single Mode) */}
                        {viewMode === 'single' ? (
                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Asignatura
                                </label>
                                <select
                                    value={selectedSubjectId || ''}
                                    onChange={(e) => handleFilterChange({ section_subject_id: e.target.value })}
                                    className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="">Seleccionar Asignatura...</option>
                                    {sectionSubjects.map(ss => (
                                        <option key={ss.id} value={ss.id}>
                                            {ss.code ? `[${ss.code}] ` : ''}{ss.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        ) : (
                            <div>
                                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                    Filtro de Búsqueda
                                </label>
                                <input
                                    type="text"
                                    placeholder="Buscar estudiante por nombre o matrícula..."
                                    value={filterQuery}
                                    onChange={(e) => setFilterQuery(e.target.value)}
                                    className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none focus:ring-1 focus:ring-brand-primary dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                            </div>
                        )}
                    </div>
                </div>

                {/* Locked Period Warning Banner */}
                {isLocked && (
                    <div className="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50/80 p-4 text-rose-900 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                        <Lock className="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
                        <div className="text-sm">
                            <span className="font-bold">Acta Cerrada / Período Bloqueado:</span> Este período de evaluación se encuentra cerrado oficialmente para edición de docentes. Solo administradores pueden realizar rectificaciones.
                        </div>
                    </div>
                )}

                {/* Single Subject View */}
                {viewMode === 'single' && (
                    <>
                        {/* Live Statistical Metric Cards */}
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-5">
                            <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span className="text-xs font-semibold uppercase tracking-wider">Estudiantes</span>
                                    <Users className="h-4 w-4" />
                                </div>
                                <div className="mt-2 flex items-baseline gap-2">
                                    <span className="text-2xl font-black text-slate-900 dark:text-white">
                                        {stats.graded_count || 0}
                                    </span>
                                    <span className="text-xs font-medium text-slate-500">
                                        / {stats.total_students || 0} calificados
                                    </span>
                                </div>
                                <div className="mt-2 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden dark:bg-slate-800">
                                    <div
                                        className="h-full bg-brand-primary rounded-full transition-all duration-500"
                                        style={{ width: `${stats.total_students > 0 ? (stats.graded_count / stats.total_students) * 100 : 0}%` }}
                                    />
                                </div>
                            </div>

                            <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span className="text-xs font-semibold uppercase tracking-wider">Promedio</span>
                                    <TrendingUp className="h-4 w-4 text-blue-600" />
                                </div>
                                <div className="mt-2 flex items-baseline gap-2">
                                    <span className={`text-2xl font-black ${
                                        (stats.average_score || 0) >= 70 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'
                                    }`}>
                                        {stats.average_score || '0.0'}
                                    </span>
                                    <span className="text-xs font-medium text-slate-400">/ 100 pts</span>
                                </div>
                                <p className="mt-1 text-[11px] text-slate-500">
                                    Rango: {stats.min_score || 0} - {stats.max_score || 0} pts
                                </p>
                            </div>

                            <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span className="text-xs font-semibold uppercase tracking-wider">Aprobados</span>
                                    <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                                </div>
                                <div className="mt-2 flex items-baseline gap-2">
                                    <span className="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                        {stats.passing_rate || 0}%
                                    </span>
                                    <span className="text-xs font-medium text-slate-500">
                                        ({stats.passing_count || 0} alumnos)
                                    </span>
                                </div>
                                <p className="mt-1 text-[11px] text-slate-500">
                                    Nota de pase: &ge; 70 pts
                                </p>
                            </div>

                            <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <div className="flex items-center justify-between text-slate-500 dark:text-slate-400">
                                    <span className="text-xs font-semibold uppercase tracking-wider">Reprobados</span>
                                    <XCircle className="h-4 w-4 text-rose-500" />
                                </div>
                                <div className="mt-2 flex items-baseline gap-2">
                                    <span className="text-2xl font-black text-rose-600 dark:text-rose-400">
                                        {stats.failing_count || 0}
                                    </span>
                                    <span className="text-xs font-medium text-slate-500">alumnos (&lt;70)</span>
                                </div>
                                <p className="mt-1 text-[11px] text-slate-500">
                                    Pendiente recuperación
                                </p>
                            </div>

                            <div className="col-span-2 sm:col-span-4 lg:col-span-1 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 flex flex-col justify-center">
                                <span className="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                                    Acciones Rápidas
                                </span>
                                <div className="flex flex-col gap-2">
                                    <button
                                        type="button"
                                        disabled={isLocked}
                                        onClick={() => handleQuickFill(70)}
                                        className="w-full text-left inline-flex items-center justify-between rounded-md bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-50 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                    >
                                        <span>Llenar vacíos con 70</span>
                                        <Sparkles className="h-3 w-3 text-amber-500" />
                                    </button>
                                    <button
                                        type="button"
                                        disabled={isLocked}
                                        onClick={() => handleQuickFill(100)}
                                        className="w-full text-left inline-flex items-center justify-between rounded-md bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-50 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                    >
                                        <span>Llenar vacíos con 100</span>
                                        <Award className="h-3 w-3 text-emerald-500" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* MINERD Scale Legend */}
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-2.5 text-xs dark:border-slate-800 dark:bg-slate-900/40">
                            <span className="font-semibold text-slate-700 dark:text-slate-300">Escala Valorativa MINERD:</span>
                            <div className="flex flex-wrap items-center gap-4">
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2.5 w-2.5 rounded-full bg-emerald-500" />
                                    <span className="font-medium text-slate-600 dark:text-slate-400">89-100: Destacado ({stats.levels?.destacado || 0})</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2.5 w-2.5 rounded-full bg-blue-500" />
                                    <span className="font-medium text-slate-600 dark:text-slate-400">77-88: Logro Evidenciado ({stats.levels?.logro_evidenciado || 0})</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2.5 w-2.5 rounded-full bg-amber-500" />
                                    <span className="font-medium text-slate-600 dark:text-slate-400">65-76: En Proceso ({stats.levels?.en_proceso || 0})</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <span className="h-2.5 w-2.5 rounded-full bg-rose-500" />
                                    <span className="font-medium text-slate-600 dark:text-slate-400">&lt;65: Insuficiente ({stats.levels?.insuficiente || 0})</span>
                                </div>
                            </div>
                        </div>

                        {/* Interactive Excel-like Grade Grid */}
                        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div className="border-b border-slate-200 bg-slate-50/50 px-6 py-3 flex items-center justify-between dark:border-slate-800 dark:bg-slate-900/60">
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-bold text-slate-800 dark:text-slate-200">
                                        {currentSection ? currentSection.full_label : 'Sección'} &bull; {currentSubject ? currentSubject.name : 'Asignatura'}
                                    </span>
                                    <span className="rounded bg-slate-200/80 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {filteredGrades.length} estudiantes
                                    </span>
                                </div>
                                <span className="text-xs text-slate-400">
                                    Tip: Usa flechas &uarr; &darr; o Enter para desplazarte rápidamente entre casillas
                                </span>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                        <tr>
                                            <th className="px-4 py-3 text-center w-12">#</th>
                                            <th className="px-4 py-3 w-32">Matrícula</th>
                                            <th className="px-4 py-3">Estudiante</th>
                                            <th className="px-4 py-3 text-center w-36">Calificación (0-100)</th>
                                            <th className="px-4 py-3 text-center w-40">Nivel MINERD</th>
                                            <th className="px-4 py-3 text-center w-28">Estado</th>
                                            <th className="px-4 py-3">Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                        {filteredGrades.map((g, idx) => {
                                            const originalIndex = gradesData.findIndex(item => item.student_id === g.student_id);
                                            const perf = getPerformanceLevel(g.score);
                                            const num = g.score !== '' ? parseFloat(g.score) : null;
                                            const isPassing = num !== null && num >= 70;

                                            return (
                                                <tr
                                                    key={g.student_id}
                                                    className={`transition hover:bg-slate-50/80 dark:hover:bg-slate-800/50 ${
                                                        g.dirty ? 'bg-blue-50/40 dark:bg-blue-950/20' : ''
                                                    }`}
                                                >
                                                    <td className="px-4 py-2.5 text-center text-xs font-semibold text-slate-400">
                                                        {idx + 1}
                                                    </td>
                                                    <td className="px-4 py-2.5 font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                                                        {g.student_code}
                                                    </td>
                                                    <td className="px-4 py-2.5 font-medium text-slate-900 dark:text-white">
                                                        <div className="flex items-center gap-2">
                                                            {g.student_name}
                                                            {g.dirty && (
                                                                <span className="h-1.5 w-1.5 rounded-full bg-blue-600" title="Modificado sin guardar" />
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-2.5 text-center">
                                                        <input
                                                            ref={(el) => (inputRefs.current[originalIndex] = el)}
                                                            type="number"
                                                            min="0"
                                                            max="100"
                                                            step="0.1"
                                                            disabled={isLocked}
                                                            placeholder="--"
                                                            value={g.score}
                                                            onChange={(e) => handleScoreChange(originalIndex, e.target.value)}
                                                            onKeyDown={(e) => handleKeyDown(e, originalIndex)}
                                                            className={`w-28 rounded-lg border text-center text-sm font-bold shadow-sm transition focus:outline-none focus:ring-2 disabled:bg-slate-100 disabled:text-slate-400 dark:disabled:bg-slate-800 dark:disabled:text-slate-500 ${
                                                                num === null
                                                                    ? 'border-slate-300 text-slate-700 focus:border-brand-primary focus:ring-brand-primary/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                                                    : isPassing
                                                                    ? 'border-emerald-300 bg-emerald-50/30 text-emerald-900 focus:border-emerald-600 focus:ring-emerald-500/20 dark:border-emerald-800 dark:bg-emerald-950/20 dark:text-emerald-200'
                                                                    : 'border-rose-300 bg-rose-50/30 text-rose-900 focus:border-rose-600 focus:ring-rose-500/20 dark:border-rose-800 dark:bg-rose-950/20 dark:text-rose-200'
                                                            }`}
                                                        />
                                                    </td>
                                                    <td className="px-4 py-2.5 text-center">
                                                        {perf ? (
                                                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${perf.badge}`}>
                                                                {perf.label}
                                                            </span>
                                                        ) : (
                                                            <span className="text-xs text-slate-400">—</span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-2.5 text-center">
                                                        {num !== null ? (
                                                            isPassing ? (
                                                                <span className="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                                    Aprobado
                                                                </span>
                                                            ) : (
                                                                <span className="inline-flex items-center gap-1 text-xs font-bold text-rose-600 dark:text-rose-400">
                                                                    <XCircle className="h-3.5 w-3.5" />
                                                                    Reprobado
                                                                </span>
                                                            )
                                                        ) : (
                                                            <span className="text-xs font-medium text-slate-400">Pendiente</span>
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-2.5">
                                                        <input
                                                            type="text"
                                                            disabled={isLocked}
                                                            placeholder="Observaciones pedagógicas..."
                                                            value={g.observations}
                                                            onChange={(e) => handleObsChange(originalIndex, e.target.value)}
                                                            className="w-full rounded-md border border-slate-200 bg-transparent px-2.5 py-1 text-xs text-slate-700 placeholder:text-slate-400 focus:border-brand-primary focus:outline-none dark:border-slate-700 dark:text-slate-300"
                                                        />
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                        {filteredGrades.length === 0 && (
                                            <tr>
                                                <td colSpan={7} className="py-12 text-center text-sm text-slate-500">
                                                    No se encontraron estudiantes para la combinación seleccionada.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </>
                )}

                {/* Section Overview Matrix Mode (Sábana General) */}
                {viewMode === 'matrix' && (
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div className="border-b border-slate-200 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 dark:border-slate-800 dark:bg-slate-900/60">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Sábana Consolidada de la Sección {currentSection?.full_label}
                                </h3>
                                <p className="text-xs text-slate-500 dark:text-slate-400">
                                    Promedios acumulados por asignatura para todos los estudiantes matriculados.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => window.print()}
                                className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                            >
                                <FileSpreadsheet className="h-3.5 w-3.5 text-slate-500" />
                                Imprimir Sábana
                            </button>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-800">
                                    <tr>
                                        <th className="px-4 py-3 text-center w-12 sticky left-0 bg-slate-50 dark:bg-slate-800">#</th>
                                        <th className="px-4 py-3 w-32 sticky left-12 bg-slate-50 dark:bg-slate-800">Matrícula</th>
                                        <th className="px-4 py-3 w-64 sticky left-44 bg-slate-50 dark:bg-slate-800">Estudiante</th>
                                        {sectionSubjects.map(ss => (
                                            <th key={ss.id} className="px-4 py-3 text-center min-w-[120px]">
                                                {ss.code || ss.name}
                                            </th>
                                        ))}
                                        <th className="px-4 py-3 text-center w-28 bg-slate-100/70 font-black dark:bg-slate-800">
                                            Promedio
                                        </th>
                                        <th className="px-4 py-3 text-center w-28">Condición</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {overviewMatrix
                                        .filter(s =>
                                            s.student_name.toLowerCase().includes(filterQuery.toLowerCase()) ||
                                            s.student_code.toLowerCase().includes(filterQuery.toLowerCase())
                                        )
                                        .map((s, idx) => (
                                            <tr key={s.student_id} className="hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                                <td className="px-4 py-3 text-center text-xs font-semibold text-slate-400 sticky left-0 bg-white dark:bg-slate-900">
                                                    {idx + 1}
                                                </td>
                                                <td className="px-4 py-3 font-mono text-xs font-semibold text-slate-600 dark:text-slate-400 sticky left-12 bg-white dark:bg-slate-900">
                                                    {s.student_code}
                                                </td>
                                                <td className="px-4 py-3 font-medium text-slate-900 dark:text-white sticky left-44 bg-white dark:bg-slate-900">
                                                    {s.student_name}
                                                </td>
                                                {sectionSubjects.map(ss => {
                                                    const score = s.subject_scores[ss.id];
                                                    return (
                                                        <td key={ss.id} className="px-4 py-3 text-center font-mono text-xs font-semibold">
                                                            {score !== null && score !== undefined ? (
                                                                <span className={score >= 70 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}>
                                                                    {score}
                                                                </span>
                                                            ) : (
                                                                <span className="text-slate-300 dark:text-slate-600">—</span>
                                                            )}
                                                        </td>
                                                    );
                                                })}
                                                <td className="px-4 py-3 text-center font-black text-xs font-mono bg-slate-50 dark:bg-slate-800/60">
                                                    {s.overall_average !== null ? (
                                                        <span className={s.overall_average >= 70 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'}>
                                                            {s.overall_average}
                                                        </span>
                                                    ) : (
                                                        <span className="text-slate-400">—</span>
                                                    )}
                                                </td>
                                                <td className="px-4 py-3 text-center">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold ${
                                                        s.status === 'Aprobado'
                                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                            : s.status === 'Reprobado'
                                                            ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                                                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                                    }`}>
                                                        {s.status}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Floating Bottom Bar when changes exist */}
                {dirty && !isLocked && (
                    <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-4 rounded-2xl border border-slate-300 bg-white/95 px-6 py-3.5 shadow-2xl backdrop-blur-md dark:border-slate-700 dark:bg-slate-900/95">
                        <div className="flex items-center gap-2">
                            <span className="relative flex h-3 w-3">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-3 w-3 bg-brand-primary"></span>
                            </span>
                            <span className="text-sm font-bold text-slate-900 dark:text-white">
                                Tienes cambios sin guardar
                            </span>
                        </div>

                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => {
                                    setGradesData(students.map(s => ({
                                        student_id: s.student_id,
                                        student_code: s.student_code,
                                        student_name: s.student_name,
                                        score: s.score ?? '',
                                        observations: s.observations ?? '',
                                        dirty: false,
                                    })));
                                    setDirty(false);
                                }}
                                className="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                            >
                                Descartar
                            </button>

                            <button
                                type="button"
                                onClick={handleSaveGrades}
                                disabled={saving}
                                className="inline-flex items-center gap-2 rounded-lg bg-brand-primary px-5 py-2 text-sm font-bold text-white shadow-md hover:bg-[#082260] active:scale-95 disabled:opacity-50 transition"
                            >
                                {saving ? (
                                    <>
                                        <RefreshCw className="h-4 w-4 animate-spin" />
                                        Guardando...
                                    </>
                                ) : (
                                    <>
                                        <Save className="h-4 w-4" />
                                        Guardar Calificaciones
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
