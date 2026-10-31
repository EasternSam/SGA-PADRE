import React, { useMemo } from 'react';
import { MONTHS_ES, getEventStyle } from './config';
import { Clock, MapPin, User, BookOpen, Edit2, Trash2, Calendar as CalendarIcon, CheckCircle2 } from 'lucide-react';

export default function CalendarDayView({
    selectedDate,
    events,
    onSelectEvent,
    onSlotClick,
    onEditEvent,
    onDeleteEvent,
}) {
    const d = new Date(selectedDate);
    const day = d.getUTCDate();
    const month = MONTHS_ES[d.getUTCMonth()];
    const year = d.getUTCFullYear();
    const todayStr = new Date().toISOString().split('T')[0];
    const isToday = selectedDate === todayStr;

    // Filtrar eventos del día seleccionado
    const dayEvents = useMemo(() => {
        return events.filter(e => e.date === selectedDate);
    }, [events, selectedDate]);

    // Separar eventos de todo el día vs eventos con hora
    const allDayEvents = useMemo(() => dayEvents.filter(e => e.is_all_day || !e.start_time), [dayEvents]);

    // Agrupar eventos con horario por bloque horario
    const groupedTimedEvents = useMemo(() => {
        const timed = dayEvents.filter(e => !e.is_all_day && e.start_time);
        // Ordenar por hora de inicio
        timed.sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));

        const groups = {};
        timed.forEach(evt => {
            const slotKey = evt.start_time + (evt.end_time ? ` - ${evt.end_time}` : '');
            if (!groups[slotKey]) {
                groups[slotKey] = {
                    timeLabel: slotKey,
                    startTime: evt.start_time,
                    items: []
                };
            }
            groups[slotKey].items.push(evt);
        });

        return Object.values(groups);
    }, [dayEvents]);

    return (
        <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
            {/* Header del Día */}
            <div className="p-4 sm:p-6 border-b border-gray-200/80 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 flex items-center justify-between">
                <div>
                    <h3 className="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <CalendarIcon className="w-5 h-5 text-indigo-600" />
                        {day} de {month}, {year}
                    </h3>
                    <p className="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {dayEvents.length} actividad(es) programada(s) para esta jornada
                    </p>
                </div>
                {isToday && (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800 rounded-full shadow-2xs">
                        <span className="w-2 h-2 rounded-full bg-indigo-500 animate-pulse" />
                        Hoy
                    </span>
                )}
            </div>

            {/* Eventos de Todo el Día */}
            {allDayEvents.length > 0 && (
                <div className="p-4 sm:p-5 border-b border-gray-200/80 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20 space-y-2.5">
                    <span className="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">
                        Hitos y Actividades de Todo el Día
                    </span>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        {allDayEvents.map((evt, idx) => {
                            const style = getEventStyle(evt.type);
                            return (
                                <div
                                    key={idx}
                                    onClick={() => onSelectEvent && onSelectEvent(evt)}
                                    className={`p-3.5 rounded-xl border flex flex-col justify-between cursor-pointer transition hover:shadow-sm ${style.card}`}
                                >
                                    <div>
                                        <div className="flex items-center justify-between gap-2 mb-1">
                                            <h4 className="text-xs font-bold truncate">{evt.title}</h4>
                                            <span className={`text-[10px] px-2 py-0.5 rounded font-semibold ${style.badge}`}>
                                                {style.label}
                                            </span>
                                        </div>
                                        {evt.description && (
                                            <p className="text-[11px] opacity-80 mt-0.5 line-clamp-2">
                                                {evt.description}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Cronograma Horario Estructurado */}
            <div className="p-4 sm:p-6 space-y-6 max-h-[720px] overflow-y-auto scrollbar-thin">
                {groupedTimedEvents.length === 0 && allDayEvents.length === 0 ? (
                    <div className="text-center py-16 text-gray-400 dark:text-gray-500">
                        <Clock className="w-10 h-10 mx-auto mb-2 opacity-40" />
                        <p className="text-sm font-semibold">No hay actividades agendadas para esta fecha</p>
                        <p className="text-xs mt-1">Usa el botón "+ Nueva Actividad" para programar un evento.</p>
                    </div>
                ) : (
                    groupedTimedEvents.map((group, gIdx) => (
                        <div key={gIdx} className="space-y-3">
                            {/* Franja de Tiempo */}
                            <div className="flex items-center gap-3">
                                <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-gray-700">
                                    <Clock className="w-3.5 h-3.5 text-indigo-500" />
                                    <span>{group.timeLabel}</span>
                                </div>
                                <div className="flex-1 h-[1px] bg-gray-200/80 dark:border-gray-800" />
                                <span className="text-xs text-gray-400 font-medium">
                                    {group.items.length} clase(s)/evento(s)
                                </span>
                            </div>

                            {/* Grid de Tarjetas de ese Bloque */}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pl-2 sm:pl-4 border-l-2 border-indigo-100 dark:border-indigo-950">
                                {group.items.map((evt, idx) => {
                                    const style = getEventStyle(evt.type);
                                    return (
                                        <div
                                            key={idx}
                                            onClick={() => onSelectEvent && onSelectEvent(evt)}
                                            className={`p-3.5 rounded-xl border shadow-2xs hover:shadow-md transition-all cursor-pointer flex flex-col justify-between ${style.card}`}
                                        >
                                            <div>
                                                <div className="flex items-start justify-between gap-2 mb-1.5">
                                                    <div className="flex items-center gap-2 truncate">
                                                        <span className={`w-2 h-2 rounded-full shrink-0 ${style.dot}`} />
                                                        <h4 className="text-xs font-bold truncate">
                                                            {evt.title}
                                                        </h4>
                                                    </div>
                                                    <span className={`text-[10px] px-2 py-0.5 rounded-md font-semibold shrink-0 ${style.badge}`}>
                                                        {style.label}
                                                    </span>
                                                </div>

                                                {evt.description && (
                                                    <p className="text-[11px] opacity-80 mt-1 line-clamp-2">
                                                        {evt.description}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-2 text-[11px] opacity-90">
                                                <div className="flex items-center gap-3">
                                                    {evt.classroom && (
                                                        <span className="flex items-center gap-1 font-medium">
                                                            <MapPin className="w-3 h-3 text-gray-400" />
                                                            {evt.classroom}
                                                        </span>
                                                    )}
                                                    {evt.teacher && (
                                                        <span className="flex items-center gap-1 font-medium">
                                                            <User className="w-3 h-3 text-gray-400" />
                                                            {evt.teacher}
                                                        </span>
                                                    )}
                                                </div>

                                                {evt.is_editable && (
                                                    <div className="flex items-center gap-1">
                                                        <button
                                                            type="button"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                onEditEvent && onEditEvent(evt);
                                                            }}
                                                            className="p-1 hover:text-indigo-600 rounded"
                                                            title="Editar"
                                                        >
                                                            <Edit2 className="w-3 h-3" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                onDeleteEvent && onDeleteEvent(evt);
                                                            }}
                                                            className="p-1 hover:text-rose-600 rounded"
                                                            title="Eliminar"
                                                        >
                                                            <Trash2 className="w-3 h-3" />
                                                        </button>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}
