import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import {
    BookOpen,
    ArrowLeft,
    CheckCircle2,
    Clock,
    User,
    MapPin,
    AlertCircle,
    Plus,
    Minus,
    Check,
    Calendar,
    Sparkles,
    Search
} from 'lucide-react';

export default function SubjectSelection({
    auth,
    student,
    career,
    availableSchedules = [],
    enrolledScheduleIds = [],
    maxCredits = 20
}) {
    const [selectedIds, setSelectedIds] = useState(enrolledScheduleIds || []);
    const [searchQuery, setSearchQuery] = useState('');
    const [processing, setProcessing] = useState(false);

    // Filter available schedules
    const filteredSchedules = availableSchedules.filter(s =>
        s.subject_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        s.subject_code.toLowerCase().includes(searchQuery.toLowerCase()) ||
        s.teacher.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Selected schedules objects
    const selectedSchedules = availableSchedules.filter(s => selectedIds.includes(s.id));
    const totalCredits = selectedSchedules.reduce((acc, curr) => acc + (curr.credits || 3), 0);

    const toggleSchedule = (sched) => {
        if (selectedIds.includes(sched.id)) {
            setSelectedIds(selectedIds.filter(id => id !== sched.id));
        } else {
            // Check if already selected a section for this same subject
            const alreadyHasSubject = selectedSchedules.some(s => s.module_id === sched.module_id);
            if (alreadyHasSubject) {
                alert(`Ya has seleccionado una sección para "${sched.subject_name}". Deselecciónala primero si deseas cambiar de horario.`);
                return;
            }

            // Check credits
            if (totalCredits + (sched.credits || 3) > maxCredits) {
                alert(`Límite de créditos excedido. El máximo permitido por período es de ${maxCredits} créditos.`);
                return;
            }

            setSelectedIds([...selectedIds, sched.id]);
        }
    };

    const handleConfirmSelection = () => {
        if (selectedIds.length === 0) {
            alert('Debes seleccionar al menos una asignatura para confirmar.');
            return;
        }

        setProcessing(true);
        router.post(route('student.selection.store'), {
            schedule_ids: selectedIds,
        }, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <AuthenticatedLayout auth={auth} title="Selección de Asignaturas">
            <Head title="Selección de Asignaturas" />

            <div className="space-y-6 pb-20">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            href={route('student.dashboard')}
                            className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Volver al Portal
                        </Link>
                        <h1 className="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            Selección de Asignaturas & Horarios
                        </h1>
                        
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                            <Sparkles className="h-3.5 w-3.5 text-amber-500" />
                            Máximo {maxCredits} Créditos
                        </span>
                    </div>
                </div>

                {/* Main Content Layout */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Left: Available Schedules List */}
                    <div className="lg:col-span-2 space-y-4">
                        {/* Search bar */}
                        <div className="relative">
                            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input
                                type="text"
                                placeholder="Buscar asignatura por nombre, código o docente..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2.5 text-xs font-medium text-slate-800 shadow-sm focus:border-brand-primary focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                            />
                        </div>

                        {/* Schedules Grid */}
                        <div className="space-y-3">
                            {filteredSchedules.map(sched => {
                                const isSelected = selectedIds.includes(sched.id);

                                return (
                                    <div
                                        key={sched.id}
                                        className={`rounded-xl border p-4 shadow-sm transition ${
                                            isSelected
                                                ? 'border-brand-primary bg-blue-50/30 dark:border-blue-500 dark:bg-blue-950/20'
                                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900'
                                        }`}
                                    >
                                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <span className="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                        {sched.subject_code}
                                                    </span>
                                                    <span className="text-xs font-semibold text-slate-400">
                                                        {sched.credits} Créditos
                                                    </span>
                                                    <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                        {sched.available_seats} cupos disp.
                                                    </span>
                                                </div>

                                                <h3 className="mt-1 text-base font-bold text-slate-900 dark:text-white">
                                                    {sched.subject_name}
                                                </h3>

                                                <div className="mt-2 flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                                    <span className="flex items-center gap-1">
                                                        <User className="h-3.5 w-3.5" />
                                                        {sched.teacher}
                                                    </span>
                                                    <span className="flex items-center gap-1">
                                                        <MapPin className="h-3.5 w-3.5" />
                                                        {sched.classroom}
                                                    </span>
                                                    <span className="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                                        <Clock className="h-3.5 w-3.5 text-brand-primary" />
                                                        {sched.days} &bull; {sched.start_time} - {sched.end_time}
                                                    </span>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                onClick={() => toggleSchedule(sched)}
                                                className={`inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-bold transition active:scale-95 ${
                                                    isSelected
                                                        ? 'bg-rose-600 text-white hover:bg-rose-700'
                                                        : 'bg-brand-primary text-white hover:bg-[#082260]'
                                                }`}
                                            >
                                                {isSelected ? (
                                                    <>
                                                        <Minus className="h-3.5 w-3.5" />
                                                        Quitar
                                                    </>
                                                ) : (
                                                    <>
                                                        <Plus className="h-3.5 w-3.5" />
                                                        Seleccionar
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                );
                            })}

                            {filteredSchedules.length === 0 && (
                                <div className="rounded-xl border border-dashed border-slate-200 p-8 text-center text-xs text-slate-400 dark:border-slate-800">
                                    No hay secciones disponibles que coincidan con la búsqueda.
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right: Selection Basket Summary */}
                    <div className="space-y-4">
                        <div className="sticky top-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg dark:border-slate-800 dark:bg-slate-900 space-y-4">
                            <div className="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Resumen de Selección
                                </h3>
                                <span className="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {selectedSchedules.length} materias
                                </span>
                            </div>

                            {/* Credits Gauge */}
                            <div className="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/40">
                                <div className="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-400">
                                    <span>Carga de Créditos</span>
                                    <span className="font-mono text-sm font-black text-slate-900 dark:text-white">
                                        {totalCredits} / {maxCredits}
                                    </span>
                                </div>
                                <div className="mt-2 h-2 w-full rounded-full bg-slate-200 overflow-hidden dark:bg-slate-700">
                                    <div
                                        className={`h-full transition-all duration-300 rounded-full ${
                                            totalCredits > maxCredits ? 'bg-rose-500' : 'bg-brand-primary'
                                        }`}
                                        style={{ width: `${Math.min(100, (totalCredits / maxCredits) * 100)}%` }}
                                    />
                                </div>
                            </div>

                            {/* Selected items list */}
                            <div className="space-y-2 max-h-60 overflow-y-auto">
                                {selectedSchedules.map(sched => (
                                    <div key={sched.id} className="flex items-center justify-between rounded-lg border border-slate-100 p-2.5 text-xs dark:border-slate-800">
                                        <div>
                                            <p className="font-bold text-slate-900 dark:text-white">{sched.subject_name}</p>
                                            <p className="text-[10px] text-slate-400">{sched.days} &bull; {sched.credits} crs.</p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => toggleSchedule(sched)}
                                            className="text-slate-400 hover:text-rose-600 p-1"
                                        >
                                            <Minus className="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                ))}

                                {selectedSchedules.length === 0 && (
                                    <p className="py-6 text-center text-xs text-slate-400">
                                        Aún no has agregado asignaturas a tu selección.
                                    </p>
                                )}
                            </div>

                            {/* Confirm Button */}
                            <button
                                type="button"
                                onClick={handleConfirmSelection}
                                disabled={selectedSchedules.length === 0 || processing}
                                className="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-brand-primary py-3 text-xs font-bold text-white shadow-md hover:bg-[#082260] active:scale-95 disabled:opacity-50 transition"
                            >
                                <Check className="h-4 w-4" />
                                {processing ? 'Confirmando...' : 'Confirmar Selección de Asignaturas'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
