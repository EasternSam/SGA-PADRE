import React, { useState } from 'react';
import { CalendarDays } from 'lucide-react';
import { EmptyState, ViewSelect, WidgetHeader, formatNumber } from './shared';

const VIEWS = [
    { value: 'shifts', label: 'Por turno' },
    { value: 'spaces', label: 'Aulas vs. laboratorios' },
];

/** Escala del eje Y: múltiplo "redondo" ≥ 115 % del máximo, dividido en 4. */
function niceScale(max) {
    const target = Math.max(10, max) * 1.15;
    const step = target > 120 ? 50 : target > 60 ? 25 : 10;

    return Math.max(step * 4, Math.ceil(target / step) * step);
}

export default function WeeklyWorkloadWidget({ data, palette }) {
    const [view, setView] = useState('shifts');
    const days = data.days ?? [];

    const bars = view === 'shifts'
        ? [
            { key: 'mat', label: 'Matutino', color: palette.primary },
            { key: 'vesp', label: 'Vespertino', color: palette.accent },
            { key: 'noct', label: 'Nocturno', color: palette.neutral },
        ]
        : [
            { key: 'aulas', label: 'Aulas', color: palette.primary },
            { key: 'labs', label: 'Laboratorios', color: palette.accent },
        ];

    const scale = niceScale(Math.max(0, ...days.flatMap((d) => bars.map((b) => d[b.key]))));
    const ticks = [scale, scale * 0.75, scale * 0.5, scale * 0.25, 0].map(Math.round);

    if (!data.totalSections) {
        return (
            <>
                <WidgetHeader icon={CalendarDays} title="Carga horaria semanal" palette={palette} />
                <EmptyState icon={CalendarDays} title="Sin secciones programadas" description="Programa secciones para ver la distribución semanal." />
            </>
        );
    }

    return (
        <>
            <WidgetHeader
                icon={CalendarDays}
                title="Carga horaria semanal"
                palette={palette}
                action={<ViewSelect value={view} onChange={setView} options={VIEWS} label="Vista de carga horaria" />}
            />

            <div className="flex flex-wrap items-center gap-3 sm:gap-4 mb-4 text-xs font-medium text-gray-500 dark:text-gray-400">
                {bars.map((bar) => (
                    <span key={bar.key} className="flex items-center gap-1.5">
                        <span style={{ backgroundColor: bar.color }} className="size-2 rounded-full" />
                        {bar.label}: <strong className="text-gray-700 dark:text-gray-300 font-semibold">{formatNumber(data.totals[bar.key])}</strong>
                    </span>
                ))}
            </div>

            <div className="relative h-48 w-full flex items-end">
                <div className="h-full flex flex-col justify-between text-[10px] text-gray-400 font-medium pr-3 pb-6 select-none tabular-nums">
                    {ticks.map((tick, i) => <span key={i}>{tick}</span>)}
                </div>
                <div className="flex-1 h-full flex items-end justify-between gap-1.5 sm:gap-2 border-b border-gray-200 dark:border-[#262626] pb-0.5">
                    {days.map((day) => (
                        <div key={day.short} className="flex-1 flex flex-col items-center h-full justify-end group">
                            <div className="flex items-end gap-1 h-full pb-1 w-full justify-center">
                                {bars.map((bar) => (
                                    <div
                                        key={bar.key}
                                        style={{
                                            height: `${Math.min(100, Math.max(day[bar.key] > 0 ? 4 : 0, (day[bar.key] / scale) * 100))}%`,
                                            backgroundColor: bar.color,
                                        }}
                                        className={`${bars.length === 2 ? 'w-3 sm:w-4' : 'w-2 sm:w-2.5'} rounded-t-sm transition-all duration-300 hover:opacity-80`}
                                        title={`${day.name} · ${bar.label}: ${day[bar.key]} bloques`}
                                    />
                                ))}
                            </div>
                            <span className="text-[10px] text-gray-500 dark:text-gray-400 font-bold mt-1 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                                {day.short}
                            </span>
                        </div>
                    ))}
                </div>
            </div>

            <div className="mt-auto pt-4 border-t border-gray-100 dark:border-[#262626] flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>{formatNumber(data.totalSections)} secciones activas</span>
                <span className="font-semibold text-gray-700 dark:text-gray-300">
                    Bloques semanales: <strong style={{ color: palette.primary }}>{formatNumber(data.totalBlocks)}</strong>
                </span>
            </div>
        </>
    );
}
