import React from 'react';
import { SegmentedControl, ProgressFoldButton } from '@/components/godui';
import { ChevronLeft, ChevronRight, Calendar as CalendarIcon, Plus, Filter, DoorOpen, GraduationCap } from 'lucide-react';
import { MONTHS_ES } from './config';

export default function CalendarHeader({
    currentView,
    onViewChange,
    selectedDate,
    onNavigate,
    onToday,
    filters,
    onToggleFilter,
    classrooms = [],
    teachers = [],
    onClassroomChange,
    onTeacherChange,
    onOpenNewEvent,
}) {
    // Generar etiqueta descriptiva según la vista actual
    const getHeadingLabel = () => {
        const d = new Date(selectedDate);
        const year = d.getUTCFullYear();
        const monthName = MONTHS_ES[d.getUTCMonth()];
        const day = d.getUTCDate();

        if (currentView === 'month') {
            return `${monthName} ${year}`;
        }
        if (currentView === 'week') {
            const dayOfWeek = d.getUTCDay();
            const diffToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
            const monday = new Date(d);
            monday.setUTCDate(d.getUTCDate() + diffToMonday);
            const sunday = new Date(monday);
            sunday.setUTCDate(monday.getUTCDate() + 6);

            const mMonth = MONTHS_ES[monday.getUTCMonth()].slice(0, 3);
            const sMonth = MONTHS_ES[sunday.getUTCMonth()].slice(0, 3);

            if (monday.getUTCMonth() === sunday.getUTCMonth()) {
                return `${monday.getUTCDate()} - ${sunday.getUTCDate()} ${mMonth} ${year}`;
            }
            return `${monday.getUTCDate()} ${mMonth} - ${sunday.getUTCDate()} ${sMonth} ${year}`;
        }
        return `${day} de ${monthName} de ${year}`;
    };

    return (
        <div className="flex flex-col gap-4 border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900/60 p-4 lg:p-6 rounded-2xl shadow-sm transition-all">
            {/* Fila Superior: Título, Navegación y Acciones */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {/* Lado izquierdo: Navegación & Fecha */}
                <div className="flex items-center flex-wrap gap-2.5">
                    <button
                        type="button"
                        onClick={onToday}
                        className="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200/80 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 transition"
                    >
                        Hoy
                    </button>

                    <div className="inline-flex items-center rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs">
                        <button
                            type="button"
                            onClick={() => onNavigate('prev')}
                            title="Anterior"
                            className="p-1.5 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white transition rounded-l-lg hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        >
                            <ChevronLeft className="w-4 h-4" />
                        </button>
                        <div className="w-[1px] h-4 bg-gray-200 dark:bg-gray-700" />
                        <button
                            type="button"
                            onClick={() => onNavigate('next')}
                            title="Siguiente"
                            className="p-1.5 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white transition rounded-r-lg hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        >
                            <ChevronRight className="w-4 h-4" />
                        </button>
                    </div>

                    <h2 className="text-xl font-bold tracking-tight text-gray-900 dark:text-white ml-1">
                        {getHeadingLabel()}
                    </h2>
                </div>

                {/* Lado derecho: Selector de Vistas Untitled UI + Botón Nueva Actividad */}
                <div className="flex items-center gap-3">
                    <SegmentedControl
                        size="sm"
                        value={currentView}
                        onChange={onViewChange}
                        options={[
                            { value: 'month', label: 'Mes' },
                            { value: 'week', label: 'Semana' },
                            { value: 'day', label: 'Día' }
                        ]}
                    />

                    <ProgressFoldButton
                        variant="primary"
                        size="sm"
                        onClick={onOpenNewEvent}
                    >
                        <span className="inline-flex items-center gap-1.5">
                            <Plus className="w-3.5 h-3.5" />
                            <span className="hidden sm:inline">Nueva Actividad</span>
                            <span className="sm:hidden">Nuevo</span>
                        </span>
                    </ProgressFoldButton>
                </div>
            </div>

            {/* Fila Inferior: Filtros de Capas y Selectores de Aula/Docente */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800/80 text-xs">
                {/* Capas Activas */}
                <div className="flex items-center gap-2 flex-wrap">
                    <span className="inline-flex items-center gap-1 text-gray-400 dark:text-gray-500 font-medium mr-1">
                        <Filter className="w-3.5 h-3.5" />
                        Capas:
                    </span>

                    <button
                        type="button"
                        onClick={() => onToggleFilter('classes')}
                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer ${
                            filters.classes
                                ? 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800 font-medium'
                                : 'bg-transparent text-gray-400 border-dashed border-gray-200 dark:border-gray-700 hover:text-gray-600'
                        }`}
                    >
                        <span className={`w-2 h-2 rounded-full ${filters.classes ? 'bg-purple-500' : 'bg-gray-300'}`} />
                        Clases & Docencia
                    </button>

                    <button
                        type="button"
                        onClick={() => onToggleFilter('events')}
                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer ${
                            filters.events
                                ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800 font-medium'
                                : 'bg-transparent text-gray-400 border-dashed border-gray-200 dark:border-gray-700 hover:text-gray-600'
                        }`}
                    >
                        <span className={`w-2 h-2 rounded-full ${filters.events ? 'bg-blue-500' : 'bg-gray-300'}`} />
                        Eventos Académicos
                    </button>

                    <button
                        type="button"
                        onClick={() => onToggleFilter('system')}
                        className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer ${
                            filters.system
                                ? 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/50 dark:text-teal-300 dark:border-teal-800 font-medium'
                                : 'bg-transparent text-gray-400 border-dashed border-gray-200 dark:border-gray-700 hover:text-gray-600'
                        }`}
                    >
                        <span className={`w-2 h-2 rounded-full ${filters.system ? 'bg-teal-500' : 'bg-gray-300'}`} />
                        Hitos del Sistema
                    </button>
                </div>

                {/* Filtros específicos por Aula y Profesor */}
                <div className="flex items-center gap-2 flex-wrap">
                    {/* Filtro Aula */}
                    <div className="relative inline-flex items-center">
                        <DoorOpen className="w-3.5 h-3.5 text-gray-400 absolute left-2.5 pointer-events-none" />
                        <select
                            value={filters.classroom_id || ''}
                            onChange={(e) => onClassroomChange && onClassroomChange(e.target.value)}
                            className="pl-7 pr-7 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-hidden focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Todas las Aulas</option>
                            {classrooms.map(c => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filtro Docente */}
                    <div className="relative inline-flex items-center">
                        <GraduationCap className="w-3.5 h-3.5 text-gray-400 absolute left-2.5 pointer-events-none" />
                        <select
                            value={filters.teacher_id || ''}
                            onChange={(e) => onTeacherChange && onTeacherChange(e.target.value)}
                            className="pl-7 pr-7 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-hidden focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="">Todos los Docentes</option>
                            {teachers.map(t => (
                                <option key={t.id} value={t.id}>{t.name}</option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>
        </div>
    );
}
