import React from 'react';
import { Link } from '@inertiajs/react';
import { ArrowRight, Gauge } from 'lucide-react';
import { NumberTicker } from '@/components/godui';
import { EmptyState, formatMoney, formatNumber } from './shared';

/**
 * Columnas por cantidad de indicadores. Con 5–6 en tamaño `xl` se usa una sola fila
 * solo en pantallas 2xl; en `lg` (2/3) se limita a 3 para que las cifras no se trunquen.
 */
const COLUMNS = {
    xl: { 1: 'lg:grid-cols-1', 2: 'lg:grid-cols-2', 3: 'lg:grid-cols-3', 4: 'lg:grid-cols-4', 5: 'lg:grid-cols-3 2xl:grid-cols-5', 6: 'lg:grid-cols-3 2xl:grid-cols-6' },
    lg: { 1: 'lg:grid-cols-1', 2: 'lg:grid-cols-2', 3: 'lg:grid-cols-3' },
};

export default function KpisWidget({ data, size, palette }) {
    const items = data.items ?? [];

    if (items.length === 0) {
        return (
            <div className="p-5 rounded-2xl bg-white dark:bg-[#171717] border border-gray-100 dark:border-[#262626] shadow-sm">
                <EmptyState icon={Gauge} title="Sin indicadores para tu rol" description="Los módulos de tu plan no tienen indicadores para mostrar." />
            </div>
        );
    }

    const columns = COLUMNS[size]?.[items.length] ?? 'lg:grid-cols-3';

    return (
        <div className={`grid grid-cols-1 sm:grid-cols-2 ${columns} gap-4`}>
            {items.map((item) => {
                const display = item.format === 'currency' ? formatMoney(item.value) : formatNumber(item.value);

                return (
                    <div
                        key={item.key}
                        className="p-5 rounded-2xl bg-white dark:bg-[#171717] border border-gray-100 dark:border-[#262626] shadow-sm hover:shadow-md transition-shadow min-w-0"
                    >
                        <div className="flex items-center justify-between gap-2">
                            <span className="text-xs font-semibold text-gray-500 dark:text-gray-400 tracking-wide truncate" title={item.label}>
                                {item.label}
                            </span>
                            {item.href && (
                                <Link
                                    href={item.href}
                                    style={{ backgroundColor: `${palette.primary}14`, color: palette.primary }}
                                    className="size-7 rounded-lg flex items-center justify-center shrink-0 transition-transform duration-200 hover:scale-105"
                                    aria-label={`Abrir ${item.label}`}
                                >
                                    <ArrowRight className="size-3.5" />
                                </Link>
                            )}
                        </div>
                        <div className="text-2xl font-black text-gray-900 dark:text-white tracking-tight mt-2 tabular-nums whitespace-nowrap" title={display}>
                            {item.format === 'currency' ? display : <NumberTicker value={item.value} />}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
