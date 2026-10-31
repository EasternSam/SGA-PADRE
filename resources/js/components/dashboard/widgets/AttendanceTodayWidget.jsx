import React from 'react';
import { UserCheck } from 'lucide-react';
import { EmptyState, WidgetHeader, formatNumber } from './shared';

/** Colores semánticos de estado (no de marca): verde/ámbar/rojo se leen sin leyenda. */
const STATES = [
    { key: 'present', label: 'Presentes', bar: 'bg-emerald-500' },
    { key: 'late', label: 'Tardanzas', bar: 'bg-amber-500' },
    { key: 'absent', label: 'Ausentes', bar: 'bg-rose-500' },
];

export default function AttendanceTodayWidget({ data, palette }) {
    if (!data.date) {
        return (
            <>
                <WidgetHeader icon={UserCheck} title="Asistencia" palette={palette} />
                <EmptyState icon={UserCheck} title="Aún no hay asistencias registradas" />
            </>
        );
    }

    return (
        <>
            <WidgetHeader
                icon={UserCheck}
                title="Asistencia"
                subtitle={data.isToday ? 'Hoy' : `Último registro: ${data.dateLabel}`}
                palette={palette}
            />

            <div className="flex items-end justify-between gap-3">
                <div>
                    <span className="text-3xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">{data.rate ?? 0}%</span>
                    <span className="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">asistencia (incluye tardanzas)</span>
                </div>
                <span className="text-xs text-gray-500 dark:text-gray-400 tabular-nums">{formatNumber(data.total)} registros</span>
            </div>

            <div className="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-[#262626] mt-4" role="img" aria-label="Proporción de asistencia">
                {STATES.map((s) => (
                    <div key={s.key} className={s.bar} style={{ width: `${data.total ? (data[s.key] / data.total) * 100 : 0}%` }} />
                ))}
            </div>

            <ul className="grid grid-cols-3 gap-2 mt-4">
                {STATES.map((s) => (
                    <li key={s.key} className="rounded-xl bg-gray-50/70 dark:bg-[#121212] border border-gray-100 dark:border-[#262626] px-3 py-2">
                        <span className="flex items-center gap-1.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                            <span className={`size-2 rounded-full ${s.bar}`} />{s.label}
                        </span>
                        <span className="block text-lg font-black text-gray-900 dark:text-white tabular-nums mt-0.5">{formatNumber(data[s.key])}</span>
                    </li>
                ))}
            </ul>
        </>
    );
}
