import React, { useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { ArrowRight, CalendarDays, ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';

const MONTHS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const WEEKDAYS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

const pad = (n) => String(n).padStart(2, '0');
const isoDate = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

/** Celdas de 5 o 6 semanas, empezando en domingo, con días de meses vecinos atenuados. */
function monthCells(year, month) {
    const first = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const weeks = Math.ceil((first.getDay() + daysInMonth) / 7);
    const todayIso = isoDate(new Date());

    return Array.from({ length: weeks * 7 }, (_, i) => {
        const date = new Date(year, month, 1 - first.getDay() + i);
        const iso = isoDate(date);
        return { iso, day: date.getDate(), inMonth: date.getMonth() === month, isToday: iso === todayIso };
    });
}

export default function CalendarWidget({ data, palette }) {
    const [cursor, setCursor] = useState(() => {
        const now = new Date();
        return { year: now.getFullYear(), month: now.getMonth() };
    });
    const cells = useMemo(() => monthCells(cursor.year, cursor.month), [cursor]);
    const url = data.calendarUrl;

    const shift = (delta) => setCursor(({ year, month }) => {
        const next = new Date(year, month + delta, 1);
        return { year: next.getFullYear(), month: next.getMonth() };
    });

    const title = `${MONTHS[cursor.month]} ${cursor.year}`;
    const monthIso = `${cursor.year}-${pad(cursor.month + 1)}-01`;

    return (
        <>
            <div className="flex items-center justify-between pb-4">
                {url ? (
                    <Link href={`${url}?date=${monthIso}&view=month`} className="text-base font-bold text-gray-900 dark:text-white tracking-tight hover:underline flex items-center gap-2">
                        <CalendarDays className="size-4" style={{ color: palette.primary }} />{title}
                    </Link>
                ) : (
                    <span className="text-base font-bold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <CalendarDays className="size-4" style={{ color: palette.primary }} />{title}
                    </span>
                )}
                <div className="flex items-center gap-1">
                    <button type="button" onClick={() => shift(-1)} aria-label="Mes anterior" className="size-7 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 flex items-center justify-center transition-colors">
                        <ChevronLeft className="size-4" />
                    </button>
                    <button type="button" onClick={() => shift(1)} aria-label="Mes siguiente" className="size-7 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 flex items-center justify-center transition-colors">
                        <ChevronRight className="size-4" />
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-7 text-center text-xs font-semibold text-gray-400 mb-2">
                {WEEKDAYS.map((d) => <span key={d}>{d}</span>)}
            </div>

            <div className="grid grid-cols-7 gap-y-2 text-center text-xs">
                {cells.map((cell) => {
                    const className = cn(
                        'size-7 mx-auto rounded-lg flex items-center justify-center font-medium transition-colors',
                        cell.isToday && 'text-white font-bold shadow-sm',
                        !cell.isToday && cell.inMonth && 'text-gray-700 dark:text-gray-200',
                        !cell.isToday && !cell.inMonth && 'text-gray-300 dark:text-gray-600',
                        url && !cell.isToday && 'hover:bg-gray-100 dark:hover:bg-white/10',
                    );
                    const style = cell.isToday ? { backgroundColor: palette.primary } : undefined;

                    return url ? (
                        <button
                            key={cell.iso}
                            type="button"
                            onClick={() => router.visit(`${url}?date=${cell.iso}&view=day`)}
                            aria-label={`Ver agenda del ${cell.iso}`}
                            className={className}
                            style={style}
                        >
                            {cell.day}
                        </button>
                    ) : (
                        <span key={cell.iso} className={className} style={style}>{cell.day}</span>
                    );
                })}
            </div>

            {url && (
                <div className="pt-3 mt-auto border-t border-gray-100 dark:border-[#262626] flex items-center justify-between">
                    <span className="text-[11px] text-gray-400 font-medium">Toca un día para ver su agenda</span>
                    <Link href={url} className="text-xs font-bold hover:underline flex items-center gap-1" style={{ color: palette.primary }}>
                        Ver calendario <ArrowRight className="size-3" />
                    </Link>
                </div>
            )}
        </>
    );
}
