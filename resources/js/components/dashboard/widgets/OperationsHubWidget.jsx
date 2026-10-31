import React from 'react';
import { Link } from '@inertiajs/react';
import { ArrowUpRight, ChevronRight, Layers } from 'lucide-react';
import { EmptyState, ICONS, WidgetHeader, formatNumber, seriesColors } from './shared';

const COLUMNS = {
    xl: { 1: 'lg:grid-cols-1', 2: 'lg:grid-cols-2', 3: 'lg:grid-cols-3', 4: 'lg:grid-cols-4' },
    lg: { 1: 'lg:grid-cols-1', 2: 'lg:grid-cols-2', 3: 'lg:grid-cols-2', 4: 'lg:grid-cols-2' },
};

export default function OperationsHubWidget({ data, size, palette }) {
    const modules = data.modules ?? [];
    const colors = seriesColors(palette);

    return (
        <>
            <WidgetHeader icon={Layers} title="Centro de operaciones" palette={palette} />

            {modules.length === 0 ? (
                <EmptyState icon={Layers} title="Sin módulos disponibles" description="Tu rol no tiene módulos habilitados en el plan actual." />
            ) : (
                <div className={`grid grid-cols-1 sm:grid-cols-2 ${COLUMNS[size]?.[modules.length] ?? 'lg:grid-cols-2'} gap-4`}>
                    {modules.map((module, i) => {
                        const Icon = ICONS[module.icon] ?? Layers;
                        const color = colors[i % colors.length];
                        const [main, secondary] = module.links;
                        return (
                            <div key={module.key} className="p-4 rounded-xl bg-gray-50/70 dark:bg-[#121212] border border-gray-100 dark:border-[#262626] flex flex-col justify-between">
                                <div>
                                    <div className="flex items-center justify-between pb-3">
                                        <span className="size-9 rounded-xl flex items-center justify-center" style={{ backgroundColor: `${color}15`, color }}>
                                            <Icon className="size-4" />
                                        </span>
                                        <span className="text-[11px] font-bold px-2 py-0.5 rounded-md tabular-nums" style={{ backgroundColor: `${color}15`, color }}>
                                            {formatNumber(module.metric.value)} {module.metric.unit}
                                        </span>
                                    </div>
                                    <h4 className="text-sm font-bold text-gray-900 dark:text-white">{module.title}</h4>
                                </div>
                                <div className="pt-3 mt-3 space-y-1.5 border-t border-gray-100 dark:border-[#262626]">
                                    <Link href={main.href} className="text-xs font-semibold flex items-center justify-between py-1 group hover:underline" style={{ color }}>
                                        <span>{main.title}</span>
                                        <ArrowUpRight className="size-3.5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                                    </Link>
                                    {secondary && (
                                        <Link href={secondary.href} className="text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center justify-between py-1">
                                            <span>{secondary.title}</span>
                                            <ChevronRight className="size-3.5 text-gray-400" />
                                        </Link>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}
        </>
    );
}
