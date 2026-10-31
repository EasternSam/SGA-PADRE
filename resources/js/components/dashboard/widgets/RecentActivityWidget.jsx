import React from 'react';
import { Activity, BookOpen, CalendarDays, CreditCard, GraduationCap } from 'lucide-react';
import { EmptyState, HeaderLink, WidgetHeader } from './shared';

const KIND_ICONS = {
    payment: CreditCard,
    student: GraduationCap,
    enrollment: BookOpen,
    schedule: CalendarDays,
    general: Activity,
};

export default function RecentActivityWidget({ data, palette }) {
    return (
        <>
            <WidgetHeader
                icon={Activity}
                title="Actividad reciente"
                palette={palette}
                action={<HeaderLink href={data.href}>Ver todas</HeaderLink>}
            />

            {data.items.length === 0 ? (
                <EmptyState icon={Activity} title="Sin actividad registrada" />
            ) : (
                <ul className="space-y-4">
                    {data.items.map((item) => {
                        const Icon = KIND_ICONS[item.kind] ?? Activity;
                        return (
                            <li key={item.id} className="flex items-start gap-3">
                                <span
                                    className="size-7 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                    style={{ backgroundColor: `${palette.primary}15`, color: palette.primary }}
                                >
                                    <Icon className="size-3.5" />
                                </span>
                                <div className="min-w-0 flex-1">
                                    <p className="text-xs text-gray-700 dark:text-gray-300 leading-snug line-clamp-2">{item.text}</p>
                                    <span className="text-[10px] text-gray-400 font-medium mt-1 block">{item.user} · {item.time}</span>
                                </div>
                            </li>
                        );
                    })}
                </ul>
            )}
        </>
    );
}
