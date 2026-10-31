import React from 'react';
import { Clock, MapPin } from 'lucide-react';
import { EmptyState, HeaderLink, WidgetHeader } from './shared';

const nowHHMM = () => new Date().toTimeString().slice(0, 5);

export default function TodayScheduleWidget({ data, palette }) {
    const items = data.items ?? [];
    const now = nowHHMM();
    const remaining = data.total - items.length;

    return (
        <>
            <WidgetHeader
                icon={Clock}
                title="Clases de hoy"
                subtitle={data.day}
                palette={palette}
                action={<HeaderLink href={data.href}>Calendario</HeaderLink>}
            />

            {items.length === 0 ? (
                <EmptyState icon={Clock} title="No hay clases programadas hoy" description={`Ninguna sección activa tiene clase los ${data.day.toLowerCase()}.`} />
            ) : (
                <ul className="space-y-2">
                    {items.map((item) => {
                        const live = item.start <= now && now < item.end;
                        return (
                            <li
                                key={item.id}
                                className="flex items-start gap-3 rounded-xl px-3 py-2.5 bg-gray-50/70 dark:bg-[#121212] border border-gray-100 dark:border-[#262626]"
                            >
                                <span className="text-[11px] font-bold tabular-nums text-gray-500 dark:text-gray-400 w-11 shrink-0 pt-0.5 leading-tight">
                                    {item.start}<br /><span className="font-medium text-gray-400">{item.end}</span>
                                </span>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs font-bold text-gray-900 dark:text-white truncate">{item.title}</p>
                                    <p className="text-[11px] text-gray-500 dark:text-gray-400 truncate flex items-center gap-1 mt-0.5">
                                        {item.classroom && <><MapPin className="size-3 shrink-0" />{item.classroom} · </>}
                                        {item.section || item.code}
                                    </p>
                                </div>
                                {live && (
                                    <span className="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0" style={{ backgroundColor: `${palette.primary}15`, color: palette.primary }}>
                                        En curso
                                    </span>
                                )}
                            </li>
                        );
                    })}
                </ul>
            )}

            {remaining > 0 && (
                <p className="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    y {remaining} {remaining === 1 ? 'sección más' : 'secciones más'} hoy
                </p>
            )}
        </>
    );
}
