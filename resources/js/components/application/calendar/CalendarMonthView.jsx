import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { WEEKDAYS_ES, getEventStyle } from './config';
import { Clock, MapPin, User, ChevronRight } from 'lucide-react';

export default function CalendarMonthView({
    selectedDate,
    events,
    onSelectDate,
    onSelectEvent,
    onViewDay,
}) {
    const [overflowDay, setOverflowDay] = useState(null);

    const baseDate = new Date(selectedDate);
    const year = baseDate.getUTCFullYear();
    const month = baseDate.getUTCMonth();

    // Primer día del mes
    const firstDayOfMonth = new Date(Date.UTC(year, month, 1));
    const lastDayOfMonth = new Date(Date.UTC(year, month + 1, 0));

    // Ajustar a Lunes (1) como inicio de semana
    let startDayOfWeek = firstDayOfMonth.getUTCDay();
    // En JS 0 = Domingo, 1 = Lunes... Si es domingo(0), shift a 7 para que lunes sea 1
    const shift = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

    const calendarCells = [];

    // Días del mes previo
    const prevMonthLastDate = new Date(Date.UTC(year, month, 0)).getUTCDate();
    for (let i = shift - 1; i >= 0; i--) {
        const d = prevMonthLastDate - i;
        const prevDate = new Date(Date.UTC(year, month - 1, d));
        calendarCells.push({
            date: prevDate.toISOString().split('T')[0],
            dayNumber: d,
            isCurrentMonth: false,
        });
    }

    // Días del mes actual
    const todayStr = new Date().toISOString().split('T')[0];
    for (let d = 1; d <= lastDayOfMonth.getUTCDate(); d++) {
        const curDate = new Date(Date.UTC(year, month, d));
        const dateStr = curDate.toISOString().split('T')[0];
        calendarCells.push({
            date: dateStr,
            dayNumber: d,
            isCurrentMonth: true,
            isToday: dateStr === todayStr,
        });
    }

    // Días del siguiente mes para completar la cuadrícula (múltiplo de 7)
    const remaining = (7 - (calendarCells.length % 7)) % 7;
    for (let i = 1; i <= remaining; i++) {
        const nextDate = new Date(Date.UTC(year, month + 1, i));
        calendarCells.push({
            date: nextDate.toISOString().split('T')[0],
            dayNumber: i,
            isCurrentMonth: false,
        });
    }

    // Agrupar eventos por fecha
    const eventsByDate = {};
    events.forEach(evt => {
        if (!eventsByDate[evt.date]) eventsByDate[evt.date] = [];
        eventsByDate[evt.date].push(evt);
    });

    return (
        <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
            {/* Cabecera de Días de la Semana */}
            <div className="grid grid-cols-7 border-b border-gray-200/80 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 text-center py-2.5">
                {WEEKDAYS_ES.map(wd => (
                    <div key={wd.short} className="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        <span className="hidden sm:inline">{wd.long}</span>
                        <span className="sm:hidden">{wd.short}</span>
                    </div>
                ))}
            </div>

            {/* Matriz Cuadrícula de 7 Columnas */}
            <div className="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-gray-200/60 dark:divide-gray-800/60 bg-gray-200/30 dark:bg-gray-800/30">
                {calendarCells.map((cell, idx) => {
                    const dayEvents = eventsByDate[cell.date] || [];
                    const maxDisplay = 3;
                    const visibleEvents = dayEvents.slice(0, maxDisplay);
                    const overflowCount = dayEvents.length - maxDisplay;

                    return (
                        <div
                            key={idx}
                            onClick={() => onSelectDate && onSelectDate(cell.date)}
                            className={`min-h-[110px] sm:min-h-[135px] p-1.5 sm:p-2 flex flex-col justify-between transition-colors relative group ${
                                cell.isCurrentMonth
                                    ? 'bg-white dark:bg-gray-900 hover:bg-gray-50/80 dark:hover:bg-gray-800/40'
                                    : 'bg-gray-50/40 dark:bg-gray-900/40 opacity-55'
                            }`}
                        >
                            {/* Número del Día y Acción Rápida */}
                            <div className="flex items-center justify-between mb-1">
                                <span
                                    className={`inline-flex items-center justify-center text-xs font-semibold rounded-full w-6 h-6 transition ${
                                        cell.isToday
                                            ? 'bg-indigo-600 text-white shadow-xs'
                                            : cell.isCurrentMonth
                                            ? 'text-gray-900 dark:text-gray-100 group-hover:text-indigo-600'
                                            : 'text-gray-400 dark:text-gray-600'
                                    }`}
                                >
                                    {cell.dayNumber}
                                </span>

                                {cell.isCurrentMonth && dayEvents.length > 0 && (
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            onViewDay && onViewDay(cell.date);
                                        }}
                                        title="Ver detalles de este día"
                                        className="opacity-0 group-hover:opacity-100 p-0.5 text-gray-400 hover:text-indigo-600 transition rounded"
                                    >
                                        <ChevronRight className="w-3.5 h-3.5" />
                                    </button>
                                )}
                            </div>

                            {/* Lista de Eventos y Clases */}
                            <div className="flex-1 flex flex-col gap-1 overflow-hidden">
                                {visibleEvents.map((evt, eIdx) => {
                                    const style = getEventStyle(evt.type);
                                    return (
                                        <div
                                            key={eIdx}
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                onSelectEvent && onSelectEvent(evt);
                                            }}
                                            className={`group/pill px-1.5 py-0.5 rounded text-[11px] font-medium truncate flex items-center gap-1.5 border shadow-2xs cursor-pointer transition active:scale-[0.98] ${style.card}`}
                                            title={`${evt.title} (${evt.start_time || 'Todo el día'})`}
                                        >
                                            <span className={`w-1.5 h-1.5 rounded-full shrink-0 ${style.dot}`} />
                                            {evt.start_time && (
                                                <span className="text-[10px] font-semibold opacity-75 shrink-0">
                                                    {evt.start_time}
                                                </span>
                                            )}
                                            <span className="truncate">{evt.title}</span>
                                        </div>
                                    );
                                })}

                                {overflowCount > 0 && (
                                    <button
                                        type="button"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            setOverflowDay({ date: cell.date, events: dayEvents });
                                        }}
                                        className="text-left text-[11px] font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 px-1 py-0.5 hover:underline"
                                    >
                                        +{overflowCount} más...
                                    </button>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Modal / Popover de Overflow para días con muchos eventos */}
            {overflowDay && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
                    <div className="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl w-full max-w-md p-5 animate-in fade-in zoom-in-95">
                        <div className="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-3">
                            <h3 className="text-sm font-bold text-gray-900 dark:text-white">
                                Eventos para {overflowDay.date} ({overflowDay.events.length})
                            </h3>
                            <button
                                type="button"
                                onClick={() => setOverflowDay(null)}
                                className="text-gray-400 hover:text-gray-600 text-xs font-semibold px-2 py-1 rounded"
                            >
                                Cerrar
                            </button>
                        </div>
                        <div className="max-h-80 overflow-y-auto space-y-2 pr-1">
                            {overflowDay.events.map((evt, idx) => {
                                const style = getEventStyle(evt.type);
                                return (
                                    <div
                                        key={idx}
                                        onClick={() => {
                                            setOverflowDay(null);
                                            onSelectEvent && onSelectEvent(evt);
                                        }}
                                        className={`p-2.5 rounded-xl border cursor-pointer hover:shadow-xs transition ${style.card}`}
                                    >
                                        <div className="flex items-center justify-between mb-1">
                                            <span className="font-semibold text-xs">{evt.title}</span>
                                            <span className={`text-[10px] px-1.5 py-0.2 rounded font-medium ${style.badge}`}>
                                                {style.label}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400">
                                            {evt.start_time && (
                                                <span className="flex items-center gap-1">
                                                    <Clock className="w-3 h-3" />
                                                    {evt.start_time} - {evt.end_time || ''}
                                                </span>
                                            )}
                                            {evt.classroom && (
                                                <span className="flex items-center gap-1">
                                                    <MapPin className="w-3 h-3" />
                                                    {evt.classroom}
                                                </span>
                                            )}
                                            {evt.teacher && (
                                                <span className="flex items-center gap-1">
                                                    <User className="w-3 h-3" />
                                                    {evt.teacher}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>, document.body)
            )}
        </div>
    );
}
