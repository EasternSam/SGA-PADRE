import React from 'react';
import { Link } from '@inertiajs/react';
import { ArrowUpRight, Zap } from 'lucide-react';
import { EmptyState, ICONS, WidgetHeader, seriesColors } from './shared';

export default function QuickActionsWidget({ data, palette }) {
    const colors = seriesColors(palette);

    return (
        <>
            <WidgetHeader icon={Zap} title="Accesos rápidos" palette={palette} />

            {data.items.length === 0 ? (
                <EmptyState icon={Zap} title="Sin accesos disponibles" description="Tu rol no tiene módulos habilitados en el plan actual." />
            ) : (
                <div className="space-y-2">
                    {data.items.map((action, i) => {
                        const Icon = ICONS[action.icon] ?? Zap;
                        const color = colors[i % colors.length];
                        return (
                            <Link
                                key={action.key}
                                href={action.href}
                                className="px-3.5 py-2.5 flex items-center justify-between gap-3 group rounded-xl bg-gray-50/70 dark:bg-[#121212] border border-gray-100 dark:border-[#262626] hover:border-gray-300 dark:hover:border-white/20 hover:bg-white dark:hover:bg-white/[0.05] transition-colors"
                            >
                                <span className="flex items-center gap-3 min-w-0">
                                    <span className="size-8 rounded-lg flex items-center justify-center shrink-0" style={{ backgroundColor: `${color}15`, color }}>
                                        <Icon className="size-4" />
                                    </span>
                                    <span className="text-xs font-bold text-gray-900 dark:text-white truncate">{action.title}</span>
                                </span>
                                <span className="flex items-center gap-2 shrink-0">
                                    <span className="text-[10px] font-semibold text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-[#1f1f1f] px-2 py-0.5 rounded-md">{action.tag}</span>
                                    <ArrowUpRight className="size-3.5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" style={{ color }} />
                                </span>
                            </Link>
                        );
                    })}
                </div>
            )}
        </>
    );
}
