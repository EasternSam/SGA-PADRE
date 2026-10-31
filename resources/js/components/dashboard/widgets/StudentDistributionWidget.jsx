import React from 'react';
import { GraduationCap } from 'lucide-react';
import { EmptyState, HeaderLink, WidgetHeader, formatNumber, seriesColors } from './shared';

const RADIUS = 46;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

export default function StudentDistributionWidget({ data, palette }) {
    const items = data.items ?? [];
    const colors = seriesColors(palette);

    if (!data.total) {
        return (
            <>
                <WidgetHeader icon={GraduationCap} title="Distribución de estudiantes" palette={palette} />
                <EmptyState icon={GraduationCap} title="Sin estudiantes registrados" />
            </>
        );
    }

    let offset = 0;
    const segments = items.map((item, i) => {
        const length = (item.count / data.total) * CIRCUMFERENCE;
        const segment = { ...item, color: item.isOther ? palette.muted : colors[i % colors.length], length, offset };
        offset += length;
        return segment;
    });

    return (
        <>
            <WidgetHeader
                icon={GraduationCap}
                title="Distribución de estudiantes"
                palette={palette}
                action={<HeaderLink href={data.href}>Ver todos</HeaderLink>}
            />

            <div className="flex flex-col sm:flex-row items-center gap-5">
                <div className="relative size-28 shrink-0">
                    <svg className="size-full -rotate-90" viewBox="0 0 120 120" role="img" aria-label="Distribución por programa">
                        <circle cx="60" cy="60" r={RADIUS} fill="none" strokeWidth="11" className="stroke-gray-100 dark:stroke-[#262626]" />
                        {segments.map((s) => (
                            <circle
                                key={s.name}
                                cx="60"
                                cy="60"
                                r={RADIUS}
                                fill="none"
                                stroke={s.color}
                                strokeWidth="11"
                                strokeDasharray={`${s.length} ${CIRCUMFERENCE - s.length}`}
                                strokeDashoffset={-s.offset}
                            >
                                <title>{`${s.name}: ${s.count}`}</title>
                            </circle>
                        ))}
                    </svg>
                    <div className="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span className="text-2xl font-black text-gray-900 dark:text-white tracking-tight tabular-nums">{formatNumber(data.total)}</span>
                        <span className="text-[10px] font-semibold text-gray-400">estudiantes</span>
                    </div>
                </div>

                <ul className="flex-1 w-full min-w-0 space-y-1.5">
                    {segments.map((s) => (
                        <li key={s.name} className="flex items-center justify-between gap-2 text-xs py-1 border-b border-gray-50 dark:border-white/[0.03] last:border-0">
                            <span className="flex items-center gap-2 min-w-0">
                                <span style={{ backgroundColor: s.color }} className="size-2 rounded-full shrink-0" />
                                <span className="font-semibold text-gray-800 dark:text-gray-200 truncate">{s.name}</span>
                            </span>
                            <span className="shrink-0 text-gray-500 dark:text-gray-400 tabular-nums">
                                <strong className="text-gray-900 dark:text-white">{s.count}</strong> · {s.percentage}%
                            </span>
                        </li>
                    ))}
                </ul>
            </div>
        </>
    );
}
