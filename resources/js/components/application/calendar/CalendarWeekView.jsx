import React, { useMemo } from 'react';
import { WEEKDAYS_ES, CALENDAR_START_HOUR, CALENDAR_END_HOUR, getEventStyle } from './config';
import { Clock, MapPin, User, ChevronRight } from 'lucide-react';

const HOUR_HEIGHT = 64; // 64px por hora

export default function CalendarWeekView({
    selectedDate,
    events,
    onSelectDate,
    onSelectEvent,
    onSlotClick,
    onViewDay,
}) {
    // Calcular los 7 días de la semana (Lunes a Domingo)
    const weekDays = useMemo(() => {
        const d = new Date(selectedDate);
        const dayOfWeek = d.getUTCDay();
        const diffToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
        const monday = new Date(d);
        monday.setUTCDate(d.getUTCDate() + diffToMonday);

        const days = [];
        const todayStr = new Date().toISOString().split('T')[0];

        for (let i = 0; i < 7; i++) {
            const cur = new Date(monday);
            cur.setUTCDate(monday.getUTCDate() + i);
            const dateStr = cur.toISOString().split('T')[0];
            days.push({
                date: dateStr,
                dayNumber: cur.getUTCDate(),
                dayName: WEEKDAYS_ES[i].long,
                dayShort: WEEKDAYS_ES[i].short,
                isToday: dateStr === todayStr,
            });
        }
        return days;
    }, [selectedDate]);

    // Generar horas para la regla (07:00 a 22:00)
    const hours = useMemo(() => {
        const list = [];
        for (let h = CALENDAR_START_HOUR; h <= CALENDAR_END_HOUR; h++) {
            const period = h >= 12 ? 'PM' : 'AM';
            const displayHour = h > 12 ? h - 12 : (h === 0 ? 12 : h);
            list.push({
                raw: h,
                label: `${displayHour}:00 ${period}`,
            });
        }
        return list;
    }, []);

    // Línea de hora actual "Ahora"
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    const isNowVisible = currentHour >= CALENDAR_START_HOUR && currentHour <= CALENDAR_END_HOUR;
    const nowTopPosition = (currentHour - CALENDAR_START_HOUR + currentMinute / 60) * HOUR_HEIGHT;

    // Separar eventos de todo el día vs eventos con hora específica
    const { allDayEvents, timedEventsByDate } = useMemo(() => {
        const allDay = [];
        const timed = {};

        events.forEach(evt => {
            if (evt.is_all_day || !evt.start_time) {
                allDay.push(evt);
            } else {
                if (!timed[evt.date]) timed[evt.date] = [];
                timed[evt.date].push(evt);
            }
        });

        return { allDayEvents: allDay, timedEventsByDate: timed };
    }, [events]);

    // Calcular posición y agrupar por slot horario para no amontonar
    const getClusteredEventsForDay = (dayEvents, dateStr) => {
        // Agrupar por hora de inicio exacta
        const slotMap = {};
        dayEvents.forEach(evt => {
            const timeKey = evt.start_time || '08:00';
            if (!slotMap[timeKey]) slotMap[timeKey] = [];
            slotMap[timeKey].push(evt);
        });

        const clusters = [];
        Object.keys(slotMap).forEach(timeKey => {
            const items = slotMap[timeKey];
            const [sH, sM] = timeKey.split(':').map(Number);
            const startVal = Math.max(sH + sM / 60, CALENDAR_START_HOUR);
            const top = (startVal - CALENDAR_START_HOUR) * HOUR_HEIGHT;

            // Tomar el primer evento para determinar duración aproximada
            const firstEvt = items[0];
            let duration = 1.75;
            if (firstEvt.end_time) {
                const [eH, eM] = firstEvt.end_time.split(':').map(Number);
                duration = Math.max((eH + eM / 60) - startVal, 0.75);
            }
            const height = duration * HOUR_HEIGHT;

            clusters.push({
                timeKey,
                top,
                height,
                items,
                dateStr,
            });
        });

        return clusters;
    };

    return (
        <div className="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/80 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
            {/* Cabecera de Columnas: Días de la Semana */}
            <div className="flex border-b border-gray-200/80 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40">
                <div className="w-16 sm:w-20 shrink-0 border-r border-gray-200/80 dark:border-gray-800 flex items-center justify-center p-2 text-[11px] font-semibold text-gray-400">
                    GMT-4
                </div>

                <div className="flex-1 grid grid-cols-7 divide-x divide-gray-200/60 dark:divide-gray-800/60">
                    {weekDays.map((d, idx) => (
                        <div
                            key={idx}
                            onClick={() => onSelectDate && onSelectDate(d.date)}
                            className={`p-2 sm:p-2.5 text-center cursor-pointer transition ${
                                d.isToday ? 'bg-indigo-50/60 dark:bg-indigo-950/20' : 'hover:bg-gray-100/50 dark:hover:bg-gray-800/30'
                            }`}
                        >
                            <span className="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <span className="hidden sm:inline">{d.dayName}</span>
                                <span className="sm:hidden">{d.dayShort}</span>
                            </span>
                            <span
                                className={`inline-flex items-center justify-center w-7 h-7 mt-1 text-sm font-bold rounded-full ${
                                    d.isToday
                                        ? 'bg-indigo-600 text-white shadow-xs'
                                        : 'text-gray-900 dark:text-white'
                                }`}
                            >
                                {d.dayNumber}
                            </span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Barra de Eventos de Todo el Día */}
            {allDayEvents.length > 0 && (
                <div className="flex border-b border-gray-200/80 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-800/20 py-1.5">
                    <div className="w-16 sm:w-20 shrink-0 text-[10px] font-semibold text-gray-400 text-right pr-3 pt-1">
                        Todo el día
                    </div>
                    <div className="flex-1 grid grid-cols-7 gap-1 px-1">
                        {weekDays.map((d, idx) => {
                            const dayAll = allDayEvents.filter(e => e.date === d.date);
                            return (
                                <div key={idx} className="flex flex-col gap-1">
                                    {dayAll.map((evt, eIdx) => {
                                        const style = getEventStyle(evt.type);
                                        return (
                                            <div
                                                key={eIdx}
                                                onClick={() => onSelectEvent && onSelectEvent(evt)}
                                                className={`px-1.5 py-0.5 rounded text-[11px] font-semibold truncate border cursor-pointer ${style.badge}`}
                                                title={evt.title}
                                            >
                                                {evt.title}
                                            </div>
                                        );
                                    })}
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Área Scrollable con la Regla y la Cuadrícula */}
            <div className="flex flex-1 max-h-[680px] overflow-y-auto relative scrollbar-thin">
                {/* Regla Horaria Izquierda */}
                <div className="w-16 sm:w-20 shrink-0 border-r border-gray-200/80 dark:border-gray-800 select-none bg-white dark:bg-gray-900">
                    {hours.map((h, i) => (
                        <div
                            key={i}
                            style={{ height: `${HOUR_HEIGHT}px` }}
                            className="relative pr-2 text-right text-[11px] font-medium text-gray-400 -top-2.5"
                        >
                            {h.label}
                        </div>
                    ))}
                </div>

                {/* Contenedor de 7 Columnas */}
                <div className="flex-1 grid grid-cols-7 divide-x divide-gray-200/60 dark:divide-gray-800/60 relative bg-white dark:bg-gray-900">
                    {/* Líneas horizontales de guía para cada hora */}
                    <div className="absolute inset-0 pointer-events-none flex flex-col">
                        {hours.map((_, i) => (
                            <div
                                key={i}
                                style={{ height: `${HOUR_HEIGHT}px` }}
                                className="border-b border-gray-100 dark:border-gray-800/50 w-full"
                            />
                        ))}
                    </div>

                    {/* Columnas de cada día con sus eventos */}
                    {weekDays.map((d, colIdx) => {
                        const dayEvents = timedEventsByDate[d.date] || [];
                        const clusters = getClusteredEventsForDay(dayEvents, d.date);

                        return (
                            <div
                                key={colIdx}
                                onClick={(e) => {
                                    if (onSlotClick) {
                                        const rect = e.currentTarget.getBoundingClientRect();
                                        const clickY = e.clientY - rect.top;
                                        const approxHour = Math.floor(clickY / HOUR_HEIGHT) + CALENDAR_START_HOUR;
                                        onSlotClick(d.date, `${String(approxHour).padStart(2, '0')}:00`);
                                    }
                                }}
                                style={{ height: `${hours.length * HOUR_HEIGHT}px` }}
                                className={`relative transition-colors ${
                                    d.isToday ? 'bg-indigo-50/20 dark:bg-indigo-950/10' : 'hover:bg-gray-50/40 dark:hover:bg-gray-800/20'
                                }`}
                            >
                                {/* Indicador de Tiempo Real "AHORA" (Línea roja) si es hoy */}
                                {d.isToday && isNowVisible && (
                                    <div
                                        style={{ top: `${nowTopPosition}px` }}
                                        className="absolute left-0 right-0 z-20 pointer-events-none flex items-center"
                                    >
                                        <div className="w-2.5 h-2.5 -ml-1 rounded-full bg-rose-500 ring-2 ring-white dark:ring-gray-900 shadow-xs" />
                                        <div className="h-[2px] w-full bg-rose-500 shadow-2xs" />
                                    </div>
                                )}

                                {/* Clusters de Eventos por Slot */}
                                {clusters.map((cluster, cIdx) => {
                                    const primaryEvt = cluster.items[0];
                                    const overflowCount = cluster.items.length - 1;
                                    const style = getEventStyle(primaryEvt.type);

                                    return (
                                        <div
                                            key={cIdx}
                                            onClick={(e) => {
                                                e.stopPropagation();
                                                onSelectEvent && onSelectEvent(primaryEvt);
                                            }}
                                            style={{
                                                top: `${cluster.top}px`,
                                                height: `${Math.max(cluster.height - 4, 38)}px`,
                                            }}
                                            className={`absolute left-1 right-1 z-10 p-1.5 sm:p-2 rounded-xl border shadow-xs cursor-pointer overflow-hidden transition-all hover:z-30 hover:shadow-md flex flex-col justify-between ${style.card}`}
                                        >
                                            <div className="overflow-hidden">
                                                <div className="flex items-center justify-between gap-1 mb-0.5">
                                                    <h4 className="text-[11px] sm:text-xs font-bold truncate leading-tight">
                                                        {primaryEvt.title}
                                                    </h4>
                                                    <span className={`w-1.5 h-1.5 rounded-full shrink-0 ${style.dot}`} />
                                                </div>

                                                <div className="flex items-center gap-1 text-[10px] font-medium opacity-80 truncate">
                                                    <Clock className="w-2.5 h-2.5 shrink-0" />
                                                    <span>{primaryEvt.start_time} - {primaryEvt.end_time || ''}</span>
                                                </div>

                                                {cluster.height > 60 && primaryEvt.classroom && (
                                                    <div className="text-[10px] opacity-85 truncate mt-0.5 hidden sm:flex items-center gap-1">
                                                        <MapPin className="w-2.5 h-2.5 shrink-0" />
                                                        <span className="truncate">{primaryEvt.classroom}</span>
                                                    </div>
                                                )}
                                            </div>

                                            {overflowCount > 0 && (
                                                <button
                                                    type="button"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        onViewDay && onViewDay(cluster.dateStr);
                                                    }}
                                                    className="mt-1 text-left text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline truncate"
                                                >
                                                    +{overflowCount} más en este horario →
                                                </button>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
